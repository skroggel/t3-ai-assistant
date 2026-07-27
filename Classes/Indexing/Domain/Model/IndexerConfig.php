<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, version 2
 * of the License, or any later version.
 */

namespace Madj2k\AiAssistant\Indexing\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use Madj2k\AiAssistant\Connection\Domain\Model\AiConnection;
use Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection;

/**
 * Class IndexerConfig
 *
 * Domain object for the tx_aiassistant_indexer record.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IndexerConfig extends AbstractEntity
{

    /**
     * @var string
     */
    protected string $title = '';


    /**
     * @var string
     */
    protected string $type = '';


    /**
     * @var string
     */
    protected string $indexerIdentifier = '';


    /**
     * @var string
     */
    protected string $additionalMetadata = '';


    /**
     * Adapter identifier.
     *
     * @var string
     */
    protected string $adapterIdentifier = '';


    /**
     * @var \Madj2k\AiAssistant\Connection\Domain\Model\AiConnection|null
     */
    protected ?AiConnection $aiConnection = null;


    /**
     * @var \Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection|null
     */
    protected ?VectorStoreConnection $vectorStoreConnection = null;


    /**
     * collection.
     *
     * @var string
     */
    protected string $collection = '';


    /**
     * connector_uid.
     *
     * @var int
     */
    protected int $connectorUid = 0;

    /**
     * shopware_base_url.
     *
     * @var string
     */
    protected string $shopwareBaseUrl = '';


    /**
     * shopware_download_base_url.
     *
     * @var string
     */
    protected string $shopwareDownloadBaseUrl = '';


    /**
     * shopware_download_path.
     *
     * @var string
     */
    protected string $shopwareDownloadPath = '';


    /**
     * shopware_client_id.
     *
     * @var string
     */
    protected string $shopwareClientId = '';


    /**
     * shopware_api_key.
     *
     * @var string
     */
    protected string $shopwareApiKey = '';


    /**
     * shopware_verify_tls.
     *
     * @var bool
     */
    protected bool $shopwareVerifyTls = true;


    /**
     * shopware_lookback_days.
     *
     * @var int
     */
    protected int $shopwareLookbackDays = 1;


    /**
     * shopware_custom_fields.
     *
     * @var string
     */
    protected string $shopwareCustomFields = '';


    /**
     * shopware_indexed_fields.
     *
     * @var string
     */
    protected string $shopwareIndexedFields = '';


    /**
     * @var int
     */
    protected int $chunkSize = 0;


    /**
     * @var int
     */
    protected int $chunkOverlap = 0;


    /**
     * @var int
     */
    protected int $maxChunks = 0;


    /**
     * @var int
     */
    protected int $minChunkChars = 0;


    /**
     * @var string
     */
    protected string $importPath = '';


    /**
     * @var bool
     */
    protected bool $includeSubfolders = false;


    /**
     * @var string
     */
    protected string $rootPages = '';


    /**
     * @var string
     */
    protected string $pageFields = '';


    /**
     * @var string
     */
    protected string $contentTypes = '';


    /**
     * @var string
     */
    protected string $contentFields = '';


    /**
     * @var string
     */
    protected string $additionalContentFields = '';


    /**
     * Returns title.
     *
     * @return string title.
     */
    public function getTitle(): string
    {
        return $this->title;
    }


    /**
     * Sets title.
     *
     * @param string $title title.
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }


    /**
     * Returns type.
     *
     * @return string type.
     */
    public function getType(): string
    {
        return $this->type;
    }


    /**
     * Sets type.
     *
     * @param string $type type.
     * @return void
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }


    /**
     * Returns indexer_identifier.
     *
     * @return string indexer_identifier.
     */
    public function getIndexerIdentifier(): string
    {
        return $this->indexerIdentifier;
    }


    /**
     * Sets indexer_identifier.
     *
     * @param string $indexerIdentifier indexer_identifier.
     * @return void
     */
    public function setIndexerIdentifier(string $indexerIdentifier): void
    {
        $this->indexerIdentifier = $indexerIdentifier;
    }


    /**
     * Returns adapter identifier.
     *
     * @return string Adapter identifier.
     */
    public function getAdapterIdentifier(): string
    {
        return $this->adapterIdentifier;
    }


    /**
     * Sets adapter identifier.
     *
     * @param string $adapterIdentifier Adapter identifier.
     * @return void
     */
    public function setAdapterIdentifier(string $adapterIdentifier): void
    {
        $this->adapterIdentifier = trim($adapterIdentifier);
    }


    /**
     * Returns additional_metadata.
     *
     * @return string additional_metadata.
     */
    public function getAdditionalMetadata(): string
    {
        return $this->additionalMetadata;
    }


    /**
     * Sets additional_metadata.
     *
     * @param string $additionalMetadata additional_metadata.
     * @return void
     */
    public function setAdditionalMetadata(string $additionalMetadata): void
    {
        $this->additionalMetadata = $additionalMetadata;
    }


    /**
     * Returns additional_metadata decoded as array.
     *
     * @return array<string, mixed> additional_metadata.
     */
    public function getAdditionalMetadataArray(): array
    {
        $decoded = json_decode($this->additionalMetadata, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        return $this->parseAdditionalMetadataLines($this->additionalMetadata);
    }


    /**
     * Parses line or comma separated metadata configuration.
     *
     * Supports entries like "source=rag", "json_text_fields.content.text" and
     * "json_metadata_fields.id=external_id".
     *
     * @param string $value Raw additional metadata value.
     * @return array<string, mixed> Parsed metadata.
     */
    private function parseAdditionalMetadataLines(string $value): array
    {
        /** @var array<string, mixed> $metadata */
        $metadata = [];
        foreach (preg_split('/[\r\n,]+/u', $value) ?: [] as $entry) {
            $entry = trim($entry);
            if ($entry === '') {
                continue;
            }

            [$key, $entryValue] = array_pad(explode('=', $entry, 2), 2, true);
            $key = trim((string)$key);
            if ($key === '') {
                continue;
            }

            $metadata[$key] = is_string($entryValue) ? trim($entryValue) : $entryValue;
        }

        return $metadata;
    }


    /**
     * Returns the AI connection.
     *
     * @return \Madj2k\AiAssistant\Connection\Domain\Model\AiConnection|null AI connection.
     */
    public function getAiConnection(): ?AiConnection
    {
        return $this->aiConnection;
    }


    /**
     * Sets the AI connection.
     *
     * @param \Madj2k\AiAssistant\Connection\Domain\Model\AiConnection|null $aiConnection AI connection.
     * @return void
     */
    public function setAiConnection(?AiConnection $aiConnection): void
    {
        $this->aiConnection = $aiConnection;
    }


    /**
     * Returns the vector store connection.
     *
     * @return \Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection|null Vector store connection.
     */
    public function getVectorStoreConnection(): ?VectorStoreConnection
    {
        return $this->vectorStoreConnection;
    }


    /**
     * Sets the vector store connection.
     *
     * @param \Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection|null $vectorStoreConnection Vector store connection.
     * @return void
     */
    public function setVectorStoreConnection(?VectorStoreConnection $vectorStoreConnection): void
    {
        $this->vectorStoreConnection = $vectorStoreConnection;
    }


    /**
     * Returns collection.
     *
     * @return string collection.
     */
    public function getCollection(): string
    {
        return $this->collection;
    }


    /**
     * Sets collection.
     *
     * @param string $collection collection.
     * @return void
     */
    public function setCollection(string $collection): void
    {
        $this->collection = $collection;
    }


    /**
     * Returns connector_uid.
     *
     * @return int connector_uid.
     */
    public function getConnectorUid(): int
    {
        return $this->connectorUid;
    }


    /**
     * Sets connector_uid.
     *
     * @param int $connectorUid connector_uid.
     * @return void
     */
    public function setConnectorUid(int $connectorUid): void
    {
        $this->connectorUid = $connectorUid;
    }

    /**
     * Returns shopware_base_url.
     *
     * @return string shopware_base_url.
     */
    public function getShopwareBaseUrl(): string
    {
        return $this->shopwareBaseUrl;
    }


    /**
     * Sets shopware_base_url.
     *
     * @param string $shopwareBaseUrl shopware_base_url.
     * @return void
     */
    public function setShopwareBaseUrl(string $shopwareBaseUrl): void
    {
        $this->shopwareBaseUrl = trim($shopwareBaseUrl);
    }


    /**
     * Returns shopware_download_base_url.
     *
     * @return string shopware_download_base_url.
     */
    public function getShopwareDownloadBaseUrl(): string
    {
        return $this->shopwareDownloadBaseUrl;
    }


    /**
     * Sets shopware_download_base_url.
     *
     * @param string $shopwareDownloadBaseUrl shopware_download_base_url.
     * @return void
     */
    public function setShopwareDownloadBaseUrl(string $shopwareDownloadBaseUrl): void
    {
        $this->shopwareDownloadBaseUrl = trim($shopwareDownloadBaseUrl);
    }


    /**
     * Returns shopware_download_path.
     *
     * @return string shopware_download_path.
     */
    public function getShopwareDownloadPath(): string
    {
        return $this->shopwareDownloadPath;
    }


    /**
     * Sets shopware_download_path.
     *
     * @param string $shopwareDownloadPath shopware_download_path.
     * @return void
     */
    public function setShopwareDownloadPath(string $shopwareDownloadPath): void
    {
        $this->shopwareDownloadPath = trim($shopwareDownloadPath);
    }


    /**
     * Returns shopware_client_id.
     *
     * @return string shopware_client_id.
     */
    public function getShopwareClientId(): string
    {
        return $this->shopwareClientId;
    }


    /**
     * Sets shopware_client_id.
     *
     * @param string $shopwareClientId shopware_client_id.
     * @return void
     */
    public function setShopwareClientId(string $shopwareClientId): void
    {
        $this->shopwareClientId = trim($shopwareClientId);
    }


    /**
     * Returns shopware_api_key.
     *
     * @return string shopware_api_key.
     */
    public function getShopwareApiKey(): string
    {
        return $this->shopwareApiKey;
    }


    /**
     * Sets shopware_api_key.
     *
     * @param string $shopwareApiKey shopware_api_key.
     * @return void
     */
    public function setShopwareApiKey(string $shopwareApiKey): void
    {
        $this->shopwareApiKey = trim($shopwareApiKey);
    }


    /**
     * Returns shopware_verify_tls.
     *
     * @return bool shopware_verify_tls.
     */
    public function isShopwareVerifyTls(): bool
    {
        return $this->shopwareVerifyTls;
    }


    /**
     * Sets shopware_verify_tls.
     *
     * @param bool $shopwareVerifyTls shopware_verify_tls.
     * @return void
     */
    public function setShopwareVerifyTls(bool $shopwareVerifyTls): void
    {
        $this->shopwareVerifyTls = $shopwareVerifyTls;
    }


    /**
     * Returns shopware_verify_tls as integer for TYPO3 database boundaries.
     *
     * @return int shopware_verify_tls.
     */
    public function getShopwareVerifyTls(): int
    {
        return $this->shopwareVerifyTls ? 1 : 0;
    }


    /**
     * Returns shopware_lookback_days.
     *
     * @return int shopware_lookback_days.
     */
    public function getShopwareLookbackDays(): int
    {
        return $this->shopwareLookbackDays;
    }


    /**
     * Sets shopware_lookback_days.
     *
     * @param int $shopwareLookbackDays shopware_lookback_days.
     * @return void
     */
    public function setShopwareLookbackDays(int $shopwareLookbackDays): void
    {
        $this->shopwareLookbackDays = max(0, $shopwareLookbackDays);
    }


    /**
     * Returns shopware_custom_fields.
     *
     * @return string shopware_custom_fields.
     */
    public function getShopwareCustomFields(): string
    {
        return $this->shopwareCustomFields;
    }


    /**
     * Sets shopware_custom_fields.
     *
     * @param string $shopwareCustomFields shopware_custom_fields.
     * @return void
     */
    public function setShopwareCustomFields(string $shopwareCustomFields): void
    {
        $this->shopwareCustomFields = trim($shopwareCustomFields);
    }


    /**
     * Returns shopware_indexed_fields.
     *
     * @return string shopware_indexed_fields.
     */
    public function getShopwareIndexedFields(): string
    {
        return $this->shopwareIndexedFields;
    }


    /**
     * Sets shopware_indexed_fields.
     *
     * @param string $shopwareIndexedFields shopware_indexed_fields.
     * @return void
     */
    public function setShopwareIndexedFields(string $shopwareIndexedFields): void
    {
        $this->shopwareIndexedFields = trim($shopwareIndexedFields);
    }


    /**
     * Returns chunk_size.
     *
     * @return int chunk_size.
     */
    public function getChunkSize(): int
    {
        return $this->chunkSize;
    }


    /**
     * Sets chunk_size.
     *
     * @param int $chunkSize chunk_size.
     * @return void
     */
    public function setChunkSize(int $chunkSize): void
    {
        $this->chunkSize = $chunkSize;
    }


    /**
     * Returns chunk_overlap.
     *
     * @return int chunk_overlap.
     */
    public function getChunkOverlap(): int
    {
        return $this->chunkOverlap;
    }


    /**
     * Sets chunk_overlap.
     *
     * @param int $chunkOverlap chunk_overlap.
     * @return void
     */
    public function setChunkOverlap(int $chunkOverlap): void
    {
        $this->chunkOverlap = $chunkOverlap;
    }


    /**
     * Returns max_chunks.
     *
     * @return int max_chunks.
     */
    public function getMaxChunks(): int
    {
        return $this->maxChunks;
    }


    /**
     * Sets max_chunks.
     *
     * @param int $maxChunks max_chunks.
     * @return void
     */
    public function setMaxChunks(int $maxChunks): void
    {
        $this->maxChunks = $maxChunks;
    }


    /**
     * Returns min_chunk_chars.
     *
     * @return int min_chunk_chars.
     */
    public function getMinChunkChars(): int
    {
        return $this->minChunkChars;
    }


    /**
     * Sets min_chunk_chars.
     *
     * @param int $minChunkChars min_chunk_chars.
     * @return void
     */
    public function setMinChunkChars(int $minChunkChars): void
    {
        $this->minChunkChars = $minChunkChars;
    }


    /**
     * Returns import_path.
     *
     * @return string import_path.
     */
    public function getImportPath(): string
    {
        return $this->importPath;
    }


    /**
     * Sets import_path.
     *
     * @param string $importPath import_path.
     * @return void
     */
    public function setImportPath(string $importPath): void
    {
        $this->importPath = $importPath;
    }


    /**
     * Returns include_subfolders.
     *
     * @return bool include_subfolders.
     */
    public function isIncludeSubfolders(): bool
    {
        return $this->includeSubfolders;
    }


    /**
     * Sets include_subfolders.
     *
     * @param bool $includeSubfolders include_subfolders.
     * @return void
     */
    public function setIncludeSubfolders(bool $includeSubfolders): void
    {
        $this->includeSubfolders = $includeSubfolders;
    }


    /**
     * Returns include_subfolders as integer for TYPO3 database boundaries.
     *
     * @return int include_subfolders.
     */
    public function getIncludeSubfolders(): int
    {
        return $this->includeSubfolders ? 1 : 0;
    }


    /**
     * Returns root_pages.
     *
     * @return string root_pages.
     */
    public function getRootPages(): string
    {
        return $this->rootPages;
    }


    /**
     * Sets root_pages.
     *
     * @param string $rootPages root_pages.
     * @return void
     */
    public function setRootPages(string $rootPages): void
    {
        $this->rootPages = $rootPages;
    }


    /**
     * Returns page_fields.
     *
     * @return string page_fields.
     */
    public function getPageFields(): string
    {
        return $this->pageFields;
    }


    /**
     * Sets page_fields.
     *
     * @param string $pageFields page_fields.
     * @return void
     */
    public function setPageFields(string $pageFields): void
    {
        $this->pageFields = $pageFields;
    }


    /**
     * Returns content_types.
     *
     * @return string content_types.
     */
    public function getContentTypes(): string
    {
        return $this->contentTypes;
    }


    /**
     * Sets content_types.
     *
     * @param string $contentTypes content_types.
     * @return void
     */
    public function setContentTypes(string $contentTypes): void
    {
        $this->contentTypes = $contentTypes;
    }


    /**
     * Returns content_fields.
     *
     * @return string content_fields.
     */
    public function getContentFields(): string
    {
        return $this->contentFields;
    }


    /**
     * Sets content_fields.
     *
     * @param string $contentFields content_fields.
     * @return void
     */
    public function setContentFields(string $contentFields): void
    {
        $this->contentFields = $contentFields;
    }


    /**
     * Returns additional_content_fields.
     *
     * @return string additional_content_fields.
     */
    public function getAdditionalContentFields(): string
    {
        return $this->additionalContentFields;
    }


    /**
     * Sets additional_content_fields.
     *
     * @param string $additionalContentFields additional_content_fields.
     * @return void
     */
    public function setAdditionalContentFields(string $additionalContentFields): void
    {
        $this->additionalContentFields = trim($additionalContentFields);
    }

}
