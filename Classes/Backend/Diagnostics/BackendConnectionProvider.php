<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension ai_assistant.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiAssistant\Backend\Diagnostics;

use Madj2k\AiAssistant\Connection\Domain\Model\AiConnection;
use Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection;
use Madj2k\AiAssistant\Connection\Domain\Repository\AiConnectionRepository;
use Madj2k\AiAssistant\Connection\Domain\Repository\VectorStoreConnectionRepository;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class BackendConnectionProvider
 *
 * Builds backend view data for configured connection records.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class BackendConnectionProvider
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Connection\Domain\Repository\AiConnectionRepository $aiConnectionRepository AI connection repository.
     * @param \Madj2k\AiAssistant\Connection\Domain\Repository\VectorStoreConnectionRepository $vectorStoreConnectionRepository Vector store connection repository.
     */
    public function __construct(
        private readonly AiConnectionRepository $aiConnectionRepository,
        private readonly VectorStoreConnectionRepository $vectorStoreConnectionRepository
    ) {
    }


    /**
     * Builds connection diagnostics view data.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $backendRequest Backend request.
     * @param array<string, mixed> $state Module state.
     * @return array<string, mixed> View data.
     */
    public function getViewData(ServerRequestInterface $backendRequest, array $state): array
    {
        /** @var \TYPO3\CMS\Backend\Routing\UriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        /** @var array<int, array<string, mixed>> $connections */
        $connections = [];

        foreach ($this->aiConnectionRepository->findAll() as $connection) {
            if (!$connection instanceof AiConnection) {
                continue;
            }

            /** @var int $uid */
            $uid = (int)$connection->getUid();

            $connections[] = [
                'uid' => $uid,
                'testIdentifier' => 'ai:' . $uid,
                'title' => $connection->getTitle(),
                'kind' => 'AI',
                'type' => $connection->getConnectorIdentifier(),
                'baseUrl' => $connection->getBaseUrl(),
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
            if (!$connection instanceof VectorStoreConnection) {
                continue;
            }

            /** @var int $uid */
            $uid = (int)$connection->getUid();

            $connections[] = [
                'uid' => $uid,
                'testIdentifier' => 'vector:' . $uid,
                'title' => $connection->getTitle(),
                'kind' => 'Vector store',
                'type' => $connection->getConnectorIdentifier(),
                'baseUrl' => $connection->getEndpoint(),
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

        return [
            'connections' => $connections,
            'connectionTestResult' => $state['connectionTestResult'] ?? null,
        ];
    }
}
