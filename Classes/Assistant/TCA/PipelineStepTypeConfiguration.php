<?php
declare(strict_types=1);

namespace Madj2k\AiAssistant\Assistant\TCA;

use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;

final class PipelineStepTypeConfiguration
{
    private const LABEL_PREFIX = 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.';

    /** @return array<string, array{showitem:string}> */
    public static function getTypes(): array
    {
        return [
            AssistantPipelineProcessorType::QueryOptimizer->value => ['showitem' => self::llmShowItem('context')],
            AssistantPipelineProcessorType::Retriever->value => ['showitem' => self::retrieverShowItem()],
            AssistantPipelineProcessorType::ContextOptimizer->value => ['showitem' => self::llmShowItem('context')],
            AssistantPipelineProcessorType::AnswerGenerator->value => ['showitem' => self::llmShowItem('contextWithFrontendSources')],
            AssistantPipelineProcessorType::QualityGate->value => ['showitem' => self::llmShowItem('context')],
            AssistantPipelineProcessorType::Memory->value => [
                'showitem' => '--palette--;;base, --div--;' . self::LABEL_PREFIX . 'tab_runtime, --palette--;;runtime',
            ],
        ];
    }

    /** @return array<int, array{label:string,value:string}> */
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

    private static function llmShowItem(string $contextPalette): string
    {
        return implode(', ', [
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
        ]);
    }

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
}
