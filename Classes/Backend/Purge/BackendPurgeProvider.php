<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension ai_assistant.
 *
 * For the full copyright information, please read the LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiAssistant\Backend\Purge;

use Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection;
use Madj2k\AiAssistant\Connection\Domain\Repository\VectorStoreConnectionRepository;
use Madj2k\AiCore\Connection\Resolver\VectorStoreConnectorResolver as VectorStoreConnectorRegistry;
use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig;
use Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerConfigRepository;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Builds purge candidates grouped by vector database connection and collection.
 */
final class BackendPurgeProvider
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Connection\Domain\Repository\VectorStoreConnectionRepository $vectorStoreConnectionRepository Vector store connection repository.
     * @param \Madj2k\AiCore\Connection\Resolver\VectorStoreConnectorResolver $vectorStoreConnectorRegistry Vector store connector registry.
     * @param \Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerConfigRepository $indexerConfigRepository Indexer repository.
     * @param \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool Connection pool.
     */
    public function __construct(
        private readonly VectorStoreConnectionRepository $vectorStoreConnectionRepository,
        private readonly VectorStoreConnectorRegistry $vectorStoreConnectorRegistry,
        private readonly IndexerConfigRepository $indexerConfigRepository,
        private readonly ConnectionPool $connectionPool
    ) {
    }


    /**
     * Builds purge view data.
     *
     * @param object $extbaseRequest Extbase request.
     * @param \Psr\Http\Message\ServerRequestInterface $backendRequest Backend request.
     * @param array<string, mixed> $state Module state.
     * @return array<string, mixed> View data.
     */
    public function getViewData(object $extbaseRequest, ServerRequestInterface $backendRequest, array $state): array
    {
        $connections = $this->getConnectionsByUid();
        $candidates = $this->getSourceStateCandidates($connections);

        $remoteCandidates = $this->getRemoteCollectionCandidates($connections);

        foreach ($this->getConfiguredCollectionCandidates($connections) as $key => $candidate) {
            if (isset($candidates[$key])) {
                $candidates[$key]['indexerCount'] += $candidate['indexerCount'];
                $candidates[$key]['indexerTitles'] = array_values(array_unique(array_merge(
                    $candidates[$key]['indexerTitles'],
                    $candidate['indexerTitles']
                )));
                continue;
            }

            $candidates[$key] = $candidate;
        }

        foreach ($remoteCandidates as $key => $candidate) {
            if (!isset($candidates[$key])) {
                if (($candidate['remoteError'] ?? '') !== '') {
                    $candidates[$key] = $candidate;
                }
                continue;
            }

            $candidates[$key]['remoteExists'] = (bool)($candidate['remoteExists'] ?? false);
            if (($candidate['remoteError'] ?? '') !== '') {
                $candidates[$key]['remoteError'] = $candidate['remoteError'];
            }
        }

        usort(
            $candidates,
            static fn (array $left, array $right): int => [
                strtolower((string)$left['connectionTitle']),
                (int)$left['connectionUid'],
                strtolower((string)$left['collection']),
            ] <=> [
                strtolower((string)$right['connectionTitle']),
                (int)$right['connectionUid'],
                strtolower((string)$right['collection']),
            ]
        );

        return [
            'purgeCollections' => array_values($candidates),
            'purgeResult' => is_array($state['purgeResult'] ?? null) ? $state['purgeResult'] : [],
        ];
    }


    /**
     * Returns vector store connections by uid.
     *
     * @return array<int, \Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection> Connections.
     */
    private function getConnectionsByUid(): array
    {
        $connections = [];
        foreach ($this->vectorStoreConnectionRepository->findAll() as $connection) {
            if (!$connection instanceof VectorStoreConnection || $connection->getUid() === null) {
                continue;
            }

            $connections[(int)$connection->getUid()] = $connection;
        }

        return $connections;
    }


    /**
     * Returns candidates from remote vector stores.
     *
     * @param array<int, \Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection> $connections Connections.
     * @return array<string, array<string, mixed>> Candidates.
     */
    private function getRemoteCollectionCandidates(array $connections): array
    {
        $candidates = [];
        foreach ($connections as $connectionUid => $connection) {
            try {
                $connector = $this->vectorStoreConnectorRegistry->get($connection->getConnectorIdentifier());
                foreach ($connector->listCollections($connection) as $collection) {
                    $collection = trim($collection);
                    if ($collection === '') {
                        continue;
                    }

                    $key = $this->buildKey($connectionUid, $collection);
                    $candidates[$key] = $this->buildCandidate(
                        $connectionUid,
                        $collection,
                        $connection,
                        0,
                        0,
                        [],
                        true
                    );
                }
            } catch (\Throwable $exception) {
                $key = $this->buildKey($connectionUid, '__connection_error__');
                $candidates[$key] = [
                    'key' => $key,
                    'connectionUid' => $connectionUid,
                    'connectionTitle' => $connection->getTitle(),
                    'connectorIdentifier' => $connection->getConnectorIdentifier(),
                    'endpoint' => $connection->getEndpoint(),
                    'collection' => '',
                    'sourceCount' => 0,
                    'indexerCount' => 0,
                    'indexerTitles' => [],
                    'canPurge' => false,
                    'remoteExists' => false,
                    'remoteError' => $exception->getMessage(),
                ];
            }
        }

        return $candidates;
    }


    /**
     * Returns candidates from persisted source state.
     *
     * @param array<int, \Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection> $connections Connections.
     * @return array<string, array<string, mixed>> Candidates.
     */
    private function getSourceStateCandidates(array $connections): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_aiassistant_indexer_source');
        $rows = $queryBuilder
            ->select('vector_store_connection', 'collection')
            ->addSelectLiteral('COUNT(*) AS source_count')
            ->from('tx_aiassistant_indexer_source')
            ->where(
                $queryBuilder->expr()->neq('collection', $queryBuilder->createNamedParameter(''))
            )
            ->groupBy('vector_store_connection', 'collection')
            ->executeQuery()
            ->fetchAllAssociative();

        $candidates = [];
        foreach ($rows as $row) {
            $connectionUid = (int)($row['vector_store_connection'] ?? 0);
            $collection = trim((string)($row['collection'] ?? ''));
            if ($collection === '') {
                continue;
            }

            $key = $this->buildKey($connectionUid, $collection);
            $candidates[$key] = $this->buildCandidate(
                $connectionUid,
                $collection,
                $connections[$connectionUid] ?? null,
                (int)($row['source_count'] ?? 0),
                0,
                [],
                false
            );
        }

        return $candidates;
    }


    /**
     * Returns candidates from indexer configuration even when no source state exists yet.
     *
     * @param array<int, \Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection> $connections Connections.
     * @return array<string, array<string, mixed>> Candidates.
     */
    private function getConfiguredCollectionCandidates(array $connections): array
    {
        $candidates = [];
        foreach ($this->indexerConfigRepository->findAll() as $configuration) {
            if (!$configuration instanceof IndexerConfig) {
                continue;
            }

            $connection = $configuration->getVectorStoreConnection();
            if (!$connection instanceof VectorStoreConnection || $connection->getUid() === null) {
                continue;
            }

            $connectionUid = (int)$connection->getUid();
            $collection = trim($configuration->getCollection());
            if ($collection === '') {
                $collection = trim($connection->getDefaultCollection());
            }

            if ($collection === '') {
                continue;
            }

            $key = $this->buildKey($connectionUid, $collection);
            if (!isset($candidates[$key])) {
                $candidates[$key] = $this->buildCandidate(
                    $connectionUid,
                    $collection,
                    $connections[$connectionUid] ?? $connection,
                    0,
                    0,
                    [],
                    false
                );
            }

            $candidates[$key]['indexerCount']++;
            $candidates[$key]['indexerTitles'][] = trim($configuration->getTitle()) !== ''
                ? $configuration->getTitle()
                : '#' . (int)$configuration->getUid();
        }

        return $candidates;
    }


    /**
     * Builds one purge candidate row.
     *
     * @param int $connectionUid Connection uid.
     * @param string $collection Collection.
     * @param \Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection|null $connection Connection.
     * @param int $sourceCount Source state count.
     * @param int $indexerCount Indexer count.
     * @param array<int, string> $indexerTitles Indexer titles.
     * @param bool $remoteExists Whether the collection exists remotely.
     * @return array<string, mixed> Candidate.
     */
    private function buildCandidate(
        int $connectionUid,
        string $collection,
        ?VectorStoreConnection $connection,
        int $sourceCount,
        int $indexerCount,
        array $indexerTitles,
        bool $remoteExists
    ): array {
        return [
            'key' => $this->buildKey($connectionUid, $collection),
            'connectionUid' => $connectionUid,
            'connectionTitle' => $connection instanceof VectorStoreConnection ? $connection->getTitle() : $this->translate('templates_backend_config.purge_unknown_connection'),
            'connectorIdentifier' => $connection instanceof VectorStoreConnection ? $connection->getConnectorIdentifier() : '',
            'endpoint' => $connection instanceof VectorStoreConnection ? $connection->getEndpoint() : '',
            'collection' => $collection,
            'sourceCount' => $sourceCount,
            'indexerCount' => $indexerCount,
            'indexerTitles' => $indexerTitles,
            'canPurge' => $connection instanceof VectorStoreConnection && $connectionUid > 0,
            'remoteExists' => $remoteExists,
            'remoteError' => '',
        ];
    }


    /**
     * Builds a stable candidate key.
     *
     * @param int $connectionUid Connection uid.
     * @param string $collection Collection.
     * @return string Candidate key.
     */
    private function buildKey(int $connectionUid, string $collection): string
    {
        return sha1($connectionUid . '|' . $collection);
    }


    /**
     * Translates a backend module label.
     *
     * @param string $key Language key.
     * @return string Translated label.
     */
    private function translate(string $key): string
    {
        $label = LocalizationUtility::translate(
            'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_config.xlf:' . $key
        );

        return (string)($label ?? $key);
    }
}
