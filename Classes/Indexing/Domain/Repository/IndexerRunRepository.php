<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Madj2k\AiAssistant\Indexing\Domain\Repository;

use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerRun;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class IndexerRunRepository
 *
 * Provides typed access to index run records.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @extends \TYPO3\CMS\Extbase\Persistence\Repository<\Madj2k\AiAssistant\Indexing\Domain\Model\IndexerRun>
 */
class IndexerRunRepository extends AbstractRepository
{
    /**
     * Starts a new index run.
     *
     * @param string $sourceType Source type.
     * @param bool $dryRun Dry-run flag.
     * @param int|null $indexerUid Indexer uid.
     * @return \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerRun Index run.
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function startRun(string $sourceType, bool $dryRun = false, ?int $indexerUid = null): IndexerRun
    {
        $run = new IndexerRun();
        $run->setPid(0);
        $run->setSourceType($sourceType);
        $run->setIndexerUid((int)($indexerUid ?? 0));
        $run->setIsDryRun($dryRun);
        $run->setStatus('running');
        $run->setStartedAt(time());

        $this->add($run);

        return $run;
    }


    /**
     * Finds the latest completed run for one source type and indexer.
     *
     * @param string $sourceType Source type.
     * @param int|null $indexerUid Indexer uid.
     * @return \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerRun|null Index run.
     */
    public function findLatestCompleted(string $sourceType, ?int $indexerUid = null): ?IndexerRun
    {
        $query = $this->createQuery();
        $constraints = [
            $query->equals('sourceType', trim($sourceType)),
            $query->equals('status', 'ok'),
        ];

        if (($indexerUid ?? 0) > 0) {
            $constraints[] = $query->equals('indexerUid', (int)$indexerUid);
        }

        $query->matching($query->logicalAnd(...$constraints));
        $query->setOrderings(['uid' => QueryInterface::ORDER_DESCENDING]);
        $query->setLimit(1);

        return $query->execute()->getFirst();
    }


    /**
     * Finds recent runs.
     *
     * @param int $limit Maximum result count.
     * @param int $offset Offset.
     * @param string|null $type Optional source type.
     * @return array<int, \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerRun> Index runs.
     */
    public function findRecent(int $limit = 10, int $offset = 0, ?string $type = null): array
    {
        $query = $this->createQuery();
        if ($type !== null && trim($type) !== '') {
            $query->matching($query->equals('sourceType', trim($type)));
        }

        $query->setOrderings(['uid' => QueryInterface::ORDER_DESCENDING]);
        $query->setOffset(max(0, $offset));
        $query->setLimit(max(1, $limit));

        return $query->execute()->toArray();
    }


    /**
     * Finds the latest run for one source type.
     *
     * @param string $sourceType Source type.
     * @param int|null $indexerUid Indexer uid.
     * @return \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerRun|null Index run.
     */
    public function findLastBySourceType(string $sourceType, ?int $indexerUid = null): ?IndexerRun
    {
        $query = $this->createQuery();
        $constraints = [
            $query->equals('sourceType', trim($sourceType)),
        ];

        if (($indexerUid ?? 0) > 0) {
            $constraints[] = $query->equals('indexerUid', (int)$indexerUid);
        }

        $query->matching($query->logicalAnd(...$constraints));
        $query->setOrderings(['uid' => QueryInterface::ORDER_DESCENDING]);
        $query->setLimit(1);

        return $query->execute()->getFirst();
    }


    /**
     * Counts runs older than the given cutoff.
     *
     * @param int $cutoff Cutoff timestamp.
     * @return int Number of runs.
     */
    public function countOlderThan(int $cutoff): int
    {
        return count($this->findOlderThan($cutoff));
    }


    /**
     * Deletes runs older than the given cutoff.
     *
     * @param int $cutoff Cutoff timestamp.
     * @return int Number of deleted runs.
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function deleteOlderThan(int $cutoff): int
    {
        $runs = $this->findOlderThan($cutoff);
        foreach ($runs as $run) {
            $this->remove($run);
        }

        return count($runs);
    }


    /**
     * Finds runs older than the given cutoff.
     *
     * @param int $cutoff Cutoff timestamp.
     * @return array<int, \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerRun> Runs.
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findOlderThan(int $cutoff): array
    {
        $query = $this->createQuery();
        $query->matching($query->logicalOr(
            $query->logicalAnd(
                $query->greaterThan('finishedAt', 0),
                $query->lessThan('finishedAt', $cutoff)
            ),
            $query->logicalAnd(
                $query->equals('finishedAt', 0),
                $query->lessThan('startedAt', $cutoff)
            )
        ));

        return $query->execute()->toArray();
    }

}
