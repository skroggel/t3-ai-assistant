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

use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile;
use Madj2k\AiAssistant\Assistant\Domain\Repository\AssistantProfileRepository;
use Madj2k\AiAssistant\Connection\Domain\Model\AiConnection;
use Madj2k\AiAssistant\Connection\Domain\Repository\AiConnectionRepository;
use Madj2k\AiAssistant\Connection\Domain\Repository\VectorStoreConnectionRepository;
use Madj2k\AiAssistant\Connection\Domain\Repository\McpConnectionRepository;
use Madj2k\AiAssistant\Connection\Domain\Model\McpConnection;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class BackendConnectionProvider
 *
 * Builds backend view data for configured connections and assistant profiles.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final class BackendConnectionProvider
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Connection\Domain\Repository\AiConnectionRepository $aiConnectionRepository AI connection repository.
     * @param \Madj2k\AiAssistant\Connection\Domain\Repository\VectorStoreConnectionRepository $vectorStoreConnectionRepository Vector store connection repository.
     * @param \Madj2k\AiAssistant\Assistant\Domain\Repository\AssistantProfileRepository $assistantProfileRepository Assistant profile repository.
     */
    public function __construct(
        private readonly AiConnectionRepository $aiConnectionRepository,
        private readonly VectorStoreConnectionRepository $vectorStoreConnectionRepository,
        private readonly McpConnectionRepository $mcpConnectionRepository,
        private readonly AssistantProfileRepository $assistantProfileRepository,
    ) {
    }


    /**
     * Builds connection diagnostics view data.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $backendRequest Backend request.
     * @param array<string, mixed> $state Module state.
     * @return array<string, mixed> View data.
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    public function getViewData(ServerRequestInterface $backendRequest, array $state): array
    {
        /** @var \TYPO3\CMS\Backend\Routing\UriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        /** @var array<int, array<string, mixed>> $connections */
        $connections = [];

        foreach ($this->aiConnectionRepository->findAll() as $connection) {
            /** @var int $uid */
            $uid = (int)$connection->getUid();

            $connections[] = [
                'uid' => $uid,
                'testIdentifier' => 'ai:' . $uid,
                'title' => $connection->getTitle(),
                'kind' => 'AI',
                'type' => $connection->getConnectorIdentifier(),
                'baseUrl' => $connection->getBaseUrl(),
                'embeddingDimension' => $connection->getEmbeddingDimension(),
                'hidden' => false,
                'editUrl' => (string)$uriBuilder->buildUriFromRoute('record_edit', [
                    'edit' => [
                        'tx_aiassistant_connection_ai' => [
                            $uid => 'edit',
                        ],
                    ],
                    'returnUrl' => (string)$backendRequest->getUri(),
                ]),
            ];
        }

        foreach ($this->vectorStoreConnectionRepository->findAll() as $connection) {
            /** @var int $uid */
            $uid = (int)$connection->getUid();

            $connections[] = [
                'uid' => $uid,
                'testIdentifier' => 'vector:' . $uid,
                'title' => $connection->getTitle(),
                'kind' => 'Vector store',
                'type' => $connection->getConnectorIdentifier(),
                'baseUrl' => $connection->getEndpoint(),
                'embeddingDimension' => null,
                'hidden' => false,
                'editUrl' => (string)$uriBuilder->buildUriFromRoute('record_edit', [
                    'edit' => [
                        'tx_aiassistant_connection_vector_database' => [
                            $uid => 'edit',
                        ],
                    ],
                    'returnUrl' => (string)$backendRequest->getUri(),
                ]),
            ];
        }

        foreach ($this->mcpConnectionRepository->findAll() as $connection) {
            if (!$connection instanceof McpConnection) {
                continue;
            }

            $uid = (int)$connection->getUid();
            $connections[] = [
                'uid' => $uid,
                'testIdentifier' => 'mcp:' . $uid,
                'title' => $connection->getTitle(),
                'kind' => 'MCP',
                'type' => $connection->getTransport(),
                'baseUrl' => $connection->getEndpoint(),
                'embeddingDimension' => null,
                'hidden' => false,
                'editUrl' => (string)$uriBuilder->buildUriFromRoute('record_edit', [
                    'edit' => ['tx_aiassistant_connection_mcp' => [$uid => 'edit']],
                    'returnUrl' => (string)$backendRequest->getUri(),
                ]),
            ];
        }

        /** @var array<int, array<string, mixed>> $assistants */
        $assistants = [];
        foreach ($this->assistantProfileRepository->findAll() as $assistant) {
            if (!$assistant instanceof AssistantProfile) {
                continue;
            }

            $uid = (int)$assistant->getUid();
            $aiConnection = $assistant->getAiConnection();
            $vectorStoreConnection = $assistant->getVectorStoreConnection();
            $measuredEmbeddingDimension = null;

            $assistantTestResult = $state['assistantTestResult'] ?? null;
            if (
                is_array($assistantTestResult)
                && (int)($assistantTestResult['uid'] ?? 0) === $uid
                && (int)($assistantTestResult['embeddingDimension'] ?? 0) > 0
            ) {
                $measuredEmbeddingDimension = (int)$assistantTestResult['embeddingDimension'];
            }

            $connectionTestResult = $state['connectionTestResult'] ?? null;
            if (
                $aiConnection instanceof AiConnection
                && is_array($connectionTestResult)
                && ($connectionTestResult['identifier'] ?? '') === 'ai:' . (int)$aiConnection->getUid()
                && (int)($connectionTestResult['embeddingDimension'] ?? 0) > 0
            ) {
                $measuredEmbeddingDimension = (int)$connectionTestResult['embeddingDimension'];
            }

            $assistants[] = [
                'uid' => $uid,
                'title' => $assistant->getTitle(),
                'aiConnection' => $aiConnection?->getTitle() ?? 'Not configured',
                'aiEmbeddingModel' => $aiConnection?->getEmbeddingModel() ?? '',
                'aiConfiguredEmbeddingDimension' => $aiConnection?->getEmbeddingDimension() ?? 0,
                'aiMeasuredEmbeddingDimension' => $measuredEmbeddingDimension,
                'vectorStoreConnection' => $vectorStoreConnection?->getTitle() ?? 'Not configured',
                'vectorStoreDistance' => $vectorStoreConnection?->getDistance() ?? '',
                'stepCount' => $assistant->getChatPipelineSteps()->count(),
                'hidden' => $assistant->isHidden(),
                'editUrl' => (string)$uriBuilder->buildUriFromRoute('record_edit', [
                    'edit' => [
                        'tx_aiassistant_assistant_profile' => [
                            $uid => 'edit',
                        ],
                    ],
                    'returnUrl' => (string)$backendRequest->getUri(),
                ]),
            ];
        }
        usort(
            $assistants,
            static fn (array $left, array $right): int => [$left['title'], $left['uid']] <=> [$right['title'], $right['uid']],
        );

        return [
            'connections' => $connections,
            'connectionTestResult' => $state['connectionTestResult'] ?? null,
            'assistants' => $assistants,
            'assistantTestResult' => $state['assistantTestResult'] ?? null,
        ];
    }
}
