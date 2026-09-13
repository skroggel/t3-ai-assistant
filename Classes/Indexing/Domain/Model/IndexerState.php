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

namespace Madj2k\AiAssistant\Indexing\Domain\Model;

/**
 * Class IndexerState
 *
 * Stores the mutable runtime state of one indexer independently from its run history.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
class IndexerState extends AbstractEntity
{
    /**
     * Indexer service identifier.
     *
     * @var string
     */
    protected string $indexerIdentifier = '';


    /**
     * Source type.
     *
     * @var string
     */
    protected string $sourceType = '';


    /**
     * Indexer configuration uid.
     *
     * @var int
     */
    protected int $indexerUid = 0;


    /**
     * State scope, for example the index or cleanup mode.
     *
     * @var string
     */
    protected string $scope = 'default';


    /**
     * Cursor for the next batch.
     *
     * @var string
     */
    protected string $cursorValue = '';


    /**
     * Current run status.
     *
     * @var string
     */
    protected string $status = '';


    /**
     * Timestamp at which the latest run started.
     *
     * @var int
     */
    protected int $lastRunStartedAt = 0;


    /**
     * Timestamp at which the latest run finished.
     *
     * @var int
     */
    protected int $lastRunFinishedAt = 0;


    /**
     * Error message of the latest failed run.
     *
     * @var string
     */
    protected string $lastError = '';


    /**
     * Returns the indexer service identifier.
     *
     * @return string Indexer service identifier.
     */
    public function getIndexerIdentifier(): string
    {
        return $this->indexerIdentifier;
    }


    /**
     * Sets the indexer service identifier.
     *
     * @param string $indexerIdentifier Indexer service identifier.
     * @return void
     */
    public function setIndexerIdentifier(string $indexerIdentifier): void
    {
        $this->indexerIdentifier = trim($indexerIdentifier);
    }


    /**
     * Returns the source type.
     *
     * @return string Source type.
     */
    public function getSourceType(): string
    {
        return $this->sourceType;
    }


    /**
     * Sets the source type.
     *
     * @param string $sourceType Source type.
     * @return void
     */
    public function setSourceType(string $sourceType): void
    {
        $this->sourceType = trim($sourceType);
    }


    /**
     * Returns the indexer configuration uid.
     *
     * @return int Indexer configuration uid.
     */
    public function getIndexerUid(): int
    {
        return $this->indexerUid;
    }


    /**
     * Sets the indexer configuration uid.
     *
     * @param int $indexerUid Indexer configuration uid.
     * @return void
     */
    public function setIndexerUid(int $indexerUid): void
    {
        $this->indexerUid = max(0, $indexerUid);
    }


    /**
     * Returns the state scope.
     *
     * @return string State scope.
     */
    public function getScope(): string
    {
        return $this->scope;
    }


    /**
     * Sets the state scope.
     *
     * @param string $scope State scope.
     * @return void
     */
    public function setScope(string $scope): void
    {
        $this->scope = trim($scope) !== '' ? trim($scope) : 'default';
    }


    /**
     * Returns the cursor for the next batch.
     *
     * @return string Cursor.
     */
    public function getCursor(): string
    {
        return $this->cursorValue;
    }


    /**
     * Sets the cursor for the next batch.
     *
     * @param string $cursor Cursor.
     * @return void
     */
    public function setCursor(string $cursor): void
    {
        $this->cursorValue = $cursor;
    }


    /**
     * Returns the current run status.
     *
     * @return string Run status.
     */
    public function getStatus(): string
    {
        return $this->status;
    }


    /**
     * Sets the current run status.
     *
     * @param string $status Run status.
     * @return void
     */
    public function setStatus(string $status): void
    {
        $this->status = trim($status);
    }


    /**
     * Returns the timestamp at which the latest run started.
     *
     * @return int Start timestamp.
     */
    public function getLastRunStartedAt(): int
    {
        return $this->lastRunStartedAt;
    }


    /**
     * Sets the timestamp at which the latest run started.
     *
     * @param int $lastRunStartedAt Start timestamp.
     * @return void
     */
    public function setLastRunStartedAt(int $lastRunStartedAt): void
    {
        $this->lastRunStartedAt = max(0, $lastRunStartedAt);
    }


    /**
     * Returns the timestamp at which the latest run finished.
     *
     * @return int Finish timestamp.
     */
    public function getLastRunFinishedAt(): int
    {
        return $this->lastRunFinishedAt;
    }


    /**
     * Sets the timestamp at which the latest run finished.
     *
     * @param int $lastRunFinishedAt Finish timestamp.
     * @return void
     */
    public function setLastRunFinishedAt(int $lastRunFinishedAt): void
    {
        $this->lastRunFinishedAt = max(0, $lastRunFinishedAt);
    }


    /**
     * Returns the error message of the latest failed run.
     *
     * @return string Error message.
     */
    public function getLastError(): string
    {
        return $this->lastError;
    }


    /**
     * Sets the error message of the latest failed run.
     *
     * @param string $lastError Error message.
     * @return void
     */
    public function setLastError(string $lastError): void
    {
        $this->lastError = $lastError;
    }
}
