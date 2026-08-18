<?php
declare(strict_types=1);

$ll = 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:';

return [
    'ctrl' => [
        'title' => $ll . 'tx_aiassistant_assistant_pipeline_step',
        'label' => 'title',
        'type' => 'type',
        'hideTable' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'sortby' => 'sorting',
        'rootLevel' => 1,
        'searchFields' => 'title,type,stage,processor_identifier',
        'iconfile' => 'EXT:ai_assistant/Resources/Public/Icons/Extension.svg',
    ],
    'types' => \Madj2k\AiAssistant\Assistant\TCA\PipelineStepTypeConfiguration::getTypes(),
    'palettes' => [
        'base' => [
            'showitem' => 'title, --linebreak--, type, --linebreak--, processor_identifier, --linebreak--, enabled, sorting, stage',
        ],

        'includes' => [
            'showitem' => 'include_identity_prompt, --linebreak--, include_behavior_rules, --linebreak--, include_retrieval_rules, --linebreak--, include_output_rules',
        ],

        'stepPrompts' => [
            'showitem' => 'step_identity, --linebreak--, step_behavior_rules, --linebreak--, step_retrieval_rules, --linebreak--, step_output_rules',
        ],

        'llm' => [
            'showitem' => 'history_mode, --linebreak--, history_limit, --linebreak--, model, --linebreak--, temperature, --linebreak--, max_tokens',
        ],

        'retrieval' => [
            'showitem' => 'max_retrieval_results, --linebreak--, score_threshold, --linebreak--, prompt_metadata_fields',
        ],

        'context' => [
            'showitem' => 'max_context_chunks, --linebreak--, max_context_characters, --linebreak--, prompt_metadata_fields',
        ],

        'runtime' => [
            'showitem' => 'failure_strategy',
        ],
    ],
    'columns' => [
        'assistant_profile' => [
            'label' => $ll . 'tx_aiassistant_assistant_pipeline_step.assistant_profile',
            'description' => $ll . 'tx_aiassistant_assistant_pipeline_step.assistant_profile.description',
            'config' => [
                'type' => 'group',
                'allowed' => 'tx_aiassistant_assistant_profile',
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ],
        'title' => [
            'label' => $ll . 'tx_aiassistant_assistant_pipeline_step.title',
            'description' => $ll . 'tx_aiassistant_assistant_pipeline_step.title.description',
            'config' => [
                'type' => 'input',
                'required' => true,
                'eval' => 'trim',
                'max' => 255,
                'size' => 40,
            ],
        ],
        'type' => [
            'onChange' => 'reload',
            'label' => $ll . 'tx_aiassistant_assistant_pipeline_step.type',
            'description' => $ll . 'tx_aiassistant_assistant_pipeline_step.type.description',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'required' => true,
                'items' => \Madj2k\AiAssistant\Assistant\TCA\PipelineStepTypeConfiguration::getItems(),
                'default' => \Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType::QueryOptimizer->value,
            ],
        ],
        'processor_identifier' => [
            'label' => $ll . 'tx_aiassistant_assistant_pipeline_step.processor_identifier',
            'description' => $ll . 'tx_aiassistant_assistant_pipeline_step.processor_identifier.description',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'required' => true,
                'itemsProcFunc' => \Madj2k\AiAssistant\Assistant\TCA\PipelineProcessorItems::class . '->items',
            ],
        ],
        'stage' => [
            'label' => $ll . 'tx_aiassistant_assistant_pipeline_step.stage',
            'description' => $ll . 'tx_aiassistant_assistant_pipeline_step.stage.description',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => 'retrieval',
                'items' => [
                    ['label' => $ll . 'tx_aiassistant_assistant_pipeline_step.stage.pre_retrieval', 'value' => 'pre_retrieval'],
                    ['label' => $ll . 'tx_aiassistant_assistant_pipeline_step.stage.retrieval', 'value' => 'retrieval'],
                    ['label' => $ll . 'tx_aiassistant_assistant_pipeline_step.stage.post_retrieval', 'value' => 'post_retrieval'],
                    ['label' => $ll . 'tx_aiassistant_assistant_pipeline_step.stage.pre_answer', 'value' => 'pre_answer'],
                    ['label' => $ll . 'tx_aiassistant_assistant_pipeline_step.stage.post_answer', 'value' => 'post_answer']
                ],
            ],
        ],
        'include_identity_prompt' => [
            'label' => $ll . 'tx_aiassistant_assistant_pipeline_step.include_identity_prompt',
            'description' => $ll . 'tx_aiassistant_assistant_pipeline_step.include_identity_prompt.description',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 1,
            ],
        ],
        'include_behavior_rules' => [
            'label' => $ll . 'tx_aiassistant_assistant_pipeline_step.include_behavior_rules',
            'description' => $ll . 'tx_aiassistant_assistant_pipeline_step.include_behavior_rules.description',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 0,
            ],
        ],
        'include_retrieval_rules' => [
            'label' => $ll . 'tx_aiassistant_assistant_pipeline_step.include_retrieval_rules',
            'description' => $ll . 'tx_aiassistant_assistant_pipeline_step.include_retrieval_rules.description',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 1,
            ],
        ],
        'include_output_rules' => [
            'label' => $ll . 'tx_aiassistant_assistant_pipeline_step.include_output_rules',
            'description' => $ll . 'tx_aiassistant_assistant_pipeline_step.include_output_rules.description',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 0,
            ],
        ],
        'step_identity' => [
            'label' => $ll . 'tx_aiassistant_assistant_pipeline_step.step_identity',
            'description' => $ll . 'tx_aiassistant_assistant_pipeline_step.step_identity.description',
            'config' => [
                'type' => 'text',
                'rows' => 3,
            ],
        ],
        'step_behavior_rules' => [
            'label' => $ll . 'tx_aiassistant_assistant_pipeline_step.step_behavior_rules',
            'description' => $ll . 'tx_aiassistant_assistant_pipeline_step.step_behavior_rules.description',
            'config' => [
                'type' => 'text',
                'rows' => 4,
            ],
        ],
        'step_retrieval_rules' => [
            'label' => $ll . 'tx_aiassistant_assistant_pipeline_step.step_retrieval_rules',
            'description' => $ll . 'tx_aiassistant_assistant_pipeline_step.step_retrieval_rules.description',
            'config' => [
                'type' => 'text',
                'rows' => 4,
            ],
        ],
        'step_output_rules' => [
            'label' => $ll . 'tx_aiassistant_assistant_pipeline_step.step_output_rules',
            'description' => $ll . 'tx_aiassistant_assistant_pipeline_step.step_output_rules.description',
            'config' => [
                'type' => 'text',
                'rows' => 4,
            ],
        ],
        'history_mode' => [
            'label' => $ll . 'tx_aiassistant_assistant_pipeline_step.history_mode',
            'description' => $ll . 'tx_aiassistant_assistant_pipeline_step.history_mode.description',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => 'last_n',
                'items' => [
                    ['label' => $ll . 'tx_aiassistant_assistant_pipeline_step.history_mode.none', 'value' => 'none'],
                    ['label' => $ll . 'tx_aiassistant_assistant_pipeline_step.history_mode.last_n', 'value' => 'last_n']
                ],
            ],
        ],
        'history_limit' => [
            'label' => $ll . 'tx_aiassistant_assistant_pipeline_step.history_limit',
            'description' => $ll . 'tx_aiassistant_assistant_pipeline_step.history_limit.description',
            'config' => [
                'type' => 'number',
                'format' => 'integer',
                'default' => 5,
            ],
        ],
        'model' => [
            'label' => $ll . 'tx_aiassistant_assistant_pipeline_step.model',
            'description' => $ll . 'tx_aiassistant_assistant_pipeline_step.model.description',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'max' => 255,
                'size' => 40,
            ],
        ],
        'temperature' => [
            'label' => $ll . 'tx_aiassistant_assistant_pipeline_step.temperature',
            'description' => $ll . 'tx_aiassistant_assistant_pipeline_step.temperature.description',
            'config' => [
                'type' => 'number',
                'format' => 'decimal',
                'default' => 0.0,
            ],
        ],
        'max_tokens' => [
            'label' => $ll . 'tx_aiassistant_assistant_pipeline_step.max_tokens',
            'description' => $ll . 'tx_aiassistant_assistant_pipeline_step.max_tokens.description',
            'config' => [
                'type' => 'number',
                'format' => 'integer',
                'default' => 500,
            ],
        ],
        'max_retrieval_results' => [
            'label' => $ll . 'tx_aiassistant_assistant_pipeline_step.max_retrieval_results',
            'description' => $ll . 'tx_aiassistant_assistant_pipeline_step.max_retrieval_results.description',
            'config' => [
                'type' => 'number',
                'format' => 'integer',
                'default' => 8,
            ],
        ],
        'score_threshold' => [
            'label' => $ll . 'tx_aiassistant_assistant_pipeline_step.score_threshold',
            'description' => $ll . 'tx_aiassistant_assistant_pipeline_step.score_threshold.description',
            'config' => [
                'type' => 'number',
                'format' => 'decimal',
                'default' => 0.0,
            ],
        ],
        'max_context_chunks' => [
            'label' => $ll . 'tx_aiassistant_assistant_pipeline_step.max_context_chunks',
            'description' => $ll . 'tx_aiassistant_assistant_pipeline_step.max_context_chunks.description',
            'config' => [
                'type' => 'number',
                'format' => 'integer',
                'default' => 6,
            ],
        ],
        'max_context_characters' => [
            'label' => $ll . 'tx_aiassistant_assistant_pipeline_step.max_context_characters',
            'description' => $ll . 'tx_aiassistant_assistant_pipeline_step.max_context_characters.description',
            'config' => [
                'type' => 'number',
                'format' => 'integer',
                'default' => 9000,
            ],
        ],
        'prompt_metadata_fields' => [
            'label' => $ll . 'tx_aiassistant_assistant_pipeline_step.prompt_metadata_fields',
            'description' => $ll . 'tx_aiassistant_assistant_pipeline_step.prompt_metadata_fields.description',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'max' => 255,
                'size' => 40,
                'placeholder' => $ll . 'tx_aiassistant_assistant_pipeline_step.prompt_metadata_fields.placeholder',
                'default' => 'title,url',
            ],
        ],
        'failure_strategy' => [
            'label' => $ll . 'tx_aiassistant_assistant_pipeline_step.failure_strategy',
            'description' => $ll . 'tx_aiassistant_assistant_pipeline_step.failure_strategy.description',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => 'continue',
                'items' => [
                    ['label' => $ll . 'tx_aiassistant_assistant_pipeline_step.failure_strategy.continue', 'value' => 'continue'],
                    ['label' => $ll . 'tx_aiassistant_assistant_pipeline_step.failure_strategy.stop', 'value' => 'stop'],
                    ['label' => $ll . 'tx_aiassistant_assistant_pipeline_step.failure_strategy.fallback', 'value' => 'fallback'],
                ],
            ],
        ]
    ],
];
