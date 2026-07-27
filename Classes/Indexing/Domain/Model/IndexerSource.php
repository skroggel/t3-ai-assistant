<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension ai_assistant.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiAssistant\Indexing\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class IndexerSource
 *
 * Domain object for the index source state of one source item.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IndexerSource extends AbstractEntity
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
    protected string $sourceId = '';


    /**
     * @var string
     */
    protected string $sourceHash = '';


    /**
     * @var int
     */
    protected int $language = 0;


    /**
     * @var string
     */
    protected string $collection = '';


    /**
     * @var int
     */
    protected int $vectorStoreConnection = 0;


    /**
     * @var int
     */
    protected int $pageId = 0;


    /**
     * @var string
     */
    protected string $path = '';


    /**
     * @var string
     */
    protected string $filename = '';


    /**
     * @var string
     */
    protected string $contentChecksum = '';


    /**
     * @var string
     */
    protected string $storageSourceHash = '';


    /**
     * @var int
     */
    protected int $fileMtime = 0;


    /**
     * @var int
     */
    protected int $fileCtime = 0;


    /**
     * @var int
     */
    protected int $lastIndexed = 0;


    /**
     * @var int
     */
    protected int $lastChanged = 0;


    /**
     * @var string
     */
    protected string $status = '';


    /**
     * @var int
     */
    protected int $lockedUntil = 0;


    /**
     * @var string
     */
    protected string $lockToken = '';


    /**
     * @var string
     */
    protected string $lastError = '';


    /**
     * @return string
     */
    public function getSourceType(): string
    {
        return $this->sourceType;
    }


    /**
     * @param string $sourceType
     * @return void
     */
    public function setSourceType(string $sourceType): void
    {
        $this->sourceType = trim($sourceType);
    }


    /**
     * @return int
     */
    public function getIndexerUid(): int
    {
        return $this->indexerUid;
    }


    /**
     * @param int $indexerUid
     * @return void
     */
    public function setIndexerUid(int $indexerUid): void
    {
        $this->indexerUid = max(0, $indexerUid);
    }


    /**
     * @return string
     */
    public function getSourceId(): string
    {
        return $this->sourceId;
    }


    public function setSourceId(string $sourceId): void
    {
        $this->sourceId = trim($sourceId);
    }


    public function getSourceHash(): string
    {
        return $this->sourceHash;
    }


    public function setSourceHash(string $sourceHash): void
    {
        $this->sourceHash = trim($sourceHash);
    }


    public function getLanguage(): int
    {
        return $this->language;
    }


    public function setLanguage(int $language): void
    {
        $this->language = $language;
    }


    public function getCollection(): string
    {
        return $this->collection;
    }


    public function setCollection(string $collection): void
    {
        $this->collection = trim($collection);
    }


    public function getVectorStoreConnection(): int
    {
        return $this->vectorStoreConnection;
    }


    public function setVectorStoreConnection(int $vectorStoreConnection): void
    {
        $this->vectorStoreConnection = max(0, $vectorStoreConnection);
    }


    public function getPageId(): int
    {
        return $this->pageId;
    }


    public function setPageId(int $pageId): void
    {
        $this->pageId = max(0, $pageId);
    }


    public function getPath(): string
    {
        return $this->path;
    }


    public function setPath(string $path): void
    {
        $this->path = trim($path);
    }


    public function getFilename(): string
    {
        return $this->filename;
    }


    public function setFilename(string $filename): void
    {
        $this->filename = trim($filename);
    }


    public function getContentChecksum(): string
    {
        return $this->contentChecksum;
    }


    public function setContentChecksum(string $contentChecksum): void
    {
        $this->contentChecksum = trim($contentChecksum);
    }


    public function getStorageSourceHash(): string
    {
        return $this->storageSourceHash;
    }


    public function setStorageSourceHash(string $storageSourceHash): void
    {
        $this->storageSourceHash = trim($storageSourceHash);
    }


    public function getFileMtime(): int
    {
        return $this->fileMtime;
    }


    public function setFileMtime(int $fileMtime): void
    {
        $this->fileMtime = max(0, $fileMtime);
    }


    public function getFileCtime(): int
    {
        return $this->fileCtime;
    }


    public function setFileCtime(int $fileCtime): void
    {
        $this->fileCtime = max(0, $fileCtime);
    }


    public function getLastIndexed(): int
    {
        return $this->lastIndexed;
    }


    public function setLastIndexed(int $lastIndexed): void
    {
        $this->lastIndexed = max(0, $lastIndexed);
    }


    public function getLastChanged(): int
    {
        return $this->lastChanged;
    }


    public function setLastChanged(int $lastChanged): void
    {
        $this->lastChanged = max(0, $lastChanged);
    }


    public function getStatus(): string
    {
        return $this->status;
    }


    public function setStatus(string $status): void
    {
        $this->status = trim($status);
    }


    public function getLockedUntil(): int
    {
        return $this->lockedUntil;
    }


    public function setLockedUntil(int $lockedUntil): void
    {
        $this->lockedUntil = max(0, $lockedUntil);
    }


    public function getLockToken(): string
    {
        return $this->lockToken;
    }


    public function setLockToken(string $lockToken): void
    {
        $this->lockToken = trim($lockToken);
    }


    public function getLastError(): string
    {
        return $this->lastError;
    }


    public function setLastError(string $lastError): void
    {
        $this->lastError = $lastError;
    }
}
