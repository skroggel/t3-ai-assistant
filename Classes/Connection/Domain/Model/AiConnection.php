<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension ai_assistant.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiAssistant\Connection\Domain\Model;

use Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class AiConnection
 *
 * Stores connection credentials and defaults for AI provider connectors.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AiConnection extends AbstractEntity implements AiConnectionConfigurationInterface
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
    protected string $connectorIdentifier = 'openai';


    /**
     * Base URL.
     *
     * @var string
     */
    protected string $baseUrl = '';


    /**
     * API key.
     *
     * @var string
     */
    protected string $apiKey = '';


    /**
     * Organization identifier.
     *
     * @var string
     */
    protected string $organization = '';


    /**
     * Project identifier.
     *
     * @var string
     */
    protected string $project = '';


    /**
     * Default chat model.
     *
     * @var string
     */
    protected string $defaultModel = '';


    /**
     * Default embedding model.
     *
     * @var string
     */
    protected string $embeddingModel = '';


    /**
     * Default chat temperature.
     *
     * @var float
     */
    protected float $defaultTemperature = 0.2;


    /**
     * Default embedding temperature.
     *
     * @var float
     */
    protected float $embeddingTemperature = 0.0;


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
            : 'openai';
    }


    /**
     * Returns base URL.
     *
     * @return string Base URL.
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }


    /**
     * Sets base URL.
     *
     * @param string $baseUrl Base URL.
     * @return void
     */
    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = rtrim(trim($baseUrl), '/');
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
     * Returns organization identifier.
     *
     * @return string Organization identifier.
     */
    public function getOrganization(): string
    {
        return $this->organization;
    }


    /**
     * Sets organization identifier.
     *
     * @param string $organization Organization identifier.
     * @return void
     */
    public function setOrganization(string $organization): void
    {
        $this->organization = trim($organization);
    }


    /**
     * Returns project identifier.
     *
     * @return string Project identifier.
     */
    public function getProject(): string
    {
        return $this->project;
    }


    /**
     * Sets project identifier.
     *
     * @param string $project Project identifier.
     * @return void
     */
    public function setProject(string $project): void
    {
        $this->project = trim($project);
    }


    /**
     * Returns default chat model.
     *
     * @return string Default chat model.
     */
    public function getDefaultModel(): string
    {
        return $this->defaultModel;
    }


    /**
     * Sets default chat model.
     *
     * @param string $defaultModel Default chat model.
     * @return void
     */
    public function setDefaultModel(string $defaultModel): void
    {
        $this->defaultModel = trim($defaultModel);
    }


    /**
     * Returns default embedding model.
     *
     * @return string Default embedding model.
     */
    public function getEmbeddingModel(): string
    {
        return $this->embeddingModel;
    }


    /**
     * Sets default embedding model.
     *
     * @param string $embeddingModel Default embedding model.
     * @return void
     */
    public function setEmbeddingModel(string $embeddingModel): void
    {
        $this->embeddingModel = trim($embeddingModel);
    }


    /**
     * Returns default chat temperature.
     *
     * @return float Default chat temperature.
     */
    public function getDefaultTemperature(): float
    {
        return $this->defaultTemperature;
    }


    /**
     * Sets default chat temperature.
     *
     * @param float $defaultTemperature Default chat temperature.
     * @return void
     */
    public function setDefaultTemperature(float $defaultTemperature): void
    {
        $this->defaultTemperature = $defaultTemperature;
    }


    /**
     * Returns default embedding temperature.
     *
     * @return float Default embedding temperature.
     */
    public function getEmbeddingTemperature(): float
    {
        return $this->embeddingTemperature;
    }


    /**
     * Sets default embedding temperature.
     *
     * @param float $embeddingTemperature Default embedding temperature.
     * @return void
     */
    public function setEmbeddingTemperature(float $embeddingTemperature): void
    {
        $this->embeddingTemperature = $embeddingTemperature;
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
