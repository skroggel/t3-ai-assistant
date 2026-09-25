<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, version 3.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Madj2k\AiAssistant\Backend\Diagnostics;

use GuzzleHttp\Client;
use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile;
use Madj2k\AiAssistant\Assistant\Domain\Repository\AssistantProfileRepository;
use Madj2k\AiCore\Connection\Health\ConnectionHealthChecker;
use Madj2k\AiAssistant\Connection\Domain\Model\AiConnection;
use Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection;
use Madj2k\AiAssistant\Connection\Domain\Repository\AiConnectionRepository;
use Madj2k\AiAssistant\Connection\Domain\Repository\VectorStoreConnectionRepository;
use Madj2k\AiAssistant\Connection\Domain\Repository\McpConnectionRepository;
use Madj2k\AiAssistant\Connection\Domain\Model\McpConnection;
use Madj2k\AiMcp\Client\StreamableHttpMcpClient;
use Madj2k\AiMcp\Authentication\OAuth2ClientCredentialsAuthentication;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class BackendConnectionTestHandler
 *
 * Handles backend tests for connection records and assistant configurations.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final readonly class BackendConnectionTestHandler
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Connection\Domain\Repository\AiConnectionRepository $aiConnectionRepository AI connection repository.
     * @param \Madj2k\AiAssistant\Connection\Domain\Repository\VectorStoreConnectionRepository $vectorStoreConnectionRepository Vector store connection repository.
     * @param \Madj2k\AiAssistant\Assistant\Domain\Repository\AssistantProfileRepository $assistantProfileRepository Assistant profile repository.
     * @param \Madj2k\AiCore\Connection\Health\ConnectionHealthChecker $connectionHealthChecker Connection health checker.
     * @param \Madj2k\AiAssistant\Backend\Diagnostics\BackendAssistantTester $assistantTester Assistant configuration tester.
     */
    public function __construct(
        private AiConnectionRepository          $aiConnectionRepository,
        private VectorStoreConnectionRepository $vectorStoreConnectionRepository,
        private McpConnectionRepository         $mcpConnectionRepository,
        private AssistantProfileRepository      $assistantProfileRepository,
        private ConnectionHealthChecker         $connectionHealthChecker,
        private BackendAssistantTester          $assistantTester
    ) {
    }


    /**
     * Checks whether this handler should process the request.
     *
     * @param object $request Extbase request.
     * @return bool True when supported.
     */
    public function supports(object $request): bool
    {
        return $this->getRequestArgument($request, 'testConnection') !== ''
            || $this->getRequestArgument($request, 'testAssistant') !== '';
    }


    /**
     * Handles the connection test request.
     *
     * @param object $request Extbase request.
     * @param array<string, mixed> $state Mutable module state.
     * @return void
     */
    public function handle(object $request, array &$state): void
    {
        $assistantUid = (int)$this->getRequestArgument($request, 'testAssistant');
        if ($assistantUid > 0) {
            $this->handleAssistantTest($assistantUid, $state);
            return;
        }

        /** @var string $testIdentifier */
        $testIdentifier = $this->getRequestArgument($request, 'testConnection');

        /** @var array<int, string> $parts */
        $parts = explode(':', $testIdentifier, 2);

        /** @var string $kind */
        $kind = trim($parts[0] ?? '');

        /** @var int $uid */
        $uid = (int)($parts[1] ?? 0);

        if ($kind === 'ai') {
            $this->handleAiConnectionTest($uid, $state);
            return;
        }

        if ($kind === 'vector') {
            $this->handleVectorStoreConnectionTest($uid, $state);
            return;
        }

        if ($kind === 'mcp') {
            $this->handleMcpConnectionTest($uid, $state);
            return;
        }

        $this->setResult($state, $testIdentifier, 'error', 'Unsupported connection test target.');
    }


    /**
     * Handles an assistant configuration and connection test.
     *
     * @param int $uid Assistant profile uid.
     * @param array<string, mixed> $state Mutable module state.
     * @return void
     */
    private function handleAssistantTest(int $uid, array &$state): void
    {
        $assistant = $this->assistantProfileRepository->findByUid($uid);
        if (!$assistant instanceof AssistantProfile) {
            $this->setAssistantResult($state, $uid, 'error', 'Assistant profile record not found.', []);
            return;
        }

        $result = $this->assistantTester->test($assistant);
        $this->setAssistantResult(
            $state,
            $uid,
            $result['status'],
            $result['message'],
            $result['checks'],
            $result['embeddingDimension'],
        );
    }


    /**
     * Handles an AI connection test.
     *
     * @param int $uid Connection uid.
     * @param array<string, mixed> $state Mutable module state.
     * @return void
     */
    private function handleAiConnectionTest(int $uid, array &$state): void
    {
        /** @var \Madj2k\AiAssistant\Connection\Domain\Model\AiConnection|null $connection */
        $connection = $this->aiConnectionRepository->findByUid($uid);

        if (!$connection instanceof AiConnection) {
            $this->setResult($state, 'ai:' . $uid, 'error', 'AI connection record not found.');
            return;
        }

        try {
            $embeddingResponse = $this->connectionHealthChecker->probeAiEmbedding(
                $connection,
                'TYPO3 AI Chat backend connection test',
            );
            $embeddingDimension = count($embeddingResponse->getEmbedding());
        } catch (\Throwable $exception) {
            $this->setResult(
                $state,
                'ai:' . $uid,
                'error',
                'Embedding test failed: ' . $exception->getMessage(),
            );
            return;
        }

        if ($embeddingDimension === 0) {
            $this->setResult($state, 'ai:' . $uid, 'error', 'AI connection test returned an empty embedding.');
            return;
        }

        $configuredEmbeddingDimension = $connection->getEmbeddingDimension();
        if ($configuredEmbeddingDimension <= 0) {
            $this->setResult(
                $state,
                'ai:' . $uid,
                'error',
                'The configured embedding dimension must be greater than zero.',
                $embeddingDimension,
            );
            return;
        }

        if ($configuredEmbeddingDimension !== $embeddingDimension) {
            $this->setResult($state, 'ai:' . $uid, 'error', sprintf(
                'Embedding dimension mismatch: provider returned %d dimensions, but the AI connection is configured for %d.',
                $embeddingDimension,
                $configuredEmbeddingDimension,
            ), $embeddingDimension);
            return;
        }

        try {
            $chatResponse = $this->connectionHealthChecker->probeAiChat(
                $connection,
                'Reply with OK.',
            );
            if (trim($chatResponse->getContent()) === '') {
                $this->setResult($state, 'ai:' . $uid, 'error', sprintf(
                'Embedding test succeeded with %d dimensions, but the chat test returned an empty response.',
                    $embeddingDimension,
                ), $embeddingDimension);
                return;
            }

            $this->setResult(
                $state,
                'ai:' . $uid,
                'ok',
                sprintf(
                    'AI connection test succeeded. Embedding: %d dimensions and configuration matches. Chat: response received.',
                    $embeddingDimension,
                ),
                $embeddingDimension,
            );
        } catch (\Throwable $exception) {
            $this->setResult($state, 'ai:' . $uid, 'error', sprintf(
                'Embedding test succeeded with %d dimensions, but the chat test failed: %s',
                $embeddingDimension,
                $exception->getMessage(),
            ), $embeddingDimension);
        }
    }


    /**
     * Handles a vector store connection test.
     *
     * @param int $uid Connection uid.
     * @param array<string, mixed> $state Mutable module state.
     * @return void
     */
    private function handleVectorStoreConnectionTest(int $uid, array &$state): void
    {
        /** @var \Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection|null $connection */
        $connection = $this->vectorStoreConnectionRepository->findByUid($uid);

        if (!$connection instanceof VectorStoreConnection) {
            $this->setResult($state, 'vector:' . $uid, 'error', 'Vector store connection record not found.');
            return;
        }

        try {
            $healthy = $this->connectionHealthChecker->probeVectorStore($connection);

            if (!$healthy) {
                $this->setResult($state, 'vector:' . $uid, 'error', 'Vector store connection test failed.');
                return;
            }

            $this->setResult($state, 'vector:' . $uid, 'ok', 'Vector store connection test succeeded.');
        } catch (\Throwable $exception) {
            $this->setResult($state, 'vector:' . $uid, 'error', $exception->getMessage());
        }
    }

    /**
     * Handles an MCP connection test and capability discovery.
     *
     * @param int $uid MCP connection uid.
     * @param array<string, mixed> $state Mutable module state.
     * @return void
     */
    private function handleMcpConnectionTest(int $uid, array &$state): void
    {
        $connection = $this->mcpConnectionRepository->findByUid($uid);
        if (!$connection instanceof McpConnection) {
            $this->setResult($state, 'mcp:' . $uid, 'error', 'MCP connection record not found.');
            return;
        }

        try {
            $httpClient = new Client(['timeout' => $connection->getTimeout()]);
            $authentication = $connection->getAuthentication() === 'oauth_client_credentials'
                ? new OAuth2ClientCredentialsAuthentication(
                    $httpClient,
                    $connection->getOauthTokenEndpoint(),
                    $connection->getOauthClientId(),
                    $connection->getOauthClientSecret(),
                    $connection->getOauthScope(),
                )
                : null;
            $client = new StreamableHttpMcpClient(
                $httpClient,
                $connection->getEndpoint(),
                headers: $connection->getHeaders(),
                authentication: $authentication,
            );
            $capabilities = $client->initialize();
            $tools = $client->listTools();
            $resources = $client->listResources();

            $this->setResult(
                $state,
                'mcp:' . $uid,
                'ok',
                sprintf('MCP connection succeeded. Tools: %d, resources: %d.', count($tools), count($resources)),
                null,
                [
                    'toolCount' => count($tools),
                    'resourceCount' => count($resources),
                    'capabilities' => $capabilities,
                ],
            );
        } catch (\Throwable $exception) {
            $this->setResult($state, 'mcp:' . $uid, 'error', 'MCP connection test failed: ' . $exception->getMessage());
        }
    }


    /**
     * Stores the connection test result for rendering in the diagnostics module.
     *
     * @param array<string, mixed> $state Mutable module state.
     * @param string $identifier Connection test identifier.
     * @param string $status Status.
     * @param string $message Message.
     * @param int|null $embeddingDimension Measured embedding dimension, if available.
     * @param array<string, mixed> $details Additional diagnostic details.
     * @return void
     */
    private function setResult(
        array &$state,
        string $identifier,
        string $status,
        string $message,
        ?int $embeddingDimension = null,
        array $details = [],
    ): void {
        $state['connectionTestResult'] = [
            'identifier' => $identifier,
            'status' => $status,
            'message' => $message,
            'embeddingDimension' => $embeddingDimension,
            'details' => $details,
        ];
    }


    /**
     * Stores the assistant test result for rendering in the diagnostics module.
     *
     * @param array<string, mixed> $state Mutable module state.
     * @param int $uid Assistant profile uid.
     * @param string $status Status.
     * @param string $message Summary message.
     * @param array<int, array{status: string, label: string, message: string}> $checks Detailed checks.
     * @param int|null $embeddingDimension Measured embedding dimension, if available.
     * @return void
     */
    private function setAssistantResult(
        array &$state,
        int $uid,
        string $status,
        string $message,
        array $checks,
        ?int $embeddingDimension = null,
    ): void {
        $state['assistantTestResult'] = [
            'uid' => $uid,
            'status' => $status,
            'message' => $message,
            'checks' => $checks,
            'embeddingDimension' => $embeddingDimension,
        ];
    }


    /**
     * Resolves a request argument from PSR-7, Extbase or raw POST data.
     *
     * @param object $request PSR-7 backend request or Extbase request.
     * @param string $name Argument name.
     * @return string Argument value.
     */
    private function getRequestArgument(object $request, string $name): string
    {
        if ($request instanceof ServerRequestInterface) {
            $parsedBody = $request->getParsedBody();
            if (is_array($parsedBody) && array_key_exists($name, $parsedBody)) {
                return trim((string)$parsedBody[$name]);
            }
        }

        if (method_exists($request, 'hasArgument') && method_exists($request, 'getArgument') && $request->hasArgument($name)) {
            return trim((string)$request->getArgument($name));
        }

        if (array_key_exists($name, $_POST)) {
            return trim((string)$_POST[$name]);
        }

        return '';
    }
}
