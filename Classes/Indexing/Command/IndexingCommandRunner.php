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

namespace Madj2k\AiAssistant\Indexing\Command;

use Madj2k\AiCore\Indexing\DTO\IndexingRequest;
use Madj2k\AiCore\Indexing\DTO\IndexingResult;
use Madj2k\AiCore\Indexing\Registry\IndexerRegistry;
use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerRun;
use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerState;
use Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerRunRepository;
use Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerStateRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Class IndexingCommandRunner
 *
 * Bridges console commands and executable indexers.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final readonly class IndexingCommandRunner
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiCore\Indexing\Registry\IndexerRegistry $indexerRegistry Indexer registry.
     * @param \Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerRunRepository $indexerRunRepository Index run repository.
     * @param \Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerStateRepository $indexerStateRepository Indexer state repository.
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager Persistence manager.
     */
    public function __construct(
        private IndexerRegistry $indexerRegistry,
        private IndexerRunRepository $indexerRunRepository,
        private IndexerStateRepository $indexerStateRepository,
        private PersistenceManager $persistenceManager
    ) {
    }


    /**
     * Runs one indexer and persists run statistics.
     *
     * @param string $indexerIdentifier Indexer identifier.
     * @param \Madj2k\AiCore\Indexing\DTO\IndexingRequest $request Indexing request.
     * @return \Madj2k\AiCore\Indexing\DTO\IndexingResult Indexing result.
     * @throws \Throwable
     */
    public function run(string $indexerIdentifier, IndexingRequest $request): IndexingResult
    {
        $indexer = $this->indexerRegistry->get($indexerIdentifier);
        if ($request->getIndexerIdentifier() === '') {
            $request->setIndexerIdentifier($indexerIdentifier);
        }

        if ($request->getSourceType() === '') {
            $request->setSourceType($indexer->getSourceType());
        }

        if ($request->getLimit() === null) {
            $request->setLimit(100);
        }

        $state = $this->resolveState($request);
        if (!$request->isResetCursor() && $request->getCursor() === '') {
            $request->setCursor($state->getCursor());
        }

        $startedAt = time();
        if (!$request->isDryRun()) {
            $this->startState($state, $startedAt);
        }

        $result = new IndexingResult();

        try {
            $result = $indexer->index($request);
            $status = $result->getFailed() > 0 ? 'error' : 'ok';
            if (!$request->isDryRun()) {
                $this->finishState($state, $status, $result, '');
            }
            if ($this->shouldPersistRun($result, $request)) {
                $this->persistRun($startedAt, $status, $result, $request);
            }
        } catch (\Throwable $exception) {
            $result->increaseFailed();
            $result->addDetail('exception', $exception->getMessage());
            if (!$request->isDryRun()) {
                $this->finishState($state, 'error', $result, $exception->getMessage());
            }
            $this->persistRun($startedAt, 'error', $result, $request);
            throw $exception;
        }

        return $result;
    }


    /**
     * Returns registered indexer identifiers.
     *
     * @return array<int, string> Identifiers.
     */
    public function getRegisteredIndexerIdentifiers(): array
    {
        return $this->indexerRegistry->getIdentifiers();
    }


    /**
     * Persists a completed run protocol record.
     *
     * @param int $startedAt Run start timestamp.
     * @param string $status Run status.
     * @param \Madj2k\AiCore\Indexing\DTO\IndexingResult $result Indexing result.
     * @param \Madj2k\AiCore\Indexing\DTO\IndexingRequest $request Indexing request.
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    private function persistRun(int $startedAt, string $status, IndexingResult $result, IndexingRequest $request): void
    {
        $nextCursor = $result->hasMore() ? $result->getNextCursor() : '';

        $run = $this->indexerRunRepository->startRun(
            $request->getSourceType(),
            $request->isDryRun(),
            $request->getIndexerUid()
        );

        $run->setStartedAt($startedAt);
        $run->setStatus($status);
        $run->setFinishedAt(time());
        $run->setItemsProcessed($result->getProcessed());
        $run->setItemsIndexed($result->getIndexed());
        $run->setItemsSkipped($result->getSkipped());
        $run->setItemsFailed($result->getFailed());
        $run->setItemsRemoved($result->getRemoved());
        $run->setChunksTotal($result->getChunksTotal());
        $run->setMessage((string)json_encode([
            'indexer_identifier' => $request->getIndexerIdentifier(),
            'source_type' => $request->getSourceType(),
            'indexer_uid' => $request->getIndexerUid(),
            'limit' => $request->getLimit(),
            'cursor' => $request->getCursor(),
            'next_cursor' => $nextCursor,
            'has_more' => $result->hasMore(),
            'only_changed' => $request->isOnlyChanged(),
            'details' => $result->getDetails(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $this->persistenceManager->persistAll();
    }


    /**
     * Resolves or creates the runtime state for the request.
     *
     * @param \Madj2k\AiCore\Indexing\DTO\IndexingRequest $request Indexing request.
     * @return \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerState Runtime state.
     */
    private function resolveState(IndexingRequest $request): IndexerState
    {
        $scope = $this->resolveStateScope($request);
        $state = $this->indexerStateRepository->findOneByExecution(
            $request->getIndexerIdentifier(),
            $request->getSourceType(),
            $request->getIndexerUid(),
            $scope
        );
        if ($state instanceof IndexerState) {
            return $state;
        }

        $cursor = '';
        if ($scope === 'default') {
            $latestRun = $this->indexerRunRepository->findLatestCompleted(
                $request->getSourceType(),
                $request->getIndexerUid()
            );
            if ($latestRun instanceof IndexerRun) {
                $messageData = $latestRun->getMessageData();
                $cursor = (string)($messageData['next_cursor'] ?? '');
            }
        }

        return $this->indexerStateRepository->createForExecution(
            $request->getIndexerIdentifier(),
            $request->getSourceType(),
            $request->getIndexerUid(),
            $scope,
            $cursor
        );
    }


    /**
     * Marks a runtime state as running.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerState $state Runtime state.
     * @param int $startedAt Run start timestamp.
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    private function startState(IndexerState $state, int $startedAt): void
    {
        $state->setStatus('running');
        $state->setLastRunStartedAt($startedAt);
        $state->setLastError('');
        $this->indexerStateRepository->save($state);
        $this->persistenceManager->persistAll();
    }


    /**
     * Persists the result and cursor of a completed execution in its runtime state.
     *
     * The previous cursor is retained after an error so the failed batch can be retried.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerState $state Runtime state.
     * @param string $status Run status.
     * @param \Madj2k\AiCore\Indexing\DTO\IndexingResult $result Indexing result.
     * @param string $errorMessage Error message.
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    private function finishState(
        IndexerState $state,
        string $status,
        IndexingResult $result,
        string $errorMessage
    ): void {
        $state->setStatus($status);
        $state->setLastRunFinishedAt(time());
        $state->setLastError($errorMessage);
        if ($status === 'ok') {
            $state->setCursor($result->hasMore() ? $result->getNextCursor() : '');
        }

        $this->indexerStateRepository->save($state);
        $this->persistenceManager->persistAll();
    }


    /**
     * Returns whether a completed execution is relevant for the run history.
     *
     * @param \Madj2k\AiCore\Indexing\DTO\IndexingResult $result Indexing result.
     * @param \Madj2k\AiCore\Indexing\DTO\IndexingRequest $request Indexing request.
     * @return bool Whether a run protocol record should be persisted.
     */
    private function shouldPersistRun(IndexingResult $result, IndexingRequest $request): bool
    {
        return $request->isDryRun()
            || $result->getIndexed() > 0
            || $result->getRemoved() > 0
            || $result->getFailed() > 0;
    }


    /**
     * Resolves the state scope from the execution mode.
     *
     * @param \Madj2k\AiCore\Indexing\DTO\IndexingRequest $request Indexing request.
     * @return string State scope.
     */
    private function resolveStateScope(IndexingRequest $request): string
    {
        $mode = $request->getOption('mode', 'default');

        return is_scalar($mode) && trim((string)$mode) !== '' ? trim((string)$mode) : 'default';
    }
}
