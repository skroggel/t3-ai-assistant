<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension ai_assistant.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiAssistant\Indexing\Indexer;

use Madj2k\AiAssistant\Connection\Ai\DTO\EmbeddingRequest;
use Madj2k\AiAssistant\Connection\Registry\AiConnectorRegistry;
use Madj2k\AiAssistant\Connection\Registry\VectorStoreConnectorRegistry;
use Madj2k\AiAssistant\Connection\VectorStore\DTO\VectorCollection;
use Madj2k\AiAssistant\Connection\VectorStore\DTO\VectorDocument;
use Madj2k\AiAssistant\Exception\VectorDatabaseException;
use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig;
use Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerConfigRepository;
use Madj2k\AiAssistant\Indexing\DTO\IndexableDocument;
use Madj2k\AiAssistant\Indexing\DTO\IndexingRequest;
use Madj2k\AiAssistant\Indexing\DTO\IndexingResult;
use Madj2k\AiAssistant\Indexing\Service\SourceStateService;
use Madj2k\AiAssistant\Indexing\Service\TextChunkerService;

/**
 * Class AbstractIndexer
 *
 * Provides shared configuration loading, collection resolution, chunking and source state handling for concrete indexers.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class AbstractIndexer implements IndexerInterface
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Connection\Registry\AiConnectorRegistry $aiConnectorRegistry AI connector registry.
     * @param \Madj2k\AiAssistant\Connection\Registry\VectorStoreConnectorRegistry $vectorStoreConnectorRegistry Vector store connector registry.
     * @param \Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerConfigRepository $indexerConfigRepository Indexer configuration repository.
     * @param \Madj2k\AiAssistant\Indexing\Service\TextChunkerService $textChunkerService Text chunker service.
     * @param \Madj2k\AiAssistant\Indexing\Service\SourceStateService $sourceStateService Index source state service.
     */
    public function __construct(
        protected readonly AiConnectorRegistry $aiConnectorRegistry,
        protected readonly VectorStoreConnectorRegistry $vectorStoreConnectorRegistry,
        protected readonly IndexerConfigRepository $indexerConfigRepository,
        protected readonly TextChunkerService $textChunkerService,
        protected readonly SourceStateService $sourceStateService
    ) {
    }


    /**
     * Resolves indexer configurations for this indexer.
     *
     * @param int|null $indexerUid Optional indexer configuration uid.
     * @return array<int, \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig> Indexer configurations.
     */
    protected function resolveConfigurations(?int $indexerUid = null): array
    {
        if (($indexerUid ?? 0) > 0) {
            $configuration = $this->indexerConfigRepository->findByUid((int)$indexerUid);

            if (!$configuration instanceof IndexerConfig) {
                return [];
            }

            return [$configuration];
        }

        /** @var iterable<\Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig> $configurations */
        $configurations = $this->indexerConfigRepository->findByIndexerIdentifier($this->getIdentifier());

        /** @var array<int, \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig> $resolvedConfigurations */
        $resolvedConfigurations = [];

        foreach ($configurations as $configuration) {
            if ($configuration instanceof IndexerConfig) {
                $resolvedConfigurations[] = $configuration;
            }
        }

        return $resolvedConfigurations;
    }


    /**
     * Resolves the target collection.
     *
     * A non-empty override wins. If no override is given, the collection from the indexer configuration is used.
     * If that is empty, the default collection of the selected vector store connection is used.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration Indexer configuration.
     * @param string $collectionOverride Optional collection override.
     * @return string Collection name.
     */
    protected function resolveCollection(IndexerConfig $configuration, string $collectionOverride = ''): string
    {
        $collectionOverride = trim($collectionOverride);

        if ($collectionOverride !== '') {
            return $collectionOverride;
        }

        $collection = trim($configuration->getCollection());

        if ($collection !== '') {
            return $collection;
        }

        $vectorStoreConnection = $configuration->getVectorStoreConnection();

        if ($vectorStoreConnection !== null && method_exists($vectorStoreConnection, 'getDefaultCollection')) {
            return trim((string)$vectorStoreConnection->getDefaultCollection());
        }

        return '';
    }


    /**
     * Indexes one document with incremental source-state handling.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration Configuration.
     * @param \Madj2k\AiAssistant\Indexing\DTO\IndexableDocument $document Document.
     * @param \Madj2k\AiAssistant\Indexing\DTO\IndexingRequest $request Request.
     * @param \Madj2k\AiAssistant\Indexing\DTO\IndexingResult $result Result.
     * @return void
     */
    protected function indexDocument(
        IndexerConfig $configuration,
        IndexableDocument $document,
        IndexingRequest $request,
        IndexingResult $result
    ): void {
        if ($document->getContent() === '') {
            $result->increaseSkipped();
            return;
        }

        /** @var string $collection */
        $collection = $this->resolveCollection($configuration, $request->getCollection());

        if ($collection === '') {
            $result->increaseSkipped();
            return;
        }

        if ($this->sourceStateService->shouldSkip($configuration, $document, $collection, $request->isOnlyChanged())) {
            $result->increaseSkipped();
            return;
        }

        try {
            $chunksWritten = $this->indexDocumentIntoVectorStore(
                $configuration,
                $document,
                $collection,
                $request->isDryRun()
            );

            if ($chunksWritten === 0) {
                $result->increaseSkipped();
                return;
            }

            if (!$request->isDryRun()) {
                $this->sourceStateService->markIndexed($configuration, $document, $collection);
            }

            $result->increaseIndexed();
            $result->increaseChunksTotal($chunksWritten);

        } catch (\Throwable $exception) {
            $result->increaseFailed();
            $result->addDetail('source_error_' . $result->getProcessed(), [
                'source_type' => $document->getMetadata()->getSourceType(),
                'source_identifier' => $document->getMetadata()->getSourceIdentifier(),
                'message' => $exception->getMessage(),
            ]);

            if (!$request->isDryRun()) {
                $this->sourceStateService->markFailed($configuration, $document, $collection, $exception);
            }
        }
    }


    /**
     * Checks whether the batch limit has been reached.
     *
     * @param \Madj2k\AiAssistant\Indexing\DTO\IndexingRequest $request Request.
     * @param \Madj2k\AiAssistant\Indexing\DTO\IndexingResult $result Result.
     * @return bool Limit reached flag.
     */
    protected function isLimitReached(IndexingRequest $request, IndexingResult $result): bool
    {
        return $request->getLimit() !== null && $result->getProcessed() >= $request->getLimit();
    }


    /**
     * Indexes a document into the configured vector store.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration Indexer configuration.
     * @param \Madj2k\AiAssistant\Indexing\DTO\IndexableDocument $document Indexable document.
     * @param string $collectionName Collection name.
     * @param bool $dryRun Whether this is a dry run.
     * @return int Number of chunks prepared or written.
     */
    protected function indexDocumentIntoVectorStore(
        IndexerConfig $configuration,
        IndexableDocument $document,
        string $collectionName,
        bool $dryRun = false
    ): int {
        /**
         * @var array<int, string> $chunks
         */
        $chunks = $this->textChunkerService->chunk(
            $document->getContent(),
            $this->resolveChunkSize($configuration),
            $this->resolveChunkOverlap($configuration),
            $this->resolveMaxChunks($configuration),
            $this->resolveMinChunkChars($configuration)
        );

        if ($chunks === []) {
            return 0;
        }

        if ($dryRun) {
            return count($chunks);
        }

        /** @var \Madj2k\AiAssistant\Connection\VectorStore\DTO\VectorCollection $collection */
        $collection = new VectorCollection($collectionName);

        /**
         * @var array<int, \Madj2k\AiAssistant\Connection\Ai\DTO\EmbeddingRequest> $embeddingRequests
         */
        $embeddingRequests = [];
        foreach ($chunks as $chunkText) {
            $embeddingRequests[] = new EmbeddingRequest($chunkText);
        }

        $aiConnection = $configuration->getAiConnection();
        if ($aiConnection === null) {
            throw new \RuntimeException('No AI connection configured for indexer.', 1780573401);
        }

        /**
         * @var array<int, \Madj2k\AiAssistant\Connection\Ai\DTO\EmbeddingResponse> $embeddingResponses
         */
        $embeddingResponses = $this->aiConnectorRegistry
            ->get($aiConnection->getConnectorIdentifier())
            ->embedBatch($aiConnection, $embeddingRequests);

        /**
         * @var array<int, \Madj2k\AiAssistant\Connection\VectorStore\DTO\VectorDocument> $vectorDocuments
         */
        $vectorDocuments = [];
        $sourceHash = $this->sourceStateService->createSourceHash($document);

        foreach ($chunks as $index => $chunkText) {
            /** @var array<int, float> $embedding */
            $embedding = isset($embeddingResponses[$index])
                ? $embeddingResponses[$index]->getEmbedding()
                : [];

            if ($embedding === []) {
                continue;
            }

            $vectorDocuments[] = new VectorDocument(
                id: $this->createVectorDocumentId($sourceHash, $index),
                vector: $embedding,
                payload: $document->createPayload($index, $chunkText, $sourceHash),
                vectorName: $collection->getName()
            );
        }

        if ($vectorDocuments === []) {
            return 0;
        }

        # delete old element
        $this->deleteDocumentFromVectorStore($configuration, $document, $collection, $dryRun);

        $vectorStoreConnection = $configuration->getVectorStoreConnection();
        if ($vectorStoreConnection === null) {
            throw new \RuntimeException('No vector store connection configured for indexer.', 1780573402);
        }

        $vectorStoreConnector = $this->vectorStoreConnectorRegistry->get(
            $vectorStoreConnection->getConnectorIdentifier()
        );
        return $vectorStoreConnector
            ->upsert($vectorStoreConnection, $collection, $vectorDocuments)
            ->getWritten();
    }


    /**
     * Deletes a document from the configured vector store by source hash.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration Indexer configuration.
     * @param \Madj2k\AiAssistant\Indexing\DTO\IndexableDocument $document
     * @param \Madj2k\AiAssistant\Connection\VectorStore\DTO\VectorCollection $collection Collection name.
     * @param bool $dryRun Whether this is a dry run.
     * @return void
     * @throws VectorDatabaseException
     */
    protected function deleteDocumentFromVectorStore(
        IndexerConfig $configuration,
        IndexableDocument $document,
        VectorCollection $collection,
        bool $dryRun = false
    ): void {

        $sourceHash = $this->sourceStateService->createSourceHash($document);
        if ($dryRun || $sourceHash === '') {
            return;
        }

        $vectorStoreConnection = $configuration->getVectorStoreConnection();
        if ($vectorStoreConnection === null) {
            throw new \RuntimeException('No vector store connection configured for indexer.', 1780573403);
        }

        $sourceHashes = $this->sourceStateService->getStorageSourceHashesForDeletion($configuration, $document, $collection->getName());
        $vectorStoreConnector = $this->vectorStoreConnectorRegistry->get(
            $vectorStoreConnection->getConnectorIdentifier()
        );

        foreach ($sourceHashes as $sourceHashForDeletion) {
            $vectorStoreConnector->deleteBySourceHash($vectorStoreConnection, $collection, $sourceHashForDeletion);
        }
    }


    /**
     * Resolves the chunk size.
     *
     * A value of 0 means that the default from \Madj2k\AiAssistant\Indexing\Service\TextChunkerService is used.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration Indexer configuration.
     * @return int|null Chunk size or null for default.
     */
    protected function resolveChunkSize(IndexerConfig $configuration): ?int
    {
        $chunkSize = method_exists($configuration, 'getChunkSize')
            ? (int)$configuration->getChunkSize()
            : 0;

        return $chunkSize > 0 ? $chunkSize : null;
    }


    /**
     * Resolves the chunk overlap.
     *
     * A value of 0 means that the default from \Madj2k\AiAssistant\Indexing\Service\TextChunkerService is used.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration Indexer configuration.
     * @return int|null Chunk overlap or null for default.
     */
    protected function resolveChunkOverlap(IndexerConfig $configuration): ?int
    {
        $chunkOverlap = method_exists($configuration, 'getChunkOverlap')
            ? (int)$configuration->getChunkOverlap()
            : 0;

        return $chunkOverlap > 0 ? $chunkOverlap : null;
    }


    /**
     * Resolves the maximum number of chunks.
     *
     * A value of 0 means that no explicit maximum is applied.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration Indexer configuration.
     * @return int|null Maximum chunks or null for unlimited/default.
     */
    protected function resolveMaxChunks(IndexerConfig $configuration): ?int
    {
        $maxChunks = method_exists($configuration, 'getMaxChunks')
            ? (int)$configuration->getMaxChunks()
            : 0;

        return $maxChunks > 0 ? $maxChunks : null;
    }


    /**
     * Resolves the minimum chunk length.
     *
     * A value of 0 means that the default from \Madj2k\AiAssistant\Indexing\Service\TextChunkerService is used.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration Indexer configuration.
     * @return int|null Minimum chunk length or null for default.
     */
    protected function resolveMinChunkChars(IndexerConfig $configuration): ?int
    {
        $minChunkChars = method_exists($configuration, 'getMinChunkChars')
            ? (int)$configuration->getMinChunkChars()
            : 0;

        return $minChunkChars > 0 ? $minChunkChars : null;
    }


    /**
     * Creates a stable vector document identifier. Has to be an UUID.
     *
     * @param string $sourceHash Stable source hash.
     * @param int $chunkIndex Chunk index.
     * @return string Vector document identifier.
     */
    protected function createVectorDocumentId(string $sourceHash, int $chunkIndex): string
    {
        $hash = md5(trim($sourceHash) . ':' . $chunkIndex);
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 12, 4),
            substr($hash, 16, 4),
            substr($hash, 20, 12)
        );
    }
}
