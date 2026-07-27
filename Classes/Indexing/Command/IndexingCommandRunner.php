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

use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerRun;
use Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerRunRepository;
use Madj2k\AiAssistant\Indexing\DTO\IndexingRequest;
use Madj2k\AiAssistant\Indexing\DTO\IndexingResult;
use Madj2k\AiAssistant\Indexing\Registry\IndexerRegistry;
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
     * @param \Madj2k\AiAssistant\Indexing\Registry\IndexerRegistry $indexerRegistry Indexer registry.
     * @param \Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerRunRepository $indexerRunRepository Index run repository.
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager Persistence manager.
     */
    public function __construct(
        private IndexerRegistry $indexerRegistry,
        private indexerRunRepository $indexerRunRepository,
        private PersistenceManager $persistenceManager
    ) {
    }


    /**
     * Runs one indexer and persists run statistics.
     *
     * @param string $indexerIdentifier Indexer identifier.
     * @param \Madj2k\AiAssistant\Indexing\DTO\IndexingRequest $request Indexing request.
     * @return \Madj2k\AiAssistant\Indexing\DTO\IndexingResult Indexing result.
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

        if (!$request->isResetCursor() && $request->getCursor() === '') {
            $request->setCursor($this->resolveStoredCursor($request));
        }

        $run = $this->indexerRunRepository->startRun(
            $request->getSourceType(),
            $request->isDryRun(),
            $request->getIndexerUid()
        );
        $this->persistenceManager->persistAll();

        $result = new IndexingResult();

        try {
            $result = $indexer->index($request);
            $this->finishRun($run, 'ok', $result, $request);
        } catch (\Throwable $exception) {
            $result->increaseFailed();
            $result->addDetail('exception', $exception->getMessage());
            $this->finishRun($run, 'error', $result, $request);
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
     * Persists run completion.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerRun $run Run.
     * @param string $status Run status.
     * @param \Madj2k\AiAssistant\Indexing\DTO\IndexingResult $result Indexing result.
     * @param \Madj2k\AiAssistant\Indexing\DTO\IndexingRequest $request Indexing request.
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    private function finishRun(IndexerRun $run, string $status, IndexingResult $result, IndexingRequest $request): void
    {
        $nextCursor = $result->hasMore() ? $result->getNextCursor() : '';

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

        $this->indexerRunRepository->update($run);
        $this->persistenceManager->persistAll();
    }


    /**
     * Resolves the cursor from the latest completed run.
     *
     * @param \Madj2k\AiAssistant\Indexing\DTO\IndexingRequest $request Indexing request.
     * @return string Stored cursor.
     */
    private function resolveStoredCursor(IndexingRequest $request): string
    {
        $latestRun = $this->indexerRunRepository->findLatestCompleted(
            $request->getSourceType(),
            $request->getIndexerUid()
        );

        if (!$latestRun instanceof IndexerRun) {
            return '';
        }

        $messageData = $latestRun->getMessageData();

        return (string)($messageData['next_cursor'] ?? '');
    }
}
