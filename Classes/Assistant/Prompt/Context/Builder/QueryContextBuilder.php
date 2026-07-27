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

namespace Madj2k\AiAssistant\Assistant\Prompt\Context\Builder;

use Madj2k\AiAssistant\Assistant\Context\Context;
use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep;
use Madj2k\AiAssistant\Assistant\Enum\AssistantPipelineProcessorType;

/**
 * Class QueryContextBuilder
 *
 * Builds prompt sections for original and current user queries.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class QueryContextBuilder extends AbstractContextBuilder
{
    /**
     * @inheritDoc
     */
    public function supports(AssistantPipelineProcessorType $type): bool
    {
        return in_array($type, [
            AssistantPipelineProcessorType::QueryOptimizer,
            AssistantPipelineProcessorType::ContextOptimizer,
            AssistantPipelineProcessorType::AnswerGenerator,
            AssistantPipelineProcessorType::QualityGate,
        ], true);
    }

    /**
     * @inheritDoc
     */
    public function build(Context $context, AssistantPipelineStep $step): array
    {
        return match ($step->getType()) {
            AssistantPipelineProcessorType::QueryOptimizer => $this->filterSections([
                $this->section('Current Query', $context->getCurrentQuery(), 10),
                $this->section('Original User Query', $context->getRequest()->getQuery(), 20),
            ]),
            AssistantPipelineProcessorType::ContextOptimizer,
            AssistantPipelineProcessorType::QualityGate => $this->filterSections([
                $this->section('Current Query', $context->getCurrentQuery(), 10),
            ]),
            AssistantPipelineProcessorType::AnswerGenerator => $this->filterSections([
                $this->section('Original User Query', $context->getRequest()->getQuery(), 10),
                $this->section('Current Query', $context->getCurrentQuery(), 20),
            ]),
            default => [],
        };
    }
}
