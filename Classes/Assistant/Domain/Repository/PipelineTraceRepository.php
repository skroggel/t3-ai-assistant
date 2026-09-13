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

namespace Madj2k\AiAssistant\Assistant\Domain\Repository;

use Madj2k\AiAssistant\Assistant\Domain\Model\PipelineTrace;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class PipelineTraceRepository
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
class PipelineTraceRepository extends Repository
{
    /**
     * Database table for pipeline traces.
     *
     * @var string
     */
    private const string TABLE_NAME = 'tx_aiassistant_pipeline_trace';

    /**
     * Disables the storage PID restriction because trace records are written as system records.
     *
     * @return void
     */
    public function initializeObject(): void
    {
        /** @var Typo3QuerySettings $querySettings */
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }


    /**
     * Adds a chat trace event.
     *
     * @param string $level Log level.
     * @param string $eventName Event name.
     * @param array<string,mixed> $payload Event payload.
     * @param string $route Route name.
     * @param string $chatIdentifier Conversation identifier.
     * @param string $queryText User query.
     * @return \Madj2k\AiAssistant\Assistant\Domain\Model\PipelineTrace Trace record.
     * @throws \JsonException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function addEvent(
        string $level,
        string $eventName,
        array $payload = [],
        string $route = '',
        string $chatIdentifier = '',
        string $queryText = ''
    ): PipelineTrace {
        /** @var PipelineTrace $trace */
        $trace = GeneralUtility::makeInstance(PipelineTrace::class);
        $trace->setPid(0);
        $trace->setLevel($level);
        $trace->setEventName($eventName);
        $trace->setRoute($route);
        $trace->setChatIdentifier($chatIdentifier);
        $trace->setQueryText($queryText);
        $trace->setPayload(json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        parent::add($trace);
        GeneralUtility::makeInstance(PersistenceManager::class)->persistAll();

        return $trace;
    }


    /**
     * Finds recent trace events.
     *
     * @param int $limit Maximum number of records.
     * @param string $chatIdentifier Optional conversation identifier filter.
     * @return array<int,\Madj2k\AiAssistant\Assistant\Domain\Model\PipelineTrace> Trace records.
     */
    public function findRecent(int $limit = 100, string $chatIdentifier = ''): array
    {
        $query = $this->createQuery();
        if ($chatIdentifier !== '') {
            $query->matching($query->equals('chatIdentifier', $chatIdentifier));
        }
        $query->setOrderings(['crdate' => QueryInterface::ORDER_DESCENDING, 'uid' => QueryInterface::ORDER_DESCENDING]);
        $query->setLimit(max(1, $limit));

        return iterator_to_array($query->execute());
    }


    /**
     * Finds recent trace rows directly from the database.
     *
     * @param int $limit Maximum number of records.
     * @param string $chatIdentifier Optional conversation identifier filter.
     * @return array<int,array<string,mixed>> Trace rows.
     */
    public function findRecentRows(int $limit = 100, string $chatIdentifier = ''): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE_NAME);

        $queryBuilder
            ->select('*')
            ->from(self::TABLE_NAME)
            ->orderBy('crdate', 'DESC')
            ->addOrderBy('uid', 'DESC')
            ->setMaxResults(max(1, $limit));

        if ($chatIdentifier !== '') {
            $queryBuilder->where(
                $queryBuilder->expr()->eq(
                    'chat_identifier',
                    $queryBuilder->createNamedParameter($chatIdentifier)
                )
            );
        }

        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }


    /**
     * Counts trace events.
     *
     * @param string $chatIdentifier Optional conversation identifier filter.
     * @return int Trace count.
     */
    public function countAll(string $chatIdentifier = ''): int
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE_NAME);

        $queryBuilder
            ->count('uid')
            ->from(self::TABLE_NAME);

        if ($chatIdentifier !== '') {
            $queryBuilder->where(
                $queryBuilder->expr()->eq(
                    'chat_identifier',
                    $queryBuilder->createNamedParameter($chatIdentifier)
                )
            );
        }

        return (int)$queryBuilder->executeQuery()->fetchOne();
    }


    /**
     * Finds recent conversation identifiers.
     *
     * @param int $limit Maximum number of scopes.
     * @return array<int,string> Conversation identifiers.
     */
    public function findRecentChatIdentifiers(int $limit = 50): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE_NAME);

        $rows = $queryBuilder
            ->select('chat_identifier')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->neq(
                    'chat_identifier',
                    $queryBuilder->createNamedParameter('')
                )
            )
            ->groupBy('chat_identifier')
           # ->orderBy('MAX(crdate)', 'DESC')
            ->setMaxResults(max(1, $limit))
            ->executeQuery()
            ->fetchAllAssociative();

        return array_values(array_filter(array_map(
            static fn (array $row): string => trim((string)($row['chat_identifier'] ?? '')),
            $rows
        )));
    }


    /**
     * Deletes all trace events.
     *
     * @return int Number of deleted records.
     */
    public function deleteAll(): int
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable(self::TABLE_NAME);

        return (int)$connection->executeStatement(
            'DELETE FROM ' . $connection->quoteIdentifier(self::TABLE_NAME)
        );
    }


    /**
     * Counts older trace records.
     *
     * @param int $cutoff Unix timestamp.
     * @return int Trace count.
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function countOlderThan(int $cutoff): int
    {
        $query = $this->createQuery();
        $query->matching($query->lessThan('crdate', $cutoff));

        return $query->execute()->count();
    }


    /**
     * Deletes older trace records.
     *
     * @param int $cutoff Unix timestamp.
     * @return int Deleted records.
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function deleteOlderThan(int $cutoff): int
    {
        $query = $this->createQuery();
        $query->matching($query->lessThan('crdate', $cutoff));

        $count = 0;
        foreach ($query->execute() as $trace) {
            if ($trace instanceof PipelineTrace) {
                $this->remove($trace);
                $count++;
            }
        }
        GeneralUtility::makeInstance(PersistenceManager::class)->persistAll();

        return $count;
    }


    /**
     * Counts events by level since a timestamp.
     *
     * @param string $level Log level.
     * @param int $cutoff Unix timestamp.
     * @return int Event count.
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function countByLevelSince(string $level, int $cutoff): int
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('level', $level),
                $query->greaterThanOrEqual('crdate', $cutoff)
            )
        );

        return $query->execute()->count();
    }
}
