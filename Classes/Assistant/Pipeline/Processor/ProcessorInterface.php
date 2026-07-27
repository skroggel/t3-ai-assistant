<?php
declare(strict_types=1);

/*
 * This file is part of the AI Chat extension.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */


namespace Madj2k\AiAssistant\Assistant\Pipeline\Processor;

use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep;
use Madj2k\AiAssistant\Assistant\Context\Context;
use Madj2k\AiAssistant\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiAssistant\Assistant\Log\PipelineLogMetaData;


/**
 * Interface ChatPipelineStepProcessorInterface
 *
 * Contract for one typed chat pipeline processor.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
interface ProcessorInterface
{

    /**
     * Tells which processor is to load
     * @return string
     */
    public function getIdentifier(): string;


    /**
     * Tells whether this processor supports a step type.
     *
     * @param \Madj2k\AiAssistant\Assistant\Enum\AssistantPipelineProcessorType $type Step type.
     * @return bool
     */
    public function supports(AssistantPipelineProcessorType $type): bool;


    /**
     * Tells whether the current context contains required input slots.
     *
     * @param \Madj2k\AiAssistant\Assistant\Context\Context $context Context.
     * @param \Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep $step Step.
     * @return bool
     */
    public function canProcess(Context $context, AssistantPipelineStep $step): bool;


    /**
     * Processes the step and writes its result to the context.
     *
     * @param \Madj2k\AiAssistant\Assistant\Context\Context $context Context.
     * @param \Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep $step Step.
     * @param \Madj2k\AiAssistant\Assistant\Log\PipelineLogMetaData|null $logContext Optional log context.
     * @return void
     * @throws \Madj2k\AiAssistant\Exception\ApiException
     */
    public function process(
        Context                  $context,
        AssistantPipelineStep    $step,
        ?PipelineLogMetaData $logContext = null
    ): void;
}
