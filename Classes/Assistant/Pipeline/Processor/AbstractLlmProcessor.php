<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Madj2k\AiAssistant\Assistant\Pipeline\Processor;

use Madj2k\AiAssistant\Assistant\Context\Context;
use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep;
use Madj2k\AiAssistant\Assistant\Log\PipelineLogMetaData;
use Madj2k\AiAssistant\Assistant\Log\PipelineLogger;
use Madj2k\AiAssistant\Assistant\Prompt\PromptBuilder;
use Madj2k\AiAssistant\Connection\Ai\DTO\AiMessage;
use Madj2k\AiAssistant\Connection\Ai\DTO\AiRequest;
use Madj2k\AiAssistant\Connection\Registry\AiConnectorRegistry;
use Madj2k\AiAssistant\Exception\AssistantException;

/**
 * Class AbstractLlmStepProcessor
 *
 * Base class for pipeline steps that call the LLM API.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class AbstractLlmProcessor implements ProcessorInterface
{

    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Connection\Registry\AiConnectorRegistry $aiConnectorRegistry AI connector registry.
     * @param \Madj2k\AiAssistant\Assistant\Prompt\PromptBuilder $promptBuilder Prompt builder.
     * @param \Madj2k\AiAssistant\Assistant\Log\PipelineLogger $pipelineLogger Pipeline logger.
     */
    public function __construct(
        protected readonly AiConnectorRegistry      $aiConnectorRegistry,
        protected readonly PromptBuilder            $promptBuilder,
        protected readonly PipelineLogger           $pipelineLogger,
    ) {
    }


    /**
     * Executes a synchronous LLM request and returns the response text.
     *
     * @param \Madj2k\AiAssistant\Assistant\Context\Context $context Context.
     * @param array<int,array{role:string,content:string}> $messages LLM messages.
     * @param AssistantPipelineStep $step Step configuration.
     * @param \Madj2k\AiAssistant\Assistant\Log\PipelineLogMetaData|null $logContext Optional log context.
     * @return string
     * @throws \Madj2k\AiAssistant\Exception\AppException
     * @throws \Madj2k\AiAssistant\Exception\AssistantException
     */
    protected function callAi(
        Context $context,
        array $messages,
        AssistantPipelineStep $step,
        ?PipelineLogMetaData $logContext = null
    ): string {

        $aiConnection = $context->getAssistant()->getAiConnection();
        if ($aiConnection === null) {
            throw new AssistantException(
                'No AI connection configured for assistant profile.',
                1780666101
            );
        }

        $options = [
            'model' => $step->getModel() ?? $aiConnection->getDefaultModel(),
            'temperature' => $step->getTemperature(),
            'max_tokens' => $step->getMaxTokens(),
        ];

        if ($logContext instanceof PipelineLogMetaData) {
            $this->pipelineLogger->logLlmRequest(
                $logContext,
                $step->getTitle(),
                $step->getType()->value,
                $this->withMessageSources($messages, $step),
                $options
            );
        }

        $response = $this->aiConnectorRegistry
            ->get($aiConnection->getConnectorIdentifier())
            ->chat(
                $aiConnection,
                new AiRequest(
                    messages: $this->createAiMessages($messages),
                    model: $step->getModel() ?? $aiConnection->getDefaultModel(),
                    temperature: $step->getTemperature(),
                    maxTokens: $step->getMaxTokens()
                )
            );

        $answer = trim($response->getContent());

        if ($logContext instanceof PipelineLogMetaData) {
            $this->pipelineLogger->logLlmResponse(
                $logContext,
                $step->getTitle(),
                $step->getType()->value,
                $answer,
                [
                    'usage' => $response->getUsage() ?? [],
                ]
            );
        }

        return $answer;
    }



    /**
     * Executes a streaming LLM request and returns the collected response text.
     *
     * @param \Madj2k\AiAssistant\Assistant\Context\Context $context Context.
     * @param array<int,array{role:string,content:string}> $messages LLM messages.
     * @param \Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep $step Step configuration.
     * @param callable $onData Callback for streamed chunks.
     * @param \Madj2k\AiAssistant\Assistant\Log\PipelineLogMetaData|null $logContext Optional log context.
     * @return string
     * @throws \Madj2k\AiAssistant\Exception\AppException
     * @throws \Madj2k\AiAssistant\Exception\AssistantException
     */
    protected function callAiStream(
        Context $context,
        array $messages,
        AssistantPipelineStep $step,
        callable $onData,
        ?PipelineLogMetaData $logContext = null
    ): string {

        $aiConnection = $context->getAssistant()->getAiConnection();
        if ($aiConnection === null) {
            throw new AssistantException(
                'No AI connection configured for assistant profile.',
                1780666101
            );
        }

        $options = [
            'model' => $step->getModel() ?? $aiConnection->getDefaultModel(),
            'temperature' => $step->getTemperature(),
            'max_tokens' => $step->getMaxTokens(),
            'stream' => true,
        ];

        if ($logContext instanceof PipelineLogMetaData) {
            $this->pipelineLogger->logLlmRequest(
                $logContext,
                $step->getTitle(),
                $step->getType()->value,
                $this->withMessageSources($messages, $step),
                $options
            );
        }


        /** @var string $answer */
        $answer = '';

        $this->aiConnectorRegistry
            ->get($aiConnection->getConnectorIdentifier())
            ->streamChat(
                $aiConnection,
                new AiRequest(
                    messages: $this->createAiMessages($messages),
                    model: $step->getModel() ?? $aiConnection->getDefaultModel(),
                    temperature: $step->getTemperature(),
                    maxTokens: $step->getMaxTokens()
                ),
                static function (string $chunk) use (&$answer, $onData): void {
                    $answer .= $chunk;
                    $onData($chunk);
                }
            );

        $answer = trim($answer);

        if ($logContext instanceof PipelineLogMetaData) {
            $this->pipelineLogger->logLlmResponse(
                $logContext,
                $step->getTitle(),
                $step->getType()->value,
                $answer,
                [
                    'usage' => [],
                    'streamed' => true,
                ]
            );
        }

        return $answer;
    }


    /**
     * Creates AI connector messages from plain message arrays.
     *
     * @param array<int, array{role:string, content:string}> $messages Messages.
     * @return array<int, \Madj2k\AiAssistant\Connection\Ai\DTO\AiMessage> AI messages.
     */
    protected function createAiMessages(array $messages): array
    {
        /** @var array<int, \Madj2k\AiAssistant\Connection\Ai\DTO\AiMessage> $aiMessages */
        $aiMessages = [];

        foreach ($messages as $message) {
            $aiMessages[] = new AiMessage(
                role: (string)($message['role'] ?? ''),
                content: (string)($message['content'] ?? '')
            );
        }

        return $aiMessages;
    }


    /**
     * Adds default source metadata to messages.
     *
     * @param array<int,array{role:string,content:string}> $messages Messages.
     * @param AssistantPipelineStep $step Step.
     * @return array<int,array<string,mixed>>
     */
    private function withMessageSources(array $messages, AssistantPipelineStep $step): array
    {
        $resolvedMessages = [];
        foreach ($messages as $index => $message) {
            $resolvedMessages[] = [
                'role' => (string)($message['role'] ?? ''),
                'content' => (string)($message['content'] ?? ''),
                'source' => $step->getType()->value . '.message.' . $index,
                'metadata' => [
                    'step_uid' => (int)$step->getUid(),
                    'step_title' => $step->getTitle(),
                ],
            ];
        }

        return $resolvedMessages;
    }
}
