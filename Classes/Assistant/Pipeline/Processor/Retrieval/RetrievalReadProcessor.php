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

namespace Madj2k\AiAssistant\Assistant\Pipeline\Processor\Retrieval;

use Madj2k\AiAssistant\Assistant\Context\Context;
use Madj2k\AiAssistant\Assistant\DTO\LastRetrievalResult;
use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep;
use Madj2k\AiAssistant\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiAssistant\Assistant\Log\PipelineLogMetaData;
use Madj2k\AiAssistant\Assistant\Log\PipelineLogger;
use Madj2k\AiAssistant\Assistant\Memory\SessionMemory;
use Madj2k\AiAssistant\Assistant\Pipeline\Processor\IO\ProcessorIoTrait;
use Madj2k\AiAssistant\Assistant\Pipeline\Processor\ProcessorInterface;

/**
 * Class RetrievalReadProcessor
 *
 * Reads the last retrieval result from session memory and writes it into the current retrieval context.
 * Use this as the first memory step in follow-up pipelines that should reuse the previous retrieval.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final readonly class RetrievalReadProcessor implements ProcessorInterface
{

    /**
     * Processor identifier.
     *
     * @var string
     */
    private const IDENTIFIER = 'aiassistant.memory.retrieval_read';


    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Assistant\Memory\SessionMemory $sessionMemory Session memory.
     * @param \Madj2k\AiAssistant\Assistant\Log\PipelineLogger $pipelineLogger Pipeline logger.
     */
    public function __construct(
        private SessionMemory $sessionMemory,
        private PipelineLogger $pipelineLogger,
    ) {
    }


    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return self::IDENTIFIER;
    }


    /**
     * @inheritDoc
     */
    public function supports(AssistantPipelineProcessorType $type): bool
    {
        return $type === AssistantPipelineProcessorType::Memory;
    }


    /**
     * @inheritDoc
     */
    public function canProcess(Context $context, AssistantPipelineStep $step): bool
    {
        return trim($context->getRequest()->getChatIdentifier()) !== '';
    }


    /**
     * @inheritDoc
     */
    public function process(
        Context $context,
        AssistantPipelineStep $step,
        ?PipelineLogMetaData $logContext = null
    ): void {
        $chatIdentifier = $context->getRequest()->getChatIdentifier();
        $lastRetrievalResult = $this->sessionMemory->getLastRetrievalResult($chatIdentifier);

        if (!$lastRetrievalResult instanceof LastRetrievalResult) {
            throw new \RuntimeException(
                sprintf('No last retrieval result found for chat identifier "%s".', $chatIdentifier),
                1782001201
            );
        }

        $context->getRetrieval()->setProcessorIdentifier(
            $lastRetrievalResult->retrievalIdentifier !== ''
                ? $lastRetrievalResult->retrievalIdentifier
                : self::IDENTIFIER
        );
        $context->getRetrieval()->setResults($lastRetrievalResult->documents);
        $context->getRetrieval()->setRawResults($lastRetrievalResult->rawData);

        $payload = [
            'chat_identifier' => $chatIdentifier,
            'retrieval_identifier' => $lastRetrievalResult->retrievalIdentifier,
            'document_count' => $lastRetrievalResult->getDocumentCount(),
            'raw_result_count' => count($lastRetrievalResult->rawData),
            'created_at' => $lastRetrievalResult->createdAt,
        ];

        if ($logContext instanceof PipelineLogMetaData) {
            $this->pipelineLogger->event('memory.retrieval_read.completed', $logContext, array_merge($payload, [
                'step_title' => $step->getTitle(),
                'processor_type' => $this->getIdentifier(),
            ]));
        }

        $context->getProcessingTrace()->add(
            'memory.retrieval_read.completed',
            $step->getUid(),
            $chatIdentifier,
            $payload
        );
    }
}
