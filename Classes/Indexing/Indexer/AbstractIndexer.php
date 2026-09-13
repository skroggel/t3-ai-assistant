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

namespace Madj2k\AiAssistant\Indexing\Indexer;

use Madj2k\AiCore\Indexing\VectorDocumentIndexer;
use Madj2k\AiCore\Indexing\DTO\IndexableDocument;
use Madj2k\AiCore\Indexing\DTO\IndexingRequest;
use Madj2k\AiCore\Indexing\DTO\IndexingResult;
use Madj2k\AiCore\Indexing\Indexer\IndexerInterface;
use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig;
use Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerConfigRepository;
use Madj2k\AiAssistant\Indexing\Service\SourceStateService;

/**
 * TYPO3 adapter shared by the concrete source indexers.
 *
 * Configuration lookup and persisted source state remain here; transforming an
 * indexable document into vectors is delegated to ai-core.
 */
abstract class AbstractIndexer implements IndexerInterface
{
    public function __construct(
        protected readonly IndexerConfigRepository $indexerConfigRepository,
        protected readonly SourceStateService $sourceStateService,
        protected readonly VectorDocumentIndexer $vectorDocumentIndexer,
    ) {
    }

    /** @return array<int, IndexerConfig> */
    protected function resolveConfigurations(?int $indexerUid = null): array
    {
        if (($indexerUid ?? 0) > 0) {
            $configuration = $this->indexerConfigRepository->findByUid((int)$indexerUid);

            return $configuration instanceof IndexerConfig ? [$configuration] : [];
        }

        $resolvedConfigurations = [];
        foreach ($this->indexerConfigRepository->findByIndexerIdentifier($this->getIdentifier()) as $configuration) {
            if ($configuration instanceof IndexerConfig) {
                $resolvedConfigurations[] = $configuration;
            }
        }

        return $resolvedConfigurations;
    }

    protected function resolveCollection(IndexerConfig $configuration, string $collectionOverride = ''): string
    {
        return $this->vectorDocumentIndexer->resolveCollection($configuration, $collectionOverride);
    }

    protected function indexDocument(
        IndexerConfig $configuration,
        IndexableDocument $document,
        IndexingRequest $request,
        IndexingResult $result,
    ): void {
        if ($document->getContent() === '') {
            $result->increaseSkipped();
            return;
        }

        $collection = $this->resolveCollection($configuration, $request->getCollection());
        if ($collection === '') {
            $result->increaseSkipped();
            return;
        }

        if ($this->sourceStateService->shouldSkip(
            $configuration,
            $document,
            $collection,
            $request->isOnlyChanged(),
        )) {
            $result->increaseSkipped();
            return;
        }

        try {
            $sourceHashesToDelete = $this->sourceStateService->getStorageSourceHashesForDeletion(
                $configuration,
                $document,
                $collection,
            );
            $chunksWritten = $this->vectorDocumentIndexer->index(
                $configuration,
                $document,
                $collection,
                $request->isDryRun(),
                $sourceHashesToDelete,
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

    protected function isLimitReached(IndexingRequest $request, IndexingResult $result): bool
    {
        return $request->getLimit() !== null && $result->getProcessed() >= $request->getLimit();
    }
}
