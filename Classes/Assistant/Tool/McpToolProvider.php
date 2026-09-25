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

namespace Madj2k\AiAssistant\Assistant\Tool;

use GuzzleHttp\Client;
use Madj2k\AiMcp\Authentication\OAuth2ClientCredentialsAuthentication;
use Madj2k\AiCore\Assistant\Tool\DTO\ToolCall;
use Madj2k\AiCore\Assistant\Tool\Provider\ContextAwareToolProviderInterface;
use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\Tool\DTO\ToolResult;
use Madj2k\AiAssistant\Assistant\Domain\Repository\AssistantProfileRepository;
use Madj2k\AiMcp\Client\StreamableHttpMcpClient;
use Madj2k\AiMcp\Integration\McpToolProvider as ExternalMcpToolProvider;

/**
 * Class McpToolProvider
 *
 * Resolves the centrally configured MCP endpoint for the TYPO3 integration.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final class McpToolProvider implements ContextAwareToolProviderInterface
{
    /** @var array<int, array<int, ExternalMcpToolProvider>> */
    private array $providersByAssistant = [];

    /**
     * @param AssistantProfileRepository $assistantProfileRepository Assistant profile repository.
     */
    public function __construct(
        private readonly AssistantProfileRepository $assistantProfileRepository,
    ) {
    }

    /** @inheritDoc */
    public function getIdentifier(): string
    {
        return 'typo3.mcp';
    }

    /** @inheritDoc */
    public function getTools(): array
    {
        return [];
    }

    /** @inheritDoc */
    public function call(ToolCall $call): ToolResult
    {
        return new ToolResult('No assistant context was supplied for the MCP tool call.', true);
    }

    /**
     * @inheritDoc
     */
    public function getToolsForContext(Context $context, ?\Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step = null): array
    {
        $tools = [];
        foreach ($this->getProviders($context, $step) as $provider) {
            array_push($tools, ...$provider->getTools());
        }

        return $tools;
    }

    /**
     * @inheritDoc
     */
    public function callForContext(Context $context, ToolCall $call, ?\Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step = null): ToolResult
    {
        foreach ($this->getProviders($context, $step) as $provider) {
            foreach ($provider->getTools() as $tool) {
                if ($tool->name === $call->name) {
                    return $provider->call($call);
                }
            }
        }

        return new ToolResult('The requested MCP tool is not assigned to this assistant.', true);
    }

    /**
     * Returns configured MCP providers for one assistant context.
     *
     * @param Context $context Assistant context.
     * @return array<int, ExternalMcpToolProvider> MCP providers.
     */
    private function getProviders(Context $context, ?\Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step = null): array
    {
        $assistantUid = $context->getAssistant()->getUid();
        $profile = $this->assistantProfileRepository->findByUid($assistantUid);
        if ($profile === null) {
            return [];
        }

        $providers = [];
        $stepConnections = $step !== null
            ? iterator_to_array($step->getMcpConnections(), false)
            : [];
        $connections = $stepConnections !== [] ? $stepConnections : $profile->getMcpConnections();
        $cacheKey = $assistantUid . ':' . implode(',', array_map(
            static fn (object $connection): string => (string)$connection->getUid(),
            $connections,
        ));
        if (isset($this->providersByAssistant[$cacheKey])) {
            return $this->providersByAssistant[$cacheKey];
        }

        foreach ($connections as $connection) {
            if (!$connection instanceof \Madj2k\AiAssistant\Connection\Domain\Model\McpConnection || $connection->getEndpoint() === '') {
                continue;
            }
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
            $providers[] = new ExternalMcpToolProvider(
                new StreamableHttpMcpClient(
                    $httpClient,
                    $connection->getEndpoint(),
                    headers: $connection->getHeaders(),
                    authentication: $authentication,
                ),
                'mcp.' . $connection->getIdentifier(),
                $connection->getAllowedTools(),
                'mcp.' . $connection->getIdentifier(),
                $connection->getMaxRounds(),
            );
        }

        return $this->providersByAssistant[$cacheKey] = $providers;
    }

}
