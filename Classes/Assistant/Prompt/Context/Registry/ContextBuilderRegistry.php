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

namespace Madj2k\AiAssistant\Assistant\Prompt\Context\Registry;

use Madj2k\AiAssistant\Assistant\Context\Context;
use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep;
use Madj2k\AiAssistant\Assistant\Prompt\Context\Builder\ContextBuilderInterface;
use Madj2k\AiAssistant\Assistant\Prompt\Context\PromptSection;

/**
 * Class ContextBuilderRegistry
 *
 * Collects context sections from all builders that support a prompt purpose.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class ContextBuilderRegistry
{
    /**
     * Constructor.
     *
     * @param iterable<\Madj2k\AiAssistant\Assistant\Prompt\Context\Builder\ContextBuilderInterface> $builders Context builders.
     */
    public function __construct(
        private readonly iterable $builders
    ) {
    }


    /**
     * Builds all prompt sections for the given pipeline step.
     *
     * @param \Madj2k\AiAssistant\Assistant\Context\Context $context Current chat context.
     * @param \Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep $step Pipeline step.
     * @return array<int, \Madj2k\AiAssistant\Assistant\Prompt\Context\PromptSection> Prompt sections.
     */
    public function buildSections(Context $context, AssistantPipelineStep $step): array
    {
        $sections = [];

        foreach ($this->builders as $builder) {
            if (!$builder instanceof ContextBuilderInterface || !$builder->supports($step->getType())) {
                continue;
            }

            foreach ($builder->build($context, $step) as $section) {
                if ($section instanceof PromptSection && !$section->isEmpty()) {
                    $sections[] = $section;
                }
            }
        }

        usort(
            $sections,
            static fn (PromptSection $left, PromptSection $right): int => $left->priority <=> $right->priority
        );

        return $sections;
    }
}
