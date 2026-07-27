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

namespace Madj2k\AiAssistant\Assistant\Pipeline;

use Madj2k\AiAssistant\Assistant\Enum\AssistantPipelineProcessorType;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class PipelineValidator
 *
 * Performs lightweight structural validation for configured chat pipelines.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class PipelineValidator
{
    /**
     * Returns validation messages.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep>  $steps Pipeline steps.
     * @return array<int,string>
     */
    public function validate(ObjectStorage $steps): array
    {
        $messages = [];
        $hasAnswerGeneratorOrMemory = false;

        /**
         * @var \Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep $step
         */
        foreach ($steps as $step) {
            if ($step->getType() === AssistantPipelineProcessorType::AnswerGenerator) {
                $hasAnswerGeneratorOrMemory = true;
            }
            if ($step->getType() === AssistantPipelineProcessorType::Memory) {
                $hasAnswerGeneratorOrMemory = true;
            }
        }

        if (!$hasAnswerGeneratorOrMemory) {
            $messages[] = 'The pipeline needs at least one answer generator or memory step.';
        }

        return $messages;
    }
}
