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

use Madj2k\AiCore\DTO\DocumentMetadata;
use Madj2k\AiCore\Indexing\Indexer\AbstractIndexer as CoreAbstractIndexer;
use Madj2k\AiCore\Indexing\VectorDocumentIndexer;
use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig;
use Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerConfigRepository;
use Madj2k\AiAssistant\Indexing\Service\SourceStateService;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AbstractIndexer
 *
 * TYPO3 adapter shared by the concrete source indexers.
 *
 * Configuration lookup and persisted source state remain here; transforming an
 * indexable document into vectors is delegated to ai-core.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
abstract class AbstractIndexer extends CoreAbstractIndexer
{
    protected readonly LoggerInterface $indexingLogger;

    /**
     * @param \Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerConfigRepository $indexerConfigRepository
     * @param \Madj2k\AiAssistant\Indexing\Service\SourceStateService $sourceStateService
     * @param \Madj2k\AiCore\Indexing\VectorDocumentIndexer $vectorDocumentIndexer
     * @param \TYPO3\CMS\Core\Log\LogManager|null $logManager
     */
    public function __construct(
        protected readonly IndexerConfigRepository $indexerConfigRepository,
        SourceStateService $sourceStateService,
        VectorDocumentIndexer $vectorDocumentIndexer,
        ?LogManager $logManager = null,
    ) {
        parent::__construct($sourceStateService, $vectorDocumentIndexer);
        $this->indexingLogger = ($logManager ?? GeneralUtility::makeInstance(LogManager::class))->getLogger(static::class);
    }

    /**
     * @param int|null $indexerUid
     * @return array
     */
    protected function resolveConfigurations(?int $indexerUid = null): array
    {
        $this->indexingLogger->debug('Resolving indexer configurations.', [
            'indexer' => $this->getIdentifier(),
            'indexer_uid' => $indexerUid,
        ]);
        if (($indexerUid ?? 0) > 0) {
            $configuration = $this->indexerConfigRepository->findByUid((int)$indexerUid);

            if (!$configuration instanceof IndexerConfig) {
                $this->indexingLogger->warning('Requested indexer configuration was not found.', [
                    'indexer_uid' => $indexerUid,
                ]);
            }

            return $configuration instanceof IndexerConfig ? [$configuration] : [];
        }

        $resolvedConfigurations = [];
        foreach ($this->indexerConfigRepository->findByIndexerIdentifier($this->getIdentifier()) as $configuration) {
            if ($configuration instanceof IndexerConfig) {
                $resolvedConfigurations[] = $configuration;
            }
        }

        $this->indexingLogger->debug('Indexer configurations resolved.', [
            'count' => count($resolvedConfigurations),
        ]);

        return $resolvedConfigurations;
    }

    /**
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration
     * @param string $collectionOverride
     * @return string
     */
    protected function resolveCollection(IndexerConfig $configuration, string $collectionOverride = ''): string
    {
        $collection = $this->vectorDocumentIndexer->resolveCollection($configuration, $collectionOverride);
        $this->indexingLogger->debug('Resolved indexer collection.', [
            'configuration_uid' => $configuration->getUid(),
            'collection_override' => $collectionOverride !== '',
            'collection' => $collection,
        ]);

        return $collection;
    }

    /**
     * Adds metadata configured on the indexer record to the document payload.
     *
     * @param DocumentMetadata $metadata Document metadata.
     * @param IndexerConfig $configuration Indexer configuration.
     * @return bool Whether the document was indexed.
     */
    protected function addAdditionalMetadata(
        DocumentMetadata $metadata,
        IndexerConfig $configuration,
    ): void {
        foreach ($configuration->getAdditionalMetadataArray() as $key => $value) {
            if (is_string($key) && trim($key) !== '') {
                $metadata->addAdditional($key, $value);
            }
        }
    }

}
