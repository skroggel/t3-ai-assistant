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
 * Class IndexerRun
 *
 * Domain object for an index run protocol record.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
class IndexerRun extends AbstractEntity
{

    /**
     * @var string
     */
    protected string $sourceType = '';


    /**
     * @var int
     */
    protected int $indexerUid = 0;


    /**
     * @var string
     */
    protected string $status = '';


    /**
     * @var bool
     */
    protected bool $isDryRun = false;


    /**
     * @var int
     */
    protected int $startedAt = 0;


    /**
     * @var int
     */
    protected int $finishedAt = 0;


    /**
     * @var int
     */
    protected int $itemsProcessed = 0;


    /**
     * @var int
     */
    protected int $itemsIndexed = 0;


    /**
     * @var int
     */
    protected int $itemsSkipped = 0;


    /**
     * @var int
     */
    protected int $itemsFailed = 0;


    /**
     * @var int
     */
    protected int $itemsRemoved = 0;


    /**
     * @var int
     */
    protected int $chunksTotal = 0;


    /**
     * @var string
     */
    protected string $message = '';



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
     * Returns the indexer uid.
     *
     * @return int Indexer uid.
     */
    public function getIndexerUid(): int
    {
        return $this->indexerUid;
    }


    /**
     * Sets the indexer uid.
     *
     * @param int $indexerUid Indexer uid.
     * @return void
     */
    public function setIndexerUid(int $indexerUid): void
    {
        $this->indexerUid = max(0, $indexerUid);
    }


    /**
     * Returns the status.
     *
     * @return string Status.
     */
    public function getStatus(): string
    {
        return $this->status;
    }


    /**
     * Sets the status.
     *
     * @param string $status Status.
     * @return void
     */
    public function setStatus(string $status): void
    {
        $this->status = trim($status);
    }


    /**
     * Returns whether this was a dry run.
     *
     * @return bool Dry-run flag.
     */
    public function isDryRun(): bool
    {
        return $this->isDryRun;
    }


    /**
     * Sets whether this was a dry run.
     *
     * @param bool $isDryRun Dry-run flag.
     * @return void
     */
    public function setIsDryRun(bool $isDryRun): void
    {
        $this->isDryRun = $isDryRun;
    }


    /**
     * Returns the started timestamp.
     *
     * @return int Started timestamp.
     */
    public function getStartedAt(): int
    {
        return $this->startedAt;
    }


    /**
     * Sets the started timestamp.
     *
     * @param int $startedAt Started timestamp.
     * @return void
     */
    public function setStartedAt(int $startedAt): void
    {
        $this->startedAt = max(0, $startedAt);
    }


    /**
     * Returns the finished timestamp.
     *
     * @return int Finished timestamp.
     */
    public function getFinishedAt(): int
    {
        return $this->finishedAt;
    }


    /**
     * Sets the finished timestamp.
     *
     * @param int $finishedAt Finished timestamp.
     * @return void
     */
    public function setFinishedAt(int $finishedAt): void
    {
        $this->finishedAt = max(0, $finishedAt);
    }


    /**
     * Returns processed items.
     *
     * @return int Processed items.
     */
    public function getItemsProcessed(): int
    {
        return $this->itemsProcessed;
    }


    /**
     * Sets processed items.
     *
     * @param int $itemsProcessed Processed items.
     * @return void
     */
    public function setItemsProcessed(int $itemsProcessed): void
    {
        $this->itemsProcessed = max(0, $itemsProcessed);
    }


    /**
     * Returns indexed items.
     *
     * @return int Indexed items.
     */
    public function getItemsIndexed(): int
    {
        return $this->itemsIndexed;
    }


    /**
     * Sets indexed items.
     *
     * @param int $itemsIndexed Indexed items.
     * @return void
     */
    public function setItemsIndexed(int $itemsIndexed): void
    {
        $this->itemsIndexed = max(0, $itemsIndexed);
    }


    /**
     * Returns skipped items.
     *
     * @return int Skipped items.
     */
    public function getItemsSkipped(): int
    {
        return $this->itemsSkipped;
    }


    /**
     * Sets skipped items.
     *
     * @param int $itemsSkipped Skipped items.
     * @return void
     */
    public function setItemsSkipped(int $itemsSkipped): void
    {
        $this->itemsSkipped = max(0, $itemsSkipped);
    }


    /**
     * Returns failed items.
     *
     * @return int Failed items.
     */
    public function getItemsFailed(): int
    {
        return $this->itemsFailed;
    }


    /**
     * Sets failed items.
     *
     * @param int $itemsFailed Failed items.
     * @return void
     */
    public function setItemsFailed(int $itemsFailed): void
    {
        $this->itemsFailed = max(0, $itemsFailed);
    }


    /**
     * Returns removed items.
     *
     * @return int Removed items.
     */
    public function getItemsRemoved(): int
    {
        return $this->itemsRemoved;
    }


    /**
     * Sets removed items.
     *
     * @param int $itemsRemoved Removed items.
     * @return void
     */
    public function setItemsRemoved(int $itemsRemoved): void
    {
        $this->itemsRemoved = max(0, $itemsRemoved);
    }


    /**
     * Returns total chunks.
     *
     * @return int Total chunks.
     */
    public function getChunksTotal(): int
    {
        return $this->chunksTotal;
    }


    /**
     * Sets total chunks.
     *
     * @param int $chunksTotal Total chunks.
     * @return void
     */
    public function setChunksTotal(int $chunksTotal): void
    {
        $this->chunksTotal = max(0, $chunksTotal);
    }


    /**
     * Returns the message.
     *
     * @return string Message.
     */
    public function getMessage(): string
    {
        return $this->message;
    }


    /**
     * Sets the message.
     *
     * @param string $message Message.
     * @return void
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }


    /**
     * Returns the message decoded as array.
     *
     * @return array<string, mixed> Message data.
     */
    public function getMessageData(): array
    {
        $decoded = json_decode($this->message, true);

        return is_array($decoded) ? $decoded : [];
    }
}
