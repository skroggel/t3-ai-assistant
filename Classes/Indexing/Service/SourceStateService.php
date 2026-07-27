<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension ai_assistant.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiAssistant\Indexing\Service;

use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig;
use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerSource;
use Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerSourceRepository;
use Madj2k\AiAssistant\Indexing\DTO\IndexableDocument;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Class SourceStateService
 *
 * Maintains source state records for incremental and batched indexing.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final readonly class SourceStateService
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerSourceRepository $IndexerSourceRepository Index source repository.
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager Persistence manager.
     */
    public function __construct(
        private IndexerSourceRepository $IndexerSourceRepository,
        private PersistenceManager $persistenceManager
    ) {
    }


    /**
     * Returns whether a document can be skipped because the source is unchanged.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration Indexer configuration.
     * @param \Madj2k\AiAssistant\Indexing\DTO\IndexableDocument $document Document.
     * @param string $collection Collection name.
     * @param bool $onlyChanged Whether unchanged sources should be skipped.
     * @return bool Skip flag.
     */
    public function shouldSkip(IndexerConfig $configuration, IndexableDocument $document, string $collection, bool $onlyChanged): bool
    {
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
     * @param \Madj2k\AiAssistant\Indexing\DTO\IndexableDocument $document Document.
     * @param string $collection Collection name.
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function markIndexed(IndexerConfig $configuration, IndexableDocument $document, string $collection): void
    {
        $state = $this->getOrCreateState($configuration, $document, $collection);
        $metadata = $document->getMetadata();

        $state->setStatus('indexed');
        $state->setLastIndexed(time());
        $state->setLastChanged($metadata->getChangedAt());
        $state->setContentChecksum($document->getContentHash());
        $state->setStorageSourceHash($this->createSourceHash($document));
        $state->setLastError('');
        $state->setLanguage($metadata->getLanguage());
        $state->setPageId($metadata->getPageId());
        $state->setPath($metadata->getPath());
        $state->setFilename($metadata->getFilename());

        $this->persistState($state);
    }


    /**
     * Marks a document as failed.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration Indexer configuration.
     * @param \Madj2k\AiAssistant\Indexing\DTO\IndexableDocument $document Document.
     * @param string $collection Collection name.
     * @param \Throwable $exception Exception.
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function markFailed(IndexerConfig $configuration, IndexableDocument $document, string $collection, \Throwable $exception): void
    {
        $state = $this->getOrCreateState($configuration, $document, $collection);
        $state->setStatus('failed');
        $state->setLastError($exception->getMessage());

        $this->persistState($state);
    }


    /**
     * Marks a document as removed.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration Indexer configuration.
     * @param \Madj2k\AiAssistant\Indexing\DTO\IndexableDocument $document Document.
     * @param string $collection Collection name.
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function markRemoved(IndexerConfig $configuration, IndexableDocument $document, string $collection): void
    {
        $state = $this->getOrCreateState($configuration, $document, $collection);
        $state->setStatus('removed');
        $state->setLastIndexed(time());
        $state->setLastError('');

        $this->persistState($state);
    }


    /**
     * Returns all source hashes that may exist in vector storage for this source.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration Indexer configuration.
     * @param \Madj2k\AiAssistant\Indexing\DTO\IndexableDocument $document Document.
     * @param string $collection Collection name.
     * @return array<int, string> Source hashes for deletion.
     */
    public function getStorageSourceHashesForDeletion(IndexerConfig $configuration, IndexableDocument $document, string $collection): array
    {
        $hashes = [$this->createSourceHash($document)];
        $state = $this->findState($configuration, $document, $collection);
        if ($state instanceof IndexerSource && $state->getStorageSourceHash() !== '') {
            $hashes[] = $state->getStorageSourceHash();
        }

        return array_values(array_unique(array_filter(array_map('trim', $hashes))));
    }


    /**
     * Returns the stable source hash for a document.
     *
     * This hash identifies the logical source only. It intentionally does not include
     * vector storage, collection, indexer configuration or the content checksum.
     *
     * @param \Madj2k\AiAssistant\Indexing\DTO\IndexableDocument $document Document.
     * @return string Stable source hash.
     */
    public function createSourceHash(IndexableDocument $document): string
    {
        $metadata = $document->getMetadata();

        return sha1(implode('|', [
            $metadata->getSourceType(),
            $metadata->getSourceIdentifier(),
            (string)$metadata->getLanguage(),
        ]));
    }


    /**
     * Returns the vector store connection uid used as storage discriminator.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration Indexer configuration.
     * @return int Vector store connection uid.
     */
    public function resolveVectorStoreConnectionUid(IndexerConfig $configuration): int
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
     * @param \Madj2k\AiAssistant\Indexing\DTO\IndexableDocument $document Document.
     * @param string $collection Collection name.
     * @return \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerSource|null Source state.
     */
    private function findState(IndexerConfig $configuration, IndexableDocument $document, string $collection): ?IndexerSource
    {
        $vectorStoreConnectionUid = $this->resolveVectorStoreConnectionUid($configuration);
        $sourceHash = $this->createSourceHash($document);
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
     * @param \Madj2k\AiAssistant\Indexing\DTO\IndexableDocument $document Document.
     * @param string $collection Collection name.
     * @return \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerSource Source state.
     */
    private function getOrCreateState(IndexerConfig $configuration, IndexableDocument $document, string $collection): IndexerSource
    {
        $hash = $this->createSourceHash($document);
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
