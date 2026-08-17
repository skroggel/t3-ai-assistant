<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension ai_assistant.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiAssistant\Backend\Diagnostics;

use Madj2k\AiCore\Connection\Health\ConnectionHealthChecker;
use Madj2k\AiAssistant\Backend\Response\BackendFlashMessageService;
use Madj2k\AiAssistant\Connection\Domain\Model\AiConnection;
use Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection;
use Madj2k\AiAssistant\Connection\Domain\Repository\AiConnectionRepository;
use Madj2k\AiAssistant\Connection\Domain\Repository\VectorStoreConnectionRepository;
use Madj2k\AiAssistant\Connection\VectorStore\DTO\VectorCollection;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

/**
 * Class BackendConnectionTestHandler
 *
 * Handles backend connection tests for configured connection records.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class BackendConnectionTestHandler
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Connection\Domain\Repository\AiConnectionRepository $aiConnectionRepository AI connection repository.
     * @param \Madj2k\AiAssistant\Connection\Domain\Repository\VectorStoreConnectionRepository $vectorStoreConnectionRepository Vector store connection repository.
     * @param \Madj2k\AiAssistant\Connection\Registry\AiConnectorRegistry $aiConnectorRegistry AI connector registry.
     * @param \Madj2k\AiAssistant\Connection\Registry\VectorStoreConnectorRegistry $vectorStoreConnectorRegistry Vector store connector registry.
     * @param \Madj2k\AiAssistant\Backend\Response\BackendFlashMessageService $flashMessageService Flash message service.
     */
    public function __construct(
        private readonly AiConnectionRepository $aiConnectionRepository,
        private readonly VectorStoreConnectionRepository $vectorStoreConnectionRepository,
        private readonly ConnectionHealthChecker $connectionHealthChecker,
        private readonly BackendFlashMessageService $flashMessageService
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
        return $this->getRequestArgument($request, 'testConnection') !== '';
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

        $this->setResult($state, $testIdentifier, 'error', 'Unsupported connection test target.');
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
            $healthy = $this->connectionHealthChecker->checkAi(
                $connection,
                'TYPO3 AI Chat backend connection test',
            );

            if (!$healthy) {
                $this->setResult($state, 'ai:' . $uid, 'error', 'AI connection test returned an empty embedding.');
                return;
            }

            $this->setResult($state, 'ai:' . $uid, 'ok', 'AI connection test succeeded.');
        } catch (\Throwable $exception) {
            $this->setResult($state, 'ai:' . $uid, 'error', $exception->getMessage());
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

        /** @var string $collectionName */
        $collectionName = trim($connection->getDefaultCollection()) !== ''
            ? trim($connection->getDefaultCollection())
            : '_aiassistant_connection_test';

        try {
            $healthy = $this->connectionHealthChecker->checkVectorStore(
                $connection,
                new VectorCollection(
                    $collectionName,
                    $connection->getVectorSize(),
                    $connection->getDistance()
                )
            );

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
     * Stores the test result and creates a flash message.
     *
     * @param array<string, mixed> $state Mutable module state.
     * @param string $identifier Connection test identifier.
     * @param string $status Status.
     * @param string $message Message.
     * @return void
     */
    private function setResult(array &$state, string $identifier, string $status, string $message): void
    {
        $state['connectionTestResult'] = [
            'identifier' => $identifier,
            'status' => $status,
            'message' => $message,
        ];

        $this->flashMessageService->add(
            $message,
            '',
            $status === 'ok' ? ContextualFeedbackSeverity::OK : ContextualFeedbackSeverity::WARNING
        );
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
