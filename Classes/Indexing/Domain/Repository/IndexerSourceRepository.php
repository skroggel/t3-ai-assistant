<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension ai_assistant.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiAssistant\Indexing\Domain\Repository;

use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerSource;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class IndexerSourceRepository
 *
 * Provides typed access to index source state records.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @extends \TYPO3\CMS\Extbase\Persistence\Repository<\Madj2k\AiAssistant\Indexing\Domain\Model\IndexerSource>
 */
class IndexerSourceRepository extends AbstractRepository
{
    /**
     * Finds one source state by stable source hash.
     *
     * @param string $hash Stable source hash.
     * @return \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerSource|null Source state.
     */
    public function findByHash(string $hash): ?IndexerSource
    {
        if (trim($hash) === '') {
            return null;
        }

        $query = $this->createQuery();
        $query->matching($query->equals('sourceHash', trim($hash)));
        $query->setLimit(1);

        return $query->execute()->getFirst();
    }


    /**
     * Finds one source state by storage, collection and stable source hash.
     *
     * @param int $vectorStoreConnection Vector store connection uid.
     * @param string $collection Collection name.
     * @param string $hash Stable source hash.
     * @return \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerSource|null Source state.
     */
    public function findByStorageCollectionAndHash(int $vectorStoreConnection, string $collection, string $hash): ?IndexerSource
    {
        if ($vectorStoreConnection <= 0 || trim($collection) === '' || trim($hash) === '') {
            return null;
        }

        $query = $this->createQuery();
        $query->matching($query->logicalAnd(
            $query->equals('vectorStoreConnection', $vectorStoreConnection),
            $query->equals('collection', trim($collection)),
            $query->equals('sourceHash', trim($hash))
        ));
        $query->setLimit(1);

        return $query->execute()->getFirst();
    }


    /**
     * Finds one legacy source state without vector store discriminator.
     *
     * @param string $collection Collection name.
     * @param string $hash Stable source hash.
     * @return \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerSource|null Source state.
     */
    public function findLegacyByCollectionAndHash(string $collection, string $hash): ?IndexerSource
    {
        if (trim($collection) === '' || trim($hash) === '') {
            return null;
        }

        $query = $this->createQuery();
        $query->matching($query->logicalAnd(
            $query->equals('vectorStoreConnection', 0),
            $query->equals('collection', trim($collection)),
            $query->equals('sourceHash', trim($hash))
        ));
        $query->setLimit(1);

        return $query->execute()->getFirst();
    }


    /**
     * Finds source states by source type, vector store and collection.
     *
     * @param string $sourceType Source type.
     * @param int $vectorStoreConnection Vector store connection uid.
     * @param string $collection Collection name.
     * @return array<int, \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerSource> Source states.
     */
    public function findBySourceTypeStorageAndCollection(string $sourceType, int $vectorStoreConnection, string $collection): array
    {
        $query = $this->createQuery();
        $query->matching($query->logicalAnd(
            $query->equals('sourceType', trim($sourceType)),
            $query->equals('vectorStoreConnection', $vectorStoreConnection),
            $query->equals('collection', trim($collection))
        ));
        $query->setOrderings(['sourceId' => QueryInterface::ORDER_ASCENDING]);

        return $query->execute()->toArray();
    }


    /**
     * Finds source states after a source identifier within one vector store collection.
     *
     * @param string $sourceType Source type.
     * @param int $vectorStoreConnection Vector store connection uid.
     * @param string $collection Collection name.
     * @param string $sourceId Source id lower bound.
     * @param int $limit Maximum result count.
     * @return array<int, \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerSource> Source states.
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findByStorageCollectionAfterSourceId(
        string $sourceType,
        int $vectorStoreConnection,
        string $collection,
        string $sourceId,
        int $limit = 100
    ): array {
        $query = $this->createQuery();
        $query->matching($query->logicalAnd(
            $query->equals('sourceType', trim($sourceType)),
            $query->equals('vectorStoreConnection', $vectorStoreConnection),
            $query->equals('collection', trim($collection)),
            $query->greaterThan('sourceId', trim($sourceId))
        ));
        $query->setOrderings(['sourceId' => QueryInterface::ORDER_ASCENDING]);
        $query->setLimit(max(1, $limit));

        return $query->execute()->toArray();
    }


    /**
     * Saves one source state.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerSource $source Source state.
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function save(IndexerSource $source): void
    {
        if ($source->getUid() === null) {
            parent::add($source);
        } else {
            parent::update($source);
        }

        GeneralUtility::makeInstance(PersistenceManager::class)->persistAll();
    }
}
