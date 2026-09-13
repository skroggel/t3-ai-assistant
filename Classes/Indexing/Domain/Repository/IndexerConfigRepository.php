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

namespace Madj2k\AiAssistant\Indexing\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class IndexerConfigRepository
 *
 * Extbase repository for indexer configuration records.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 *
 * @extends \TYPO3\CMS\Extbase\Persistence\Repository<\Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig>
 */
class IndexerConfigRepository extends AbstractRepository
{
    /**
     * Returns all indexer configuration records.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<\Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig>
     */
    public function findAll(): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->setOrderings(['sorting' => QueryInterface::ORDER_ASCENDING, 'uid' => QueryInterface::ORDER_ASCENDING]);

        return $query->execute();
    }


    /**
     * Finds enabled indexers by source type.
     *
     * @param string $type Source type.
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<\Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig> Indexer configurations.
     */
    public function findByType(string $type): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching(
            $query->equals('type', trim($type))
        );
        $query->setOrderings(['sorting' => QueryInterface::ORDER_ASCENDING, 'uid' => QueryInterface::ORDER_ASCENDING]);

        return $query->execute();
    }


    /**
     * Finds enabled indexers by indexer identifier.
     *
     * @param string $identifier Indexer identifier.
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<\Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig> Indexer configurations.
     */
    public function findByIndexerIdentifier(string $identifier): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching(
            $query->equals('indexerIdentifier', trim($identifier))
        );
        $query->setOrderings(['sorting' => QueryInterface::ORDER_ASCENDING, 'uid' => QueryInterface::ORDER_ASCENDING]);

        return $query->execute();
    }
}
