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


namespace Madj2k\AiAssistant\Assistant\Enum;

/**
 * Enum AssistantPipelineStepType
 *
 * Defines the supported pipeline step processors for one assistant.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
enum AssistantPipelineProcessorType: string
{
    case QueryOptimizer = 'query_optimizer';
    case Retriever = 'retriever';
    case ContextOptimizer = 'context_optimizer';
    case AnswerGenerator = 'answer_generator';
    case QualityGate = 'quality_gate';
    case Memory = 'memory';


    /**
     * Returns TCA types configuration.
     *
     * @return array<string, array<string, string>>
     */
    public static function getTcaTypes(): array
    {
        return [
            self::QueryOptimizer->value => [
                'showitem' => '--palette--;;base, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.tab_prompt_includes, --palette--;;includes, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.tab_step_prompts, --palette--;;stepPrompts, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.tab_context, --palette--;;context, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.tab_llm, --palette--;;llm, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.tab_runtime, --palette--;;runtime',
            ],
            self::Retriever->value => [
                'showitem' => '--palette--;;base, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.tab_retrieval, --palette--;;retrieval, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.tab_runtime, --palette--;;runtime',
            ],
            self::ContextOptimizer->value => [
                'showitem' => '--palette--;;base, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.tab_prompt_includes, --palette--;;includes, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.tab_step_prompts, --palette--;;stepPrompts, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.tab_context, --palette--;;context, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.tab_llm, --palette--;;llm, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.tab_runtime, --palette--;;runtime',
            ],
            self::AnswerGenerator->value => [
                'showitem' => '--palette--;;base, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.tab_prompt_includes, --palette--;;includes, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.tab_step_prompts, --palette--;;stepPrompts, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.tab_context, --palette--;;contextWithFrontendSources, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.tab_llm, --palette--;;llm, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.tab_runtime, --palette--;;runtime',
            ],
            self::QualityGate->value => [
                'showitem' => '--palette--;;base, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.tab_prompt_includes, --palette--;;includes, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.tab_step_prompts, --palette--;;stepPrompts, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.tab_context, --palette--;;context, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.tab_llm, --palette--;;llm, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.tab_runtime, --palette--;;runtime',
            ],
            self::Memory->value => [
                'showitem' => '--palette--;;base, --div--; LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.tab_runtime, --palette--;;runtime',
            ],
        ];
    }


    /**
     * Returns TCA select items.
     *
     * @return array<int, array<string, string>>
     */
    public static function getTcaItems(): array
    {
        return array_map(
            static fn (self $type): array => [
                'label' => $type->getLabel(),
                'value' => $type->value,
            ],
            self::cases()
        );
    }


    /**
     * Returns a human-readable label.
     *
     * @return string Label.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::QueryOptimizer => 'Query optimizer',
            self::Retriever => 'Retriever',
            self::ContextOptimizer => 'Context optimizer',
            self::AnswerGenerator => 'Answer generator',
            self::QualityGate => 'Quality gate',
            self::Memory => 'Memory',
        };
    }
}
