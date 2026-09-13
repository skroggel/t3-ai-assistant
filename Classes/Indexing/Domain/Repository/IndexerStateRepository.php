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

use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerState;

/**
 * Class IndexerStateRepository
 *
 * Provides typed access to persistent indexer runtime states.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IndexerStateRepository extends AbstractRepository
{
    /**
     * Finds the runtime state for an indexer and execution scope.
     *
     * @param string $indexerIdentifier Indexer service identifier.
     * @param string $sourceType Source type.
     * @param int|null $indexerUid Indexer configuration uid.
     * @param string $scope Execution scope.
     * @return \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerState|null Runtime state.
     */
    public function findOneByExecution(
        string $indexerIdentifier,
        string $sourceType,
        ?int $indexerUid,
        string $scope
    ): ?IndexerState {
        $query = $this->createQuery();
        $query->matching($query->logicalAnd(
            $query->equals('indexerIdentifier', trim($indexerIdentifier)),
            $query->equals('sourceType', trim($sourceType)),
            $query->equals('indexerUid', (int)($indexerUid ?? 0)),
            $query->equals('scope', trim($scope) !== '' ? trim($scope) : 'default')
        ));
        $query->setLimit(1);

        $state = $query->execute()->getFirst();

        return $state instanceof IndexerState ? $state : null;
    }


    /**
     * Creates an unpersisted runtime state for an indexer and execution scope.
     *
     * @param string $indexerIdentifier Indexer service identifier.
     * @param string $sourceType Source type.
     * @param int|null $indexerUid Indexer configuration uid.
     * @param string $scope Execution scope.
     * @param string $cursor Initial cursor.
     * @return \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerState Runtime state.
     */
    public function createForExecution(
        string $indexerIdentifier,
        string $sourceType,
        ?int $indexerUid,
        string $scope,
        string $cursor = ''
    ): IndexerState {
        $state = new IndexerState();
        $state->setPid(0);
        $state->setIndexerIdentifier($indexerIdentifier);
        $state->setSourceType($sourceType);
        $state->setIndexerUid((int)($indexerUid ?? 0));
        $state->setScope($scope);
        $state->setCursor($cursor);

        return $state;
    }


    /**
     * Adds or updates a runtime state.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerState $state Runtime state.
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function save(IndexerState $state): void
    {
        if ($state->getUid() === null) {
            $this->add($state);
        } else {
            $this->update($state);
        }
    }
}
