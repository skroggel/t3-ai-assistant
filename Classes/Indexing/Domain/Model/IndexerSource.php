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

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class IndexerSource
 *
 * Domain object for the index source state of one source item.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
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
     * @var string
     */
    protected string $sourceGroupHash = '';


    /**
     * @var string
     */
    protected string $language = '';


    /**
     * @var int
     */
    protected int $languageId = -1;


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


    /**
     * @param string $sourceId
     * @return void
     */
    public function setSourceId(string $sourceId): void
    {
        $this->sourceId = trim($sourceId);
    }


    /**
     * @return string
     */
    public function getSourceHash(): string
    {
        return $this->sourceHash;
    }


    /**
     * @param string $sourceHash
     * @return void
     */
    public function setSourceHash(string $sourceHash): void
    {
        $this->sourceHash = trim($sourceHash);
    }


    /**
     * @return string
     */
    public function getSourceGroupHash(): string
    {
        return $this->sourceGroupHash;
    }


    /**
     * @param string $sourceGroupHash
     * @return void
     */
    public function setSourceGroupHash(string $sourceGroupHash): void
    {
        $this->sourceGroupHash = trim($sourceGroupHash);
    }


    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @param string $language
     * @return void
     */
    public function setLanguage(string $language): void
    {
        $this->language = trim($language);
    }


    /**
     * @return int
     */
    public function getLanguageId(): int
    {
        return $this->languageId;
    }


    /**
     * @param int $languageId
     * @return void
     */
    public function setLanguageId(int $languageId): void
    {
        $this->languageId = $languageId;
    }


    /**
     * @return string
     */
    public function getCollection(): string
    {
        return $this->collection;
    }


    /**
     * @param string $collection
     * @return void
     */
    public function setCollection(string $collection): void
    {
        $this->collection = trim($collection);
    }


    /**
     * @return int
     */
    public function getVectorStoreConnection(): int
    {
        return $this->vectorStoreConnection;
    }


    /**
     * @param int $vectorStoreConnection
     * @return void
     */
    public function setVectorStoreConnection(int $vectorStoreConnection): void
    {
        $this->vectorStoreConnection = max(0, $vectorStoreConnection);
    }


    /**
     * @return int
     */
    public function getPageId(): int
    {
        return $this->pageId;
    }


    /**
     * @param int $pageId
     * @return void
     */
    public function setPageId(int $pageId): void
    {
        $this->pageId = max(0, $pageId);
    }


    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }


    /**
     * @param string $path
     * @return void
     */
    public function setPath(string $path): void
    {
        $this->path = trim($path);
    }


    public function getFilename(): string
    {
        return $this->filename;
    }


    /**
     * @param string $filename
     * @return void
     */
    public function setFilename(string $filename): void
    {
        $this->filename = trim($filename);
    }


    /**
     * @return string
     */
    public function getContentChecksum(): string
    {
        return $this->contentChecksum;
    }


    /**
     * @param string $contentChecksum
     * @return void
     */
    public function setContentChecksum(string $contentChecksum): void
    {
        $this->contentChecksum = trim($contentChecksum);
    }


    /**
     * @return string
     */
    public function getStorageSourceHash(): string
    {
        return $this->storageSourceHash;
    }


    /**
     * @param string $storageSourceHash
     * @return void
     */
    public function setStorageSourceHash(string $storageSourceHash): void
    {
        $this->storageSourceHash = trim($storageSourceHash);
    }


    /**
     * @return int
     */
    public function getFileMtime(): int
    {
        return $this->fileMtime;
    }


    /**
     * @param int $fileMtime
     * @return void
     */
    public function setFileMtime(int $fileMtime): void
    {
        $this->fileMtime = max(0, $fileMtime);
    }


    /**
     * @return int
     */
    public function getFileCtime(): int
    {
        return $this->fileCtime;
    }


    /**
     * @param int $fileCtime
     * @return void
     */
    public function setFileCtime(int $fileCtime): void
    {
        $this->fileCtime = max(0, $fileCtime);
    }


    /**
     * @return int
     */
    public function getLastIndexed(): int
    {
        return $this->lastIndexed;
    }


    /**
     * @param int $lastIndexed
     * @return void
     */
    public function setLastIndexed(int $lastIndexed): void
    {
        $this->lastIndexed = max(0, $lastIndexed);
    }


    /**
     * @return int
     */
    public function getLastChanged(): int
    {
        return $this->lastChanged;
    }


    /**
     * @param int $lastChanged
     * @return void
     */
    public function setLastChanged(int $lastChanged): void
    {
        $this->lastChanged = max(0, $lastChanged);
    }


    /**
     * @return string
     */
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


    /**
     * @param int $lockedUntil
     * @return void
     */
    public function setLockedUntil(int $lockedUntil): void
    {
        $this->lockedUntil = max(0, $lockedUntil);
    }


    /**
     * @return string
     */
    public function getLockToken(): string
    {
        return $this->lockToken;
    }


    /**
     * @param string $lockToken
     * @return void
     */
    public function setLockToken(string $lockToken): void
    {
        $this->lockToken = trim($lockToken);
    }


    /**
     * @return string
     */
    public function getLastError(): string
    {
        return $this->lastError;
    }


    /**
     * @param string $lastError
     * @return void
     */
    public function setLastError(string $lastError): void
    {
        $this->lastError = $lastError;
    }
}
