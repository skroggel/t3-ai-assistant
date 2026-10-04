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

namespace Madj2k\AiAssistant\Indexing\Service;

use Madj2k\AiCore\Indexing\Identity\SourceIdentityGenerator;
use Madj2k\AiCore\Indexing\DTO\IndexableDocument;
use Madj2k\AiCore\Indexing\Configuration\IndexingConfigurationInterface;
use Madj2k\AiCore\Indexing\State\SourceStateInterface;
use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig;
use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerSource;
use Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerSourceRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Class SourceStateService
 *
 * Maintains source state records for incremental and batched indexing.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final readonly class SourceStateService implements SourceStateInterface
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerSourceRepository $IndexerSourceRepository Index source repository.
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager Persistence manager.
     */
    public function __construct(
        private IndexerSourceRepository $IndexerSourceRepository,
        private PersistenceManager $persistenceManager,
        private SourceIdentityGenerator $sourceIdentityGenerator,
    ) {
    }


    /**
     * Returns whether a document can be skipped because the source is unchanged.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration Indexer configuration.
     * @param \Madj2k\AiCore\Indexing\DTO\IndexableDocument $document Document.
     * @param string $collection Collection name.
     * @param bool $onlyChanged Whether unchanged sources should be skipped.
     * @return bool Skip flag.
     */
    public function shouldSkip(
        IndexingConfigurationInterface $configuration,
        IndexableDocument $document,
        string $collection,
        bool $onlyChanged
    ): bool {

        if (!$onlyChanged) {
            return false;
        }

        $state = $this->findState($configuration, $document, $collection);
        if (!$state instanceof IndexerSource) {
            return false;
        }

        return $state->getStatus() === 'indexed'
            && $state->getContentChecksum() === $document->getContentHash();
    }


    /**
     * Marks a document as indexed.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration Indexer configuration.
     * @param \Madj2k\AiCore\Indexing\DTO\IndexableDocument $document Document.
     * @param string $collection Collection name.
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function markIndexed(
        IndexingConfigurationInterface $configuration,
        IndexableDocument $document,
        string $collection
    ): void {

        $state = $this->getOrCreateState($configuration, $document, $collection);
        $metadata = $document->getMetadata();

        $state->setStatus('indexed');
        $state->setLastIndexed(time());
        $state->setLastChanged($metadata->getChangedAt());
        $state->setContentChecksum($document->getContentHash());
        $state->setStorageSourceHash($document->getSourceHash());
        $state->setSourceGroupHash($document->getSourceGroupHash());
        $state->setLastError('');
        $state->setLanguage($metadata->getLanguage());
        $state->setLanguageId($metadata->getLanguageId());
        $state->setPageId($metadata->getPageId());
        $state->setPath($metadata->getPath());
        $state->setFilename($metadata->getFilename());

        $this->persistState($state);
    }


    /**
     * Marks a document as failed.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration Indexer configuration.
     * @param \Madj2k\AiCore\Indexing\DTO\IndexableDocument $document Document.
     * @param string $collection Collection name.
     * @param \Throwable $exception Exception.
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function markFailed(
        IndexingConfigurationInterface $configuration,
        IndexableDocument $document,
        string $collection,
        \Throwable $exception
    ): void {
        $state = $this->getOrCreateState($configuration, $document, $collection);
        $state->setStatus('failed');
        $state->setLastError($exception->getMessage());

        $this->persistState($state);
    }


    /**
     * Marks a document as removed.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration Indexer configuration.
     * @param \Madj2k\AiCore\Indexing\DTO\IndexableDocument $document Document.
     * @param string $collection Collection name.
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function markRemoved(IndexingConfigurationInterface $configuration, IndexableDocument $document, string $collection): void
    {
        $state = $this->getOrCreateState($configuration, $document, $collection);
        $state->setStatus('removed');
        $state->setLastIndexed(time());
        $state->setLastError('');

        $this->persistState($state);
    }


    /**
     * Returns all source hashes that may exist in vector storage for this source.
     * Also find all documents in a collection that belong to the same document-group
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration Indexer configuration.
     * @param \Madj2k\AiCore\Indexing\DTO\IndexableDocument $document Document.
     * @param string $collection Collection name.
     * @return array<int, string> Source hashes for deletion.
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function getStorageSourceHashesForDeletion(
        IndexingConfigurationInterface $configuration,
        IndexableDocument $document,
        string $collection
    ): array {

        $hashes = [];
        $state = $this->findState($configuration, $document, $collection);
        if ($state instanceof IndexerSource && $state->getStorageSourceHash() !== '') {
            $hashes[] = $state->getStorageSourceHash();
        }

        // find all documents in a collection that belong to the same document-group
        // this is relevant for files that consist of multiple pages!
        foreach ($this->IndexerSourceRepository->findByStorageCollectionAndGroupHash(
            $this->resolveVectorStoreConnectionUid($configuration), $collection, $document->getSourceGroupHash()
        ) as $groupState) {
            if ($groupState->getStorageSourceHash() !== '') {
                $hashes[] = $groupState->getStorageSourceHash();
            }
        }

        return array_values(array_unique(array_filter(array_map('trim', $hashes))));
    }



    /**
     * Removes persisted source states that are no longer members of a group.
     *
     * @param IndexerConfig $configuration Indexer configuration.
     * @param IndexableDocument $document Current group member.
     * @param string $collection Collection name.
     * @param array<int, string> $currentHashes Current group source hashes.
     * @return int Number of removed states.
     */
    public function removeStaleGroupMembers(
        IndexingConfigurationInterface $configuration,
        IndexableDocument $document,
        string $collection,
        array $currentHashes,
    ): int {

        // get all documents of a group in a collection
        $connectionUid = $this->resolveVectorStoreConnectionUid($configuration);
        $groupStates = $this->IndexerSourceRepository->findByStorageCollectionAndGroupHash(
            $connectionUid,
            $collection,
            $document->getSourceGroupHash()
        );

        // now check which documents are active and which are not
        // this is relevant when in a multiple page document pages have been deleted
        $staleHashes = [];
        foreach ($groupStates as $state) {
            if (!in_array($state->getSourceHash(), $currentHashes, true)) {
                $staleHashes[] = $state->getSourceHash();
            }
        }

        return $this->IndexerSourceRepository->deleteByStorageCollectionAndHashes(
            $connectionUid,
            $collection,
            $staleHashes,
        );
    }


    /**
     * Returns the vector store connection uid used as storage discriminator.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration Indexer configuration.
     * @return int Vector store connection uid.
     */
    public function resolveVectorStoreConnectionUid(IndexingConfigurationInterface $configuration): int
    {
        $vectorStoreConnection = $configuration->getVectorStoreConnection();
        if ($vectorStoreConnection === null) {
            return 0;
        }

        return (int)$vectorStoreConnection->getUid();
    }


    /**
     * Finds the source state for a document in one vector store collection.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration Indexer configuration.
     * @param \Madj2k\AiCore\Indexing\DTO\IndexableDocument $document Document.
     * @param string $collection Collection name.
     * @return \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerSource|null Source state.
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    private function findState(
        IndexingConfigurationInterface $configuration,
        IndexableDocument $document,
        string $collection
    ): ?IndexerSource {

        $vectorStoreConnectionUid = $this->resolveVectorStoreConnectionUid($configuration);
        $sourceHash = $document->getSourceHash();
        $state = $this->IndexerSourceRepository->findByStorageCollectionAndHash(
            $vectorStoreConnectionUid,
            $collection,
            $sourceHash
        );
        if ($state instanceof IndexerSource) {
            return $state;
        }

        $legacyState = $this->IndexerSourceRepository->findLegacyByCollectionAndHash($collection, $sourceHash);
        if (!$legacyState instanceof IndexerSource) {
            return null;
        }

        if ($vectorStoreConnectionUid > 0) {
            $legacyState->setVectorStoreConnection($vectorStoreConnectionUid);
            $this->persistState($legacyState);
        }

        return $legacyState;
    }


    /**
     * Returns an existing state or creates a new one.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration Indexer configuration.
     * @param \Madj2k\AiCore\Indexing\DTO\IndexableDocument $document Document.
     * @param string $collection Collection name.
     * @return \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerSource Source state.
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    private function getOrCreateState(
        IndexingConfigurationInterface $configuration,
        IndexableDocument $document,
        string $collection
    ): IndexerSource {
        if (!$configuration instanceof IndexerConfig) {
            throw new \InvalidArgumentException('TYPO3 source state requires an IndexerConfig.', 1791107002);
        }

        $hash = $document->getSourceHash();
        $state = $this->findState($configuration, $document, $collection);
        if ($state instanceof IndexerSource) {
            return $state;
        }

        $metadata = $document->getMetadata();
        $state = new IndexerSource();
        $state->setPid(0);
        $state->setSourceType($metadata->getSourceType());
        $state->setIndexerUid((int)$configuration->getUid());
        $state->setVectorStoreConnection($this->resolveVectorStoreConnectionUid($configuration));
        $state->setSourceId($metadata->getSourceIdentifier());
        $state->setSourceHash($hash);
        $state->setSourceGroupHash($document->getSourceGroupHash());
        $state->setCollection($collection);

        return $state;
    }


    /**
     * Persists a source state.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerSource $state Source state.
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    private function persistState(IndexerSource $state): void
    {
        if ($state->getUid() === null) {
            $this->IndexerSourceRepository->add($state);
        } else {
            $this->IndexerSourceRepository->update($state);
        }

        $this->persistenceManager->persistAll();
    }
}
