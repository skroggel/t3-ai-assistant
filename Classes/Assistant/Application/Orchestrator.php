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

namespace Madj2k\AiAssistant\Assistant\Application;

use Madj2k\AiAssistant\Assistant\Context\Context;
use Madj2k\AiAssistant\Assistant\Context\ContextFactory;
use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile;
use Madj2k\AiAssistant\Assistant\DTO\AssistantRequest;
use Madj2k\AiAssistant\Assistant\DTO\AssistantResponse;
use Madj2k\AiAssistant\Assistant\Log\PipelineLogger;
use Madj2k\AiAssistant\Assistant\Memory\SessionMemory;
use Madj2k\AiAssistant\Assistant\Pipeline\Pipeline;
use Madj2k\AiAssistant\Exception\AssistantException;

/**
 * Class Orchestrator
 *
 * Coordinates history, pipeline execution and tracing for one frontend chat turn.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final readonly class Orchestrator
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Assistant\Context\ContextFactory $contextFactory Runtime context factory.
     * @param \Madj2k\AiAssistant\Assistant\Pipeline\Pipeline $pipeline Pipeline.
     * @param \Madj2k\AiAssistant\Assistant\Log\PipelineLogger $pipelineLogger Pipeline logger.
     * @param \Madj2k\AiAssistant\Assistant\Memory\SessionMemory $sessionMemory Session memory.
     */
    public function __construct(
        private ContextFactory $contextFactory,
        private Pipeline       $pipeline,
        private PipelineLogger $pipelineLogger,
        private SessionMemory  $sessionMemory,
    ) {
    }


    /**
     * Executes one chat turn.
     *
     * @param \Madj2k\AiAssistant\Assistant\DTO\AssistantRequest $assistantRequest Assistant request.
     * @return \Madj2k\AiAssistant\Assistant\DTO\AssistantResponse
     * @throws \Throwable
     */
    public function handle(AssistantRequest $assistantRequest): AssistantResponse
    {
        return $this->execute($assistantRequest);
    }


    /**
     * Executes one chat turn and streams chunks from streaming-capable processors.
     *
     * @param \Madj2k\AiAssistant\Assistant\DTO\AssistantRequest $assistantRequest Assistant request.
     * @param callable $onData Callback for streamed chunks.
     * @return \Madj2k\AiAssistant\Assistant\DTO\AssistantResponse
     * @throws \Throwable
     */
    public function handleStream(AssistantRequest $assistantRequest, callable $onData): AssistantResponse
    {
        return $this->executeStream($assistantRequest, $onData);
    }


    /**
     * Prepares one streaming chat turn before the SSE response is created.
     *
     * This keeps session/history handling in the normal controller request phase
     * and delays only the actual pipeline streaming until response emission.
     *
     * @param \Madj2k\AiAssistant\Assistant\DTO\AssistantRequest $assistantRequest Assistant request.
     * @param callable $onData Callback for streamed chunks.
     * @return callable
     * @throws \Throwable
     */
    public function createStreamProducer(AssistantRequest $assistantRequest, callable $onData): callable
    {
        $query = $this->resolveQuery($assistantRequest);
        $assistantProfile = $this->resolveAssistantProfile($assistantRequest);

        $logMetaData = $this->pipelineLogger->createMetaData(
            $assistantRequest,
            'pipeline',
        );

        $this->sessionMemory->start($assistantRequest->chatIdentifier, $assistantRequest->startTimestamp);

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep> $steps */
        $steps = $assistantProfile->getChatPipelineSteps();

        $this->pipelineLogger->startChat($logMetaData, [
            'step_count' => count($steps),
            'streaming' => true,
        ]);

        /** @var array<int,array{role:string,content:string}> $historyMessages */
        $historyMessages = $this->sessionMemory->getMessages($assistantRequest->chatIdentifier);

        $context = $this->contextFactory->create(
            $assistantRequest,
            $historyMessages
        );

        return function () use ($assistantRequest, $query, $steps, $context, $onData, $logMetaData): AssistantResponse {
            try {
                $result = $this->pipeline->runStream($context, $steps, $onData, $logMetaData);

                $this->sessionMemory->addMessage($assistantRequest->chatIdentifier, 'user', $query);
                $this->sessionMemory->addMessage($assistantRequest->chatIdentifier, 'assistant', $result->answer);

                $this->pipelineLogger->finishChat($logMetaData, [
                    'answer_characters' => strlen($result->answer),
                    'history_count' => count($this->sessionMemory->getMessages($assistantRequest->chatIdentifier)),
                    'streaming' => true,
                ]);

                return new AssistantResponse($result->answer, $result->context, $result->debug);
            } catch (\Throwable $exception) {
                $this->pipelineLogger->failChat($logMetaData, [
                    'exception_class' => get_class($exception),
                    'exception_message' => $exception->getMessage(),
                    'streaming' => true,
                ]);

                throw $exception;
            }
        };
    }


    /**
     * Executes one chat turn synchronously.
     *
     * @param \Madj2k\AiAssistant\Assistant\DTO\AssistantRequest $assistantRequest Assistant request.
     * @return \Madj2k\AiAssistant\Assistant\DTO\AssistantResponse
     * @throws \Throwable
     */
    private function execute(AssistantRequest $assistantRequest): AssistantResponse
    {
        $query = $this->resolveQuery($assistantRequest);
        $assistantProfile = $this->resolveAssistantProfile($assistantRequest);

        $logMetaData = $this->pipelineLogger->createMetaData(
            $assistantRequest,
            'pipeline',
        );

        try {
            $this->sessionMemory->start($assistantRequest->chatIdentifier, $assistantRequest->startTimestamp);

            /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep> $steps */
            $steps = $assistantProfile->getChatPipelineSteps();

            $this->pipelineLogger->startChat($logMetaData, [
                'step_count' => count($steps),
                'streaming' => false,
            ]);

            $context = $this->contextFactory->create(
                $assistantRequest,
                $this->sessionMemory->getMessages($assistantRequest->chatIdentifier)
            );

            $result = $this->pipeline->run($context, $steps, $logMetaData);

            $this->sessionMemory->addMessage($assistantRequest->chatIdentifier, 'user', $query);
            $this->sessionMemory->addMessage($assistantRequest->chatIdentifier, 'assistant', $result->answer);

            $this->pipelineLogger->finishChat($logMetaData, [
                'answer_characters' => strlen($result->answer),
                'history_count' => count($this->sessionMemory->getMessages($assistantRequest->chatIdentifier)),
                'streaming' => false,
            ]);

            return new AssistantResponse($result->answer, $result->context, $result->debug);
        } catch (\Throwable $exception) {
            $this->pipelineLogger->failChat($logMetaData, [
                'exception_class' => get_class($exception),
                'exception_message' => $exception->getMessage(),
                'streaming' => false,
            ]);

            throw $exception;
        }
    }


    /**
     * Executes one chat turn with streaming.
     *
     * @param \Madj2k\AiAssistant\Assistant\DTO\AssistantRequest $assistantRequest Assistant request.
     * @param callable $onData Callback for streamed chunks.
     * @return \Madj2k\AiAssistant\Assistant\DTO\AssistantResponse
     * @throws \Throwable
     */
    private function executeStream(AssistantRequest $assistantRequest, callable $onData): AssistantResponse
    {
        $streamProducer = $this->createStreamProducer($assistantRequest, $onData);

        return $streamProducer();
    }


    /**
     * Resolves and validates the user query.
     *
     * @param \Madj2k\AiAssistant\Assistant\DTO\AssistantRequest $assistantRequest Assistant request.
     * @return string Query.
     * @throws \Madj2k\AiAssistant\Exception\AssistantException
     */
    private function resolveQuery(AssistantRequest $assistantRequest): string
    {
        $query = trim($assistantRequest->query);
        if ($query === '') {
            throw new AssistantException('Missing query parameter.', 1760001001);
        }

        return $query;
    }


    /**
     * Resolves and validates the assistant profile.
     *
     * @param \Madj2k\AiAssistant\Assistant\DTO\AssistantRequest $assistantRequest Assistant request.
     * @return \Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile Assistant profile.
     * @throws \Madj2k\AiAssistant\Exception\AssistantException
     */
    private function resolveAssistantProfile(AssistantRequest $assistantRequest): AssistantProfile
    {
        $assistantProfile = $assistantRequest->assistantProfile;
        if (!$assistantProfile instanceof AssistantProfile) {
            throw new AssistantException('No active assistant profile selected for this chat plugin.', 1760001002);
        }

        return $assistantProfile;
    }
}
