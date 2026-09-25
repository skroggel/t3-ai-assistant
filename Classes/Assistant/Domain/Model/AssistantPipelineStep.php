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

namespace Madj2k\AiAssistant\Assistant\Domain\Model;

use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Enum\HistoryMode;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineFailureStrategy;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineStage;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection;
use Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class AssistantPipelineStep
 *
 * Domain model for a configurable assistant pipeline step.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
class AssistantPipelineStep extends AbstractEntity implements PipelineStepConfigurationInterface
{
    /**
     * Assistant profile uid.
     *
     * @var int
     */
    protected int $assistantProfile = 0;


    /**
     * Title.
     *
     * @var string
     */
    protected string $title = '';


    /**
     * Processor type.
     *
     * @var string
     */
    protected string $type = 'answer_generator';


    /**
     * Processor identifier.
     *
     * @var string
     */
    protected string $processorIdentifier = '';


    /**
     * Semantic pipeline stage.
     *
     * @var string
     */
    protected string $stage = 'pre_answer';


    /**
     * Include assistant identity prompt.
     *
     * @var bool
     */
    protected bool $includeIdentityPrompt = true;


    /**
     * Include assistant behavior rules.
     *
     * @var bool
     */
    protected bool $includeBehaviorRules = true;


    /**
     * Include assistant retrieval rules.
     *
     * @var bool
     */
    protected bool $includeRetrievalRules = true;


    /**
     * Include assistant output rules.
     *
     * @var bool
     */
    protected bool $includeOutputRules = true;


    /**
     * Step identity.
     *
     * @var string
     */
    protected string $stepIdentity = '';


    /**
     * Step behavior rules.
     *
     * @var string
     */
    protected string $stepBehaviorRules = '';


    /**
     * Step retrieval rules.
     *
     * @var string
     */
    protected string $stepRetrievalRules = '';


    /**
     * Step output rules.
     *
     * @var string
     */
    protected string $stepOutputRules = '';


    /**
     * History mode.
     *
     * @var string
     */
    protected string $historyMode = 'last_n';


    /**
     * History limit.
     *
     * @var int
     */
    protected int $historyLimit = 5;


    /**
     * LLM model override.
     *
     * @var string
     */
    protected string $model = '';


    /**
     * LLM temperature.
     *
     * @var float
     */
    protected float $temperature = 0.2;


    /**
     * Maximum completion tokens.
     *
     * @var int
     */
    protected int $maxTokens = 800;


    /**
     * Maximum retrieval results.
     *
     * @var int
     */
    protected int $maxRetrievalResults = 8;


    /**
     * Optional vector store connection override.
     *
     * @var \Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection|null
     */
    protected ?VectorStoreConnection $retrievalVectorStoreConnection = null;

    /**
     * Optional MCP connections overriding the assistant profile default.
     *
     * @var ObjectStorage<object>
     */
    protected ObjectStorage $mcpConnections;

    /**
     * Initializes object storage properties.
     *
     * @return void
     */
    public function __construct()
    {
        $this->mcpConnections = new ObjectStorage();
    }

    /**
     * Returns MCP connections configured for this step.
     *
     * @return ObjectStorage<object> MCP connections.
     */
    public function getMcpConnections(): ObjectStorage
    {
        return $this->mcpConnections;
    }

    /**
     * Sets MCP connections configured for this step.
     *
     * @param ObjectStorage<object> $mcpConnections MCP connections.
     * @return void
     */
    public function setMcpConnections(ObjectStorage $mcpConnections): void
    {
        $this->mcpConnections = $mcpConnections;
    }

    /**
     * Adds an MCP connection override.
     *
     * @param object $mcpConnection MCP connection.
     * @return void
     */
    public function addMcpConnection(object $mcpConnection): void
    {
        $this->mcpConnections->attach($mcpConnection);
    }

    /**
     * Removes an MCP connection override.
     *
     * @param object $mcpConnection MCP connection.
     * @return void
     */
    public function removeMcpConnection(object $mcpConnection): void
    {
        $this->mcpConnections->detach($mcpConnection);
    }


    /**
     * Optional vector collection override.
     *
     * @var string
     */
    protected string $retrievalCollection = '';


    /**
     * Retrieval score threshold.
     *
     * @var float
     */
    protected float $scoreThreshold = 0.0;


    /**
     * Maximum context chunks.
     *
     * @var int
     */
    protected int $maxContextChunks = 6;


    /**
     * Maximum context characters.
     *
     * @var int
     */
    protected int $maxContextCharacters = 8000;


    /**
     * Prompt metadata fields.
     *
     * @var string
     */
    protected string $promptMetadataFields = 'title,url';


    /**
     * Failure strategy.
     *
     * @var string
     */
    protected string $failureStrategy = 'continue';


    /**
     * Returns the assistant profile uid.
     *
     * @return int
     */
    public function getAssistantProfile(): int
    {
        return $this->assistantProfile;
    }


    /**
     * Sets the assistant profile uid.
     *
     * @param int $assistantProfile Assistant profile uid.
     * @return void
     */
    public function setAssistantProfile(int $assistantProfile): void
    {
        $this->assistantProfile = $assistantProfile;
    }


    /**
     * Returns the title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }


    /**
     * Sets the title.
     *
     * @param string $title Title.
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }


    /**
     * Returns the typed step type.
     *
     * @return \Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType
     */
    public function getType(): AssistantPipelineProcessorType
    {
        return AssistantPipelineProcessorType::tryFrom($this->type) ?? AssistantPipelineProcessorType::AnswerGenerator;
    }


    /**
     * Sets the step type.
     *
     * @param \Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType|string $type Step type.
     * @return void
     */
    public function setType(AssistantPipelineProcessorType|string $type): void
    {
        $this->type = $type instanceof AssistantPipelineProcessorType ? $type->value : $type;
    }


    /**
     * Returns the raw step type value.
     *
     * @return string
     */
    public function getTypeValue(): string
    {
        return $this->type;
    }

    /**
     * Returns the processor identifier
     *
     * @return string
     */
    public function getProcessorIdentifier(): string
    {
        return $this->processorIdentifier;
    }


    /**
     * Sets the processor identifier
     *
     * @param string $processorIdentifier
     * @return void
     */
    public function setProcessorIdentifier(string $processorIdentifier): void
    {
        $this->processorIdentifier = $processorIdentifier;
    }

    /**
     * Returns the typed stage.
     *
     * @return \Madj2k\AiCore\Assistant\Enum\AssistantPipelineStage
     */
    public function getStage(): AssistantPipelineStage
    {
        return AssistantPipelineStage::tryFrom($this->stage) ?? AssistantPipelineStage::PreAnswer;
    }


    /**
     * Sets the stage.
     *
     * @param \Madj2k\AiCore\Assistant\Enum\AssistantPipelineStage|string $stage Stage.
     * @return void
     */
    public function setStage(AssistantPipelineStage|string $stage): void
    {
        $this->stage = $stage instanceof AssistantPipelineStage ? $stage->value : $stage;
    }


    /**
     * Returns the raw stage value.
     *
     * @return string
     */
    public function getStageValue(): string
    {
        return $this->stage;
    }


    /**
     * Returns whether the assistant identity prompt is included.
     *
     * @return bool
     */
    public function getIncludeIdentityPrompt(): bool
    {
        return $this->includeIdentityPrompt;
    }


    /**
     * Sets whether the assistant identity prompt is included.
     *
     * @param bool $includeIdentityPrompt Include assistant identity prompt.
     * @return void
     */
    public function setIncludeIdentityPrompt(bool $includeIdentityPrompt): void
    {
        $this->includeIdentityPrompt = $includeIdentityPrompt;
    }


    /**
     * Returns whether the assistant behavior rules are included.
     *
     * @return bool
     */
    public function getIncludeBehaviorRules(): bool
    {
        return $this->includeBehaviorRules;
    }


    /**
     * Sets whether the assistant behavior rules are included.
     *
     * @param bool $includeBehaviorRules Include assistant behavior rules.
     * @return void
     */
    public function setIncludeBehaviorRules(bool $includeBehaviorRules): void
    {
        $this->includeBehaviorRules = $includeBehaviorRules;
    }


    /**
     * Returns whether the assistant retrieval rules are included.
     *
     * @return bool
     */
    public function getIncludeRetrievalRules(): bool
    {
        return $this->includeRetrievalRules;
    }


    /**
     * Sets whether the assistant retrieval rules are included.
     *
     * @param bool $includeRetrievalRules Include assistant retrieval rules.
     * @return void
     */
    public function setIncludeRetrievalRules(bool $includeRetrievalRules): void
    {
        $this->includeRetrievalRules = $includeRetrievalRules;
    }


    /**
     * Returns whether the assistant output rules are included.
     *
     * @return bool
     */
    public function getIncludeOutputRules(): bool
    {
        return $this->includeOutputRules;
    }


    /**
     * Sets whether the assistant output rules are included.
     *
     * @param bool $includeOutputRules Include assistant output rules.
     * @return void
     */
    public function setIncludeOutputRules(bool $includeOutputRules): void
    {
        $this->includeOutputRules = $includeOutputRules;
    }


    /**
     * Returns the step identity.
     *
     * @return string
     */
    public function getStepIdentity(): string
    {
        return $this->stepIdentity;
    }


    /**
     * Sets the step identity.
     *
     * @param string $stepIdentity Step identity.
     * @return void
     */
    public function setStepIdentity(string $stepIdentity): void
    {
        $this->stepIdentity = $stepIdentity;
    }


    /**
     * Returns the step behavior rules.
     *
     * @return string
     */
    public function getStepBehaviorRules(): string
    {
        return $this->stepBehaviorRules;
    }


    /**
     * Sets the step behavior rules.
     *
     * @param string $stepBehaviorRules Step behavior rules.
     * @return void
     */
    public function setStepBehaviorRules(string $stepBehaviorRules): void
    {
        $this->stepBehaviorRules = $stepBehaviorRules;
    }


    /**
     * Returns the step retrieval rules.
     *
     * @return string
     */
    public function getStepRetrievalRules(): string
    {
        return $this->stepRetrievalRules;
    }


    /**
     * Sets the step retrieval rules.
     *
     * @param string $stepRetrievalRules Step retrieval rules.
     * @return void
     */
    public function setStepRetrievalRules(string $stepRetrievalRules): void
    {
        $this->stepRetrievalRules = $stepRetrievalRules;
    }


    /**
     * Returns the step output rules.
     *
     * @return string
     */
    public function getStepOutputRules(): string
    {
        return $this->stepOutputRules;
    }


    /**
     * Sets the step output rules.
     *
     * @param string $stepOutputRules Step output rules.
     * @return void
     */
    public function setStepOutputRules(string $stepOutputRules): void
    {
        $this->stepOutputRules = $stepOutputRules;
    }


    /**
     * Returns the typed history mode.
     *
     * @return \Madj2k\AiCore\Assistant\Enum\HistoryMode
     */
    public function getHistoryMode(): HistoryMode
    {
        return HistoryMode::tryFrom($this->historyMode) ?? HistoryMode::LastN;
    }


    /**
     * Sets the history mode.
     *
     * @param \Madj2k\AiCore\Assistant\Enum\HistoryMode|string $historyMode History mode.
     * @return void
     */
    public function setHistoryMode(HistoryMode|string $historyMode): void
    {
        $this->historyMode = $historyMode instanceof HistoryMode ? $historyMode->value : $historyMode;
    }


    /**
     * Returns the raw history mode value.
     *
     * @return string
     */
    public function getHistoryModeValue(): string
    {
        return $this->historyMode;
    }


    /**
     * Returns the history limit.
     *
     * @return int
     */
    public function getHistoryLimit(): int
    {
        return $this->historyLimit;
    }


    /**
     * Sets the history limit.
     *
     * @param int $historyLimit History limit.
     * @return void
     */
    public function setHistoryLimit(int $historyLimit): void
    {
        $this->historyLimit = $historyLimit;
    }


    /**
     * Returns the LLM model override.
     *
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }


    /**
     * Sets the LLM model override.
     *
     * @param string $model LLM model override.
     * @return void
     */
    public function setModel(string $model): void
    {
        $this->model = $model;
    }


    /**
     * Returns the LLM temperature.
     *
     * @return float
     */
    public function getTemperature(): float
    {
        return $this->temperature;
    }


    /**
     * Sets the LLM temperature.
     *
     * @param float $temperature LLM temperature.
     * @return void
     */
    public function setTemperature(float $temperature): void
    {
        $this->temperature = $temperature;
    }


    /**
     * Returns the maximum completion tokens.
     *
     * @return int
     */
    public function getMaxTokens(): int
    {
        return $this->maxTokens;
    }


    /**
     * Sets the maximum completion tokens.
     *
     * @param int $maxTokens Maximum completion tokens.
     * @return void
     */
    public function setMaxTokens(int $maxTokens): void
    {
        $this->maxTokens = $maxTokens;
    }


    /**
     * Returns maximum retrieval results.
     *
     * @return int
     */
    public function getMaxRetrievalResults(): int
    {
        return $this->maxRetrievalResults;
    }


    /**
     * Sets maximum retrieval results.
     *
     * @param int $maxRetrievalResults Maximum retrieval results.
     * @return void
     */
    public function setMaxRetrievalResults(int $maxRetrievalResults): void
    {
        $this->maxRetrievalResults = $maxRetrievalResults;
    }


    /**
     * Returns the vector store connection override.
     *
     * @return VectorStoreConnectionConfigurationInterface|null Vector store connection.
     */
    public function getRetrievalVectorStoreConnection(): ?VectorStoreConnectionConfigurationInterface
    {
        return $this->retrievalVectorStoreConnection;
    }


    /**
     * Sets the vector store connection override.
     *
     * @param VectorStoreConnection|null $retrievalVectorStoreConnection Vector store connection.
     * @return void
     */
    public function setRetrievalVectorStoreConnection(
        ?VectorStoreConnection $retrievalVectorStoreConnection,
    ): void {
        $this->retrievalVectorStoreConnection = $retrievalVectorStoreConnection;
    }


    /**
     * Returns the vector collection override.
     *
     * @return string Collection name.
     */
    public function getRetrievalCollection(): string
    {
        return trim($this->retrievalCollection);
    }


    /**
     * Sets the vector collection override.
     *
     * @param string $retrievalCollection Collection name.
     * @return void
     */
    public function setRetrievalCollection(string $retrievalCollection): void
    {
        $this->retrievalCollection = trim($retrievalCollection);
    }


    /**
     * Returns the retrieval score threshold.
     *
     * @return float
     */
    public function getScoreThreshold(): float
    {
        return $this->scoreThreshold;
    }


    /**
     * Sets the retrieval score threshold.
     *
     * @param float $scoreThreshold Retrieval score threshold.
     * @return void
     */
    public function setScoreThreshold(float $scoreThreshold): void
    {
        $this->scoreThreshold = $scoreThreshold;
    }


    /**
     * Returns the maximum context chunks.
     *
     * @return int
     */
    public function getMaxContextChunks(): int
    {
        return $this->maxContextChunks;
    }


    /**
     * Sets the maximum context chunks.
     *
     * @param int $maxContextChunks Maximum context chunks.
     * @return void
     */
    public function setMaxContextChunks(int $maxContextChunks): void
    {
        $this->maxContextChunks = $maxContextChunks;
    }


    /**
     * Returns the maximum context characters.
     *
     * @return int
     */
    public function getMaxContextCharacters(): int
    {
        return $this->maxContextCharacters;
    }


    /**
     * Sets the maximum context characters.
     *
     * @param int $maxContextCharacters Maximum context characters.
     * @return void
     */
    public function setMaxContextCharacters(int $maxContextCharacters): void
    {
        $this->maxContextCharacters = $maxContextCharacters;
    }


    /**
     * Returns prompt metadata fields as raw comma-separated string.
     *
     * @return string
     */
    public function getPromptMetadataFields(): string
    {
        return $this->promptMetadataFields;
    }


    /**
     * Sets prompt metadata fields as raw comma-separated string.
     *
     * @param string $promptMetadataFields Prompt metadata fields.
     * @return void
     */
    public function setPromptMetadataFields(string $promptMetadataFields): void
    {
        $this->promptMetadataFields = $promptMetadataFields;
    }


    /**
     * Returns prompt metadata fields as normalized list.
     *
     * @return array<int,string>
     */
    public function getPromptMetadataFieldList(): array
    {
        return $this->splitList($this->promptMetadataFields);
    }


    /**
     * Returns the typed failure strategy.
     *
     * @return \Madj2k\AiCore\Assistant\Enum\AssistantPipelineFailureStrategy
     */
    public function getFailureStrategy(): AssistantPipelineFailureStrategy
    {
        $failureStrategy = AssistantPipelineFailureStrategy::tryFrom($this->failureStrategy);
        if ($failureStrategy === null || $failureStrategy === AssistantPipelineFailureStrategy::Fallback) {
            return AssistantPipelineFailureStrategy::Continue;
        }

        return $failureStrategy;
    }


    /**
     * Sets the failure strategy.
     *
     * @param \Madj2k\AiCore\Assistant\Enum\AssistantPipelineFailureStrategy|string $failureStrategy Failure strategy.
     * @return void
     */
    public function setFailureStrategy(AssistantPipelineFailureStrategy|string $failureStrategy): void
    {
        $value = $failureStrategy instanceof AssistantPipelineFailureStrategy
            ? $failureStrategy->value
            : $failureStrategy;
        $this->failureStrategy = $value === AssistantPipelineFailureStrategy::Fallback->value
            ? AssistantPipelineFailureStrategy::Continue->value
            : $value;
    }


    /**
     * Returns the raw failure strategy value.
     *
     * @return string
     */
    public function getFailureStrategyValue(): string
    {
        return $this->failureStrategy;
    }


    /**
     * Tells whether this step uses an LLM prompt.
     *
     * @return bool
     */
    public function isLlmStep(): bool
    {
        return $this->getType() !== AssistantPipelineProcessorType::QdrantRetrieval;
    }


    /**
     * Splits a comma-separated list into normalized values.
     *
     * @param string $value Raw comma-separated list.
     * @return array<int,string>
     */
    private function splitList(string $value): array
    {
        return array_values(array_filter(
            array_map(static fn (string $item): string => trim($item), explode(',', $value)),
            static fn (string $item): bool => $item !== ''
        ));
    }
}
