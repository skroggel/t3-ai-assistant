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

namespace Madj2k\AiAssistant\Tests\Unit\Indexing\Command;

use Madj2k\AiAssistant\Indexing\Command\IndexingCommandRunner;
use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerRun;
use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerState;
use Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerRunRepository;
use Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerStateRepository;
use Madj2k\AiCore\Indexing\DTO\IndexingRequest;
use Madj2k\AiCore\Indexing\DTO\IndexingResult;
use Madj2k\AiCore\Indexing\Indexer\IndexerInterface;
use Madj2k\AiCore\Indexing\Registry\IndexerRegistry;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Locking\LockFactory;
use TYPO3\CMS\Core\Locking\LockingStrategyInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Class IndexingCommandRunnerTest
 *
 * Verifies that operational cursor state is persisted without producing empty run records.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class IndexingCommandRunnerTest extends TestCase
{
    /**
     * Verifies that a skipped batch advances its state without adding a history record.
     *
     * @return void
     */
    public function testNoOpBatchUpdatesStateWithoutPersistingRun(): void
    {
        $state = new IndexerState();
        $state->setCursor('10');

        $indexer = new class implements IndexerInterface {
            /**
             * @var string
             */
            public string $receivedCursor = '';


            /**
             * @inheritDoc
             */
            public function getIdentifier(): string
            {
                return 'test.indexer';
            }


            /**
             * @inheritDoc
             */
            public function getLabel(): string
            {
                return 'Test indexer';
            }


            /**
             * @inheritDoc
             */
            public function getSourceType(): string
            {
                return 'test';
            }


            /**
             * @inheritDoc
             */
            public function index(IndexingRequest $request): IndexingResult
            {
                $this->receivedCursor = $request->getCursor();

                $result = new IndexingResult();
                $result->increaseProcessed();
                $result->increaseSkipped();
                $result->setNextCursor('11');
                $result->setHasMore(true);

                return $result;
            }
        };

        $runRepository = $this->createMock(IndexerRunRepository::class);
        $runRepository->expects(self::never())->method('startRun');

        $stateRepository = $this->createMock(IndexerStateRepository::class);
        $stateRepository->expects(self::once())
            ->method('findOneByExecution')
            ->with('test.indexer', 'test', 42, 'default')
            ->willReturn($state);
        $stateRepository->expects(self::exactly(2))
            ->method('save')
            ->with($state);

        $persistenceManager = $this->createMock(PersistenceManager::class);
        $persistenceManager->expects(self::exactly(2))->method('persistAll');

        $lockFactory = $this->createLockFactoryMock();

        $subject = new IndexingCommandRunner(
            new IndexerRegistry([$indexer]),
            $runRepository,
            $stateRepository,
            $lockFactory,
            $persistenceManager
        );

        $request = new IndexingRequest();
        $request->setIndexerUid(42);

        $result = $subject->run('test.indexer', $request);

        self::assertSame('10', $indexer->receivedCursor);
        self::assertSame(1, $result->getSkipped());
        self::assertSame('11', $state->getCursor());
        self::assertSame('ok', $state->getStatus());
        self::assertGreaterThan(0, $state->getLastRunStartedAt());
        self::assertGreaterThanOrEqual($state->getLastRunStartedAt(), $state->getLastRunFinishedAt());
    }


    /**
     * Verifies that a batch with an indexed item is added to the run history.
     *
     * @return void
     */
    public function testChangedBatchPersistsCompletedRun(): void
    {
        $state = new IndexerState();
        $run = new IndexerRun();

        $indexer = new class implements IndexerInterface {
            /**
             * @inheritDoc
             */
            public function getIdentifier(): string
            {
                return 'test.indexer';
            }


            /**
             * @inheritDoc
             */
            public function getLabel(): string
            {
                return 'Test indexer';
            }


            /**
             * @inheritDoc
             */
            public function getSourceType(): string
            {
                return 'test';
            }


            /**
             * @inheritDoc
             */
            public function index(IndexingRequest $request): IndexingResult
            {
                $result = new IndexingResult();
                $result->increaseProcessed();
                $result->increaseIndexed();
                $result->increaseChunksTotal(2);

                return $result;
            }
        };

        $runRepository = $this->createMock(IndexerRunRepository::class);
        $runRepository->expects(self::once())
            ->method('startRun')
            ->with('test', false, 42)
            ->willReturn($run);
        $runRepository->expects(self::never())->method('update');

        $stateRepository = $this->createMock(IndexerStateRepository::class);
        $stateRepository->method('findOneByExecution')->willReturn($state);
        $stateRepository->expects(self::exactly(2))->method('save')->with($state);

        $persistenceManager = $this->createMock(PersistenceManager::class);
        $persistenceManager->expects(self::exactly(3))->method('persistAll');

        $lockFactory = $this->createLockFactoryMock();

        $subject = new IndexingCommandRunner(
            new IndexerRegistry([$indexer]),
            $runRepository,
            $stateRepository,
            $lockFactory,
            $persistenceManager
        );

        $request = new IndexingRequest();
        $request->setIndexerUid(42);

        $subject->run('test.indexer', $request);

        self::assertSame('ok', $run->getStatus());
        self::assertSame(1, $run->getItemsProcessed());
        self::assertSame(1, $run->getItemsIndexed());
        self::assertSame(2, $run->getChunksTotal());
        self::assertGreaterThan(0, $run->getFinishedAt());
    }


    /**
     * Creates a lock factory mock that verifies acquisition and release.
     *
     * @return \TYPO3\CMS\Core\Locking\LockFactory Lock factory mock.
     */
    private function createLockFactoryMock(): LockFactory
    {
        $locker = $this->createMock(LockingStrategyInterface::class);
        $locker->expects(self::once())->method('acquire')->willReturn(true);
        $locker->expects(self::once())->method('release')->willReturn(true);

        $lockFactory = $this->createMock(LockFactory::class);
        $lockFactory->expects(self::once())
            ->method('createLocker')
            ->with(self::callback(
                static fn (string $identifier): bool => str_starts_with($identifier, 'aiassistant-indexer-')
                    && strlen($identifier) === 84
            ))
            ->willReturn($locker);

        return $lockFactory;
    }
}
