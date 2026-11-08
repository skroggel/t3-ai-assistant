<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, version 3.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Madj2k\AiAssistant\Assistant\TCA;

use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;

/**
 * Class PipelineStepTypeConfiguration
 *
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final class PipelineStepTypeConfiguration
{

    private const string LABEL_PREFIX = 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.';

    /**
     * @return array[]
     */
    public static function getTypes(): array
    {
        return [
            AssistantPipelineProcessorType::QueryOptimizer->value => ['showitem' => self::llmShowItem('context', false, true)],
            AssistantPipelineProcessorType::IntentSelector->value => ['showitem' => self::decisionShowItem()],
            AssistantPipelineProcessorType::Retriever->value => ['showitem' => self::retrieverShowItem()],
            AssistantPipelineProcessorType::ContextOptimizer->value => ['showitem' => self::llmShowItem('context')],
            AssistantPipelineProcessorType::AnswerGenerator->value => ['showitem' => self::llmShowItem('contextWithFrontendSources', true)],
            AssistantPipelineProcessorType::QualityGate->value => ['showitem' => self::llmShowItem('context', true)],
            AssistantPipelineProcessorType::Memory->value => [
                'showitem' => '--palette--;;base, --div--;' . self::LABEL_PREFIX . 'tab_runtime, --palette--;;runtime',
            ],
        ];
    }

    /**
     * @return array<int, array{label:string,value:string}>
     */
    public static function getItems(): array
    {
        return array_map(
            static fn (AssistantPipelineProcessorType $type): array => [
                'label' => $type->getLabel(),
                'value' => $type->value,
            ],
            AssistantPipelineProcessorType::cases(),
        );
    }

    /**
     * @param string $contextPalette
     * @param bool $includeUiComponents
     * @param bool $includeDecision
     * @return string
     */
    private static function llmShowItem(
        string $contextPalette,
        bool $includeUiComponents = false,
        bool $includeDecision = false,
    ): string
    {
        $showItems = [
            '--palette--;;base',
            '--div--;' . self::LABEL_PREFIX . 'tab_prompt_includes',
            '--palette--;;includes',
            '--div--;' . self::LABEL_PREFIX . 'tab_step_prompts',
            '--palette--;;stepPrompts',
            '--div--;' . self::LABEL_PREFIX . 'tab_context',
            '--palette--;;' . $contextPalette,
            '--div--;' . self::LABEL_PREFIX . 'tab_llm',
            '--palette--;;llm',
            '--div--;' . self::LABEL_PREFIX . 'tab_runtime',
            '--palette--;;runtime',
        ];

        if ($includeUiComponents) {
            $showItems[] = '--div--;' . self::LABEL_PREFIX . 'tab_ui_components';
            $showItems[] = '--palette--;;uiComponents';
        }

        if ($includeDecision) {
            $showItems[] = '--div--;' . self::LABEL_PREFIX . 'tab_decision';
            $showItems[] = '--palette--;;decision';
        }

        return implode(', ', $showItems);
    }

    /**
     * @return string
     */
    private static function retrieverShowItem(): string
    {
        return implode(', ', [
            '--palette--;;base',
            '--div--;' . self::LABEL_PREFIX . 'tab_retrieval',
            '--palette--;;retrieval',
            '--div--;' . self::LABEL_PREFIX . 'tab_runtime',
            '--palette--;;runtime',
        ]);
    }

    /**
     * @return string
     */
    private static function decisionShowItem(): string
    {
        return implode(', ', [
            '--palette--;;base',
            '--div--;' . self::LABEL_PREFIX . 'tab_decision',
            '--palette--;;decision',
            '--div--;' . self::LABEL_PREFIX . 'tab_llm',
            '--palette--;;llm',
            '--div--;' . self::LABEL_PREFIX . 'tab_runtime',
            '--palette--;;runtime',
        ]);
    }
}
