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
 * Class ConnectorConfig
 *
 * Domain object for the tx_aiassistant_indexer_connector record.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
class ConnectorConfig extends AbstractEntity
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
    protected string $baseUrl = '';


    /**
     * @var string
     */
    protected string $downloadBaseUrl = '';


    /**
     * @var string
     */
    protected string $downloadPath = '';


    /**
     * @var string
     */
    protected string $clientId = '';


    /**
     * @var string
     */
    protected string $clientSecret = '';


    /**
     * @var bool
     */
    protected bool $verifyTls = false;


    /**
     * @var int
     */
    protected int $lookbackDays = 0;


    /**
     * @var string
     */
    protected string $customFields = '';


    /**
     * @var string
     */
    protected string $indexedFields = '';


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
     * Returns base_url.
     *
     * @return string base_url.
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }


    /**
     * Sets base_url.
     *
     * @param string $baseUrl base_url.
     * @return void
     */
    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
    }


    /**
     * Returns download_base_url.
     *
     * @return string download_base_url.
     */
    public function getDownloadBaseUrl(): string
    {
        return $this->downloadBaseUrl;
    }


    /**
     * Sets download_base_url.
     *
     * @param string $downloadBaseUrl download_base_url.
     * @return void
     */
    public function setDownloadBaseUrl(string $downloadBaseUrl): void
    {
        $this->downloadBaseUrl = $downloadBaseUrl;
    }


    /**
     * Returns download_path.
     *
     * @return string download_path.
     */
    public function getDownloadPath(): string
    {
        return $this->downloadPath;
    }


    /**
     * Sets download_path.
     *
     * @param string $downloadPath download_path.
     * @return void
     */
    public function setDownloadPath(string $downloadPath): void
    {
        $this->downloadPath = $downloadPath;
    }


    /**
     * Returns client_id.
     *
     * @return string client_id.
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }


    /**
     * Sets client_id.
     *
     * @param string $clientId client_id.
     * @return void
     */
    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }


    /**
     * Returns client_secret.
     *
     * @return string client_secret.
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }


    /**
     * Sets client_secret.
     *
     * @param string $clientSecret client_secret.
     * @return void
     */
    public function setClientSecret(string $clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }


    /**
     * Returns verify_tls.
     *
     * @return bool verify_tls.
     */
    public function isVerifyTls(): bool
    {
        return $this->verifyTls;
    }


    /**
     * Sets verify_tls.
     *
     * @param bool $verifyTls verify_tls.
     * @return void
     */
    public function setVerifyTls(bool $verifyTls): void
    {
        $this->verifyTls = $verifyTls;
    }


    /**
     * Returns verify_tls as integer for TYPO3 database boundaries.
     *
     * @return int verify_tls.
     */
    public function getVerifyTls(): int
    {
        return $this->verifyTls ? 1 : 0;
    }


    /**
     * Returns lookback_days.
     *
     * @return int lookback_days.
     */
    public function getLookbackDays(): int
    {
        return $this->lookbackDays;
    }


    /**
     * Sets lookback_days.
     *
     * @param int $lookbackDays lookback_days.
     * @return void
     */
    public function setLookbackDays(int $lookbackDays): void
    {
        $this->lookbackDays = $lookbackDays;
    }


    /**
     * Returns custom_fields.
     *
     * @return string custom_fields.
     */
    public function getCustomFields(): string
    {
        return $this->customFields;
    }


    /**
     * Sets custom_fields.
     *
     * @param string $customFields custom_fields.
     * @return void
     */
    public function setCustomFields(string $customFields): void
    {
        $this->customFields = $customFields;
    }


    /**
     * Returns indexed_fields.
     *
     * @return string indexed_fields.
     */
    public function getIndexedFields(): string
    {
        return $this->indexedFields;
    }


    /**
     * Sets indexed_fields.
     *
     * @param string $indexedFields indexed_fields.
     * @return void
     */
    public function setIndexedFields(string $indexedFields): void
    {
        $this->indexedFields = $indexedFields;
    }

}
