<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension ai_assistant.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiAssistant\Connection\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class VectorStoreConnection
 *
 * Stores connection credentials and defaults for vector store connectors.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class VectorStoreConnection extends AbstractEntity
{
    /**
     * Title.
     *
     * @var string
     */
    protected string $title = '';


    /**
     * Connector identifier.
     *
     * @var string
     */
    protected string $connectorIdentifier = 'qdrant';


    /**
     * Endpoint URL.
     *
     * @var string
     */
    protected string $endpoint = '';


    /**
     * API key.
     *
     * @var string
     */
    protected string $apiKey = '';


    /**
     * Default collection.
     *
     * @var string
     */
    protected string $defaultCollection = '';


    /**
     * Vector size.
     *
     * @var int
     */
    protected int $vectorSize = 1536;


    /**
     * Distance metric.
     *
     * @var string
     */
    protected string $distance = 'Cosine';


    /**
     * Additional options as JSON.
     *
     * @var string
     */
    protected string $additionalOptions = '';


    /**
     * Returns title.
     *
     * @return string Title.
     */
    public function getTitle(): string
    {
        return $this->title;
    }


    /**
     * Sets title.
     *
     * @param string $title Title.
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = trim($title);
    }


    /**
     * Returns connector identifier.
     *
     * @return string Connector identifier.
     */
    public function getConnectorIdentifier(): string
    {
        return $this->connectorIdentifier;
    }


    /**
     * Sets connector identifier.
     *
     * @param string $connectorIdentifier Connector identifier.
     * @return void
     */
    public function setConnectorIdentifier(string $connectorIdentifier): void
    {
        $this->connectorIdentifier = trim($connectorIdentifier) !== ''
            ? trim($connectorIdentifier)
            : 'qdrant';
    }


    /**
     * Returns endpoint URL.
     *
     * @return string Endpoint URL.
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }


    /**
     * Sets endpoint URL.
     *
     * @param string $endpoint Endpoint URL.
     * @return void
     */
    public function setEndpoint(string $endpoint): void
    {
        $this->endpoint = rtrim(trim($endpoint), '/');
    }


    /**
     * Returns API key.
     *
     * @return string API key.
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }


    /**
     * Sets API key.
     *
     * @param string $apiKey API key.
     * @return void
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = trim($apiKey);
    }


    /**
     * Returns default collection.
     *
     * @return string Default collection.
     */
    public function getDefaultCollection(): string
    {
        return $this->defaultCollection;
    }


    /**
     * Sets default collection.
     *
     * @param string $defaultCollection Default collection.
     * @return void
     */
    public function setDefaultCollection(string $defaultCollection): void
    {
        $this->defaultCollection = trim($defaultCollection);
    }


    /**
     * Returns vector size.
     *
     * @return int Vector size.
     */
    public function getVectorSize(): int
    {
        return $this->vectorSize;
    }


    /**
     * Sets vector size.
     *
     * @param int $vectorSize Vector size.
     * @return void
     */
    public function setVectorSize(int $vectorSize): void
    {
        $this->vectorSize = $vectorSize > 0 ? $vectorSize : 1536;
    }


    /**
     * Returns distance metric.
     *
     * @return string Distance metric.
     */
    public function getDistance(): string
    {
        return $this->distance;
    }


    /**
     * Sets distance metric.
     *
     * @param string $distance Distance metric.
     * @return void
     */
    public function setDistance(string $distance): void
    {
        $this->distance = trim($distance) !== ''
            ? trim($distance)
            : 'Cosine';
    }


    /**
     * Returns additional options as JSON.
     *
     * @return string Additional options as JSON.
     */
    public function getAdditionalOptions(): string
    {
        return $this->additionalOptions;
    }


    /**
     * Sets additional options as JSON.
     *
     * @param string $additionalOptions Additional options as JSON.
     * @return void
     */
    public function setAdditionalOptions(string $additionalOptions): void
    {
        $this->additionalOptions = trim($additionalOptions);
    }


    /**
     * Returns additional options as array.
     *
     * @return array<string, mixed> Additional options.
     */
    public function getAdditionalOptionsArray(): array
    {
        /** @var mixed $decoded */
        $decoded = json_decode($this->additionalOptions, true);

        return is_array($decoded) ? $decoded : [];
    }
}
