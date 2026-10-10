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

use Madj2k\AiCore\Assistant\Configuration\AssistantConfigurationInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use Madj2k\AiAssistant\Connection\Domain\Model\AiConnection;
use Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection;

/**
 * Class AssistantProfile
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
class AssistantProfile extends AbstractEntity implements AssistantConfigurationInterface
{

    /**
     * Initializes object storage properties.
     *
     * @return void
     */
    public function __construct()
    {
        $this->chatPipelineSteps = new ObjectStorage();

    }

    /**
     * @var string
     */
    protected string $title = '';


    /**
     * @var string
     */
    protected string $assistantLabel = '';


    /**
     * @var string
     */
    protected string $introText = '';


    /**
     * @var string
     */
    protected string $initialMessage = '';


    /**
     * @var string
     */
    protected string $identityPrompt = '';


    /**
     * @var string
     */
    protected string $behaviorRules = '';


    /**
     * @var string
     */
    protected string $retrievalRules = '';


    /**
     * @var string
     */
    protected string $outputRules = '';


    /**
     * Frontend error handling mode.
     *
     * @var string
     */
    protected string $errorHandling = 'default';


    /**
     * UI component record UIDs available to the pipeline.
     *
     * @var string
     */
    protected string $uiComponents = '';


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep>
     */
    protected ObjectStorage $chatPipelineSteps;


    /**
     * AI connection.
     *
     * @var \Madj2k\AiAssistant\Connection\Domain\Model\AiConnection|null
     */
    protected ?AiConnection $aiConnection = null;


    /**
     * Vector store connection.
     *
     * @var \Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection|null
     */
    protected ?VectorStoreConnection $vectorStoreConnection = null;

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
     * Returns assistant_label.
     *
     * @return string assistant_label.
     */
    public function getAssistantLabel(): string
    {
        return $this->assistantLabel;
    }


    /**
     * Sets assistant_label.
     *
     * @param string $assistantLabel assistant_label.
     * @return void
     */
    public function setAssistantLabel(string $assistantLabel): void
    {
        $this->assistantLabel = $assistantLabel;
    }


    /**
     * Returns intro_text.
     *
     * @return string intro_text.
     */
    public function getIntroText(): string
    {
        return $this->introText;
    }


    /**
     * Sets intro_text.
     *
     * @param string $introText intro_text.
     * @return void
     */
    public function setIntroText(string $introText): void
    {
        $this->introText = $introText;
    }


    /**
     * Returns initial_message.
     *
     * @return string initial_message.
     */
    public function getInitialMessage(): string
    {
        return $this->initialMessage;
    }


    /**
     * Sets initial_message.
     *
     * @param string $initialMessage initial_message.
     * @return void
     */
    public function setInitialMessage(string $initialMessage): void
    {
        $this->initialMessage = $initialMessage;
    }


    /**
     * Returns identity_prompt.
     *
     * @return string identity_prompt.
     */
    public function getIdentityPrompt(): string
    {
        return $this->identityPrompt;
    }


    /**
     * Sets identity_prompt.
     *
     * @param string $identityPrompt identity_prompt.
     * @return void
     */
    public function setIdentityPrompt(string $identityPrompt): void
    {
        $this->identityPrompt = $identityPrompt;
    }


    /**
     * Returns behavior_rules.
     *
     * @return string behavior_rules.
     */
    public function getBehaviorRules(): string
    {
        return $this->behaviorRules;
    }


    /**
     * Sets behavior_rules.
     *
     * @param string $behaviorRules behavior_rules.
     * @return void
     */
    public function setBehaviorRules(string $behaviorRules): void
    {
        $this->behaviorRules = $behaviorRules;
    }


    /**
     * Returns retrieval_rules.
     *
     * @return string retrieval_rules.
     */
    public function getRetrievalRules(): string
    {
        return $this->retrievalRules;
    }


    /**
     * Sets retrieval_rules.
     *
     * @param string $retrievalRules retrieval_rules.
     * @return void
     */
    public function setRetrievalRules(string $retrievalRules): void
    {
        $this->retrievalRules = $retrievalRules;
    }


    /**
     * Returns output_rules.
     *
     * @return string output_rules.
     */
    public function getOutputRules(): string
    {
        return $this->outputRules;
    }


    /**
     * Sets output_rules.
     *
     * @param string $outputRules output_rules.
     * @return void
     */
    public function setOutputRules(string $outputRules): void
    {
        $this->outputRules = $outputRules;
    }


    /**
     * Returns the frontend error handling mode.
     *
     * @return string Error handling mode.
     */
    public function getErrorHandling(): string
    {
        return in_array($this->errorHandling, ['default', 'silent'], true)
            ? $this->errorHandling
            : 'default';
    }


    /**
     * Sets the frontend error handling mode.
     *
     * @param string $errorHandling Error handling mode.
     * @return void
     */
    public function setErrorHandling(string $errorHandling): void
    {
        $this->errorHandling = $errorHandling;
    }


    /**
     * Returns the configured chat pipeline steps.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep>
     */
    public function getChatPipelineSteps(): ObjectStorage
    {
         return $this->chatPipelineSteps;
    }


    /**
     * Sets the configured chat pipeline steps.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep> $chatPipelineSteps Chat pipeline steps.
     * @return void
     */
    public function setChatPipelineSteps(ObjectStorage $chatPipelineSteps): void
    {
        $this->chatPipelineSteps = $chatPipelineSteps;
    }


    /**
     * Adds one chat pipeline step.
     *
     * @param \Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep $chatPipelineStep Chat pipeline step.
     * @return void
     */
    public function addChatPipelineStep(AssistantPipelineStep $chatPipelineStep): void
    {
        $this->chatPipelineSteps->attach($chatPipelineStep);
    }


    /**
     * Removes one chat pipeline step.
     *
     * @param \Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep $chatPipelineStep Chat pipeline step.
     * @return void
     */
    public function removeChatPipelineStep(AssistantPipelineStep $chatPipelineStep): void
    {
        $this->chatPipelineSteps->detach($chatPipelineStep);
    }

    /**
     * Returns the configured UI component record UIDs.
     *
     * @return array<int,int> Component record UIDs.
     */
    public function getUiComponentUids(): array
    {
        return $this->splitList($this->uiComponents);
    }


    /**
     * Sets the configured UI component record UIDs.
     *
     * @param string $uiComponents Comma-separated component record UIDs.
     * @return void
     */
    public function setUiComponents(string $uiComponents): void
    {
        $this->uiComponents = $uiComponents;
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
     * Splits a comma-separated relation value into numeric UIDs.
     *
     * @param string $value Relation value.
     * @return array<int,int> UIDs.
     */
    private function splitList(string $value): array
    {
        return array_values(array_filter(
            array_map(static fn (string $item): int => (int)trim($item), explode(',', $value)),
            static fn (int $item): bool => $item > 0,
        ));
    }

}
