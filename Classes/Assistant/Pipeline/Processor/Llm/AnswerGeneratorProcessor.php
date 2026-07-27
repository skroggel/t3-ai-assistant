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


namespace Madj2k\AiAssistant\Assistant\Pipeline\Processor\Llm;

use Madj2k\AiAssistant\Assistant\Context\Context;
use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep;
use Madj2k\AiAssistant\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiAssistant\Assistant\Log\PipelineLogMetaData;
use Madj2k\AiAssistant\Assistant\Log\PipelineLogger;
use Madj2k\AiAssistant\Assistant\Pipeline\Processor\AbstractLlmProcessor;
use Madj2k\AiAssistant\Assistant\Pipeline\Processor\ProcessorStreamingInterface;
use Madj2k\AiAssistant\Assistant\Prompt\PromptBuilder;
use Madj2k\AiAssistant\Connection\Registry\AiConnectorRegistry;

/**
 * Class AnswerGeneratorProcessor
 *
 * Generates the answer candidate for the user.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class AnswerGeneratorProcessor extends AbstractLlmProcessor implements ProcessorStreamingInterface
{
    /**
     * Constructor.
     *
     * @inheritDoc
     * @param \Madj2k\AiAssistant\Assistant\Prompt\PromptBuilder $promptBuilder Prompt builder.
     */
    public function __construct(
        AiConnectorRegistry                      $aiConnectorRegistry,
        PromptBuilder                            $promptBuilder,
        PipelineLogger                           $pipelineLogger,
    ) {
        parent::__construct($aiConnectorRegistry, $promptBuilder, $pipelineLogger);
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return 'aiassistant.answer_generator.default';
    }


    /**
     * @inheritDoc
     */
    public function supports(AssistantPipelineProcessorType $type): bool
    {
        return $type === AssistantPipelineProcessorType::AnswerGenerator;
    }


    /**
     * @inheritDoc
     */
    public function canProcess(Context $context, AssistantPipelineStep $step): bool
    {
        return trim($context->getCurrentQuery()) !== '';
    }


    /**
     * @inheritDoc
     */
    public function process(Context $context, AssistantPipelineStep $step, ?PipelineLogMetaData $logContext = null): void
    {
        $messages = $this->promptBuilder->buildMessages($context, $step);
        $answer = $this->callAi($context, $messages, $step, $logContext);
        $context->getAnswer()->setCandidate($answer);

        // trace
        $context->getProcessingTrace()->add('answer_generator.completed',
            $step->getUid(),
            $messages,
            $answer
        );
    }


    /**
     * @inheritDoc
     */
    public function processStream(
        Context $context,
        AssistantPipelineStep $step,
        callable $onData,
        ?PipelineLogMetaData $logContext = null
    ): void {
        $messages = $this->promptBuilder->buildMessages($context, $step);
        $answer = $this->callAiStream($context, $messages, $step, $onData, $logContext);
        $context->getAnswer()->setCandidate($answer);

        // trace
        $context->getProcessingTrace()->add('answer_generator.completed',
            $step->getUid(),
            $messages,
            $answer
        );
    }


}
