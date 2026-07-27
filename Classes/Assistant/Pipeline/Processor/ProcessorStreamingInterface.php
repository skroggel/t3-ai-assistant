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

/**
 * Interface ProcessorStreamingInterface
 *
 * Contract for processors that can stream their output.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
interface ProcessorStreamingInterface
{
    /**
     * Processes the step and streams output chunks to the callback.
     *
     * @param \Madj2k\AiAssistant\Assistant\Context\Context $context Context.
     * @param \Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep $step Step.
     * @param callable $onData Callback for streamed output chunks.
     * @param \Madj2k\AiAssistant\Assistant\Log\PipelineLogMetaData|null $logContext Optional log context.
     * @return void
     * @throws \Madj2k\AiAssistant\Exception\ApiException
     */
    public function processStream(
        Context $context,
        AssistantPipelineStep $step,
        callable $onData,
        ?PipelineLogMetaData $logContext = null
    ): void;
}
