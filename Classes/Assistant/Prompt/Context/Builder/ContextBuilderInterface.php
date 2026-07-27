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
 * Class ContextBuilderInterface
 *
 * Defines a builder that contributes structured sections to a prompt context.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
interface ContextBuilderInterface
{
    /**
     * Returns whether this builder contributes sections for the processor type.
     *
     * @param \Madj2k\AiAssistant\Assistant\Enum\AssistantPipelineProcessorType $type Pipeline processor type.
     * @return bool Supports flag.
     */
    public function supports(AssistantPipelineProcessorType $type): bool;

    /**
     * Builds prompt sections for the given context and pipeline step.
     *
     * @param \Madj2k\AiAssistant\Assistant\Context\Context $context Current chat context.
     * @param \Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep $step Pipeline step.
     * @return array<int, \Madj2k\AiAssistant\Assistant\Prompt\Context\PromptSection> Prompt sections.
     */
    public function build(Context $context, AssistantPipelineStep $step): array;
}
