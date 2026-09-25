<?php
declare(strict_types=1);

$ll = 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_profile.xlf:';

return [
    'ctrl' => [
        'title' => $ll . 'tx_aiassistant_assistant_profile',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'sortby' => 'sorting',
        'rootLevel' => -1,
        'searchFields' => 'title,assistant_label',
        'iconfile' => 'EXT:ai_assistant/Resources/Public/Icons/Extension.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => '
                --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_profile.xlf:tx_aiassistant_assistant_profile.tab_assistant,
                    title, assistant_label,
                    intro_text, initial_message,
                --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_profile.xlf:tx_aiassistant_assistant_profile.tab_prompts,
                    identity_prompt, behavior_rules, retrieval_rules, output_rules,
                --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_profile.xlf:tx_aiassistant_assistant_profile.tab_pipeline,
                    chat_pipeline_steps,
                --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_profile.xlf:tx_aiassistant_assistant_profile.tab_connections,
                    --palette--;;connections,
                    mcp_connections,

            ',
        ]
    ],
    'palettes' => [
        'connections' => [
            'label' => $ll . 'tx_aiassistant_assistant_profile.palette_connections',
            'showitem' => 'ai_connection, --linebreak--, vector_store_connection',
        ]
    ],
    'columns' => [
        'title' => [
            'label' => $ll . 'tx_aiassistant_assistant_profile.title',
            'description' => $ll . 'tx_aiassistant_assistant_profile.title_desc',
            'config' => [
                'type' => 'input',
                'required' => true,
                'eval' => 'trim',
                'max' => 255,
                'size' => 40,
            ],
        ],
        'assistant_label' => [
            'label' => $ll . 'tx_aiassistant_assistant_profile.assistant_label',
            'description' => $ll . 'tx_aiassistant_assistant_profile.assistant_label_desc',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'max' => 255,
                'size' => 40,
            ],
        ],
        'ai_connection' => [
            'label' => $ll . 'tx_aiassistant_assistant_profile.ai_connection',
            'description' => $ll . 'tx_aiassistant_assistant_profile.ai_connection_desc',
            'config' => [
                'type' => 'group',
                'allowed' => 'tx_aiassistant_connection_ai',
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ],
        'vector_store_connection' => [
            'label' => $ll . 'tx_aiassistant_assistant_profile.vector_store_connection',
            'description' => $ll . 'tx_aiassistant_assistant_profile.vector_store_connection_desc',
            'config' => [
                'type' => 'group',
                'allowed' => 'tx_aiassistant_connection_vector_database',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'mcp_connections' => [
            'label' => 'MCP connections',
            'description' => 'MCP servers available to this assistant.',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_aiassistant_connection_mcp',
                'MM' => 'tx_aiassistant_assistant_profile_mcp_mm',
                'size' => 5,
                'maxitems' => 50,
            ],
        ],
        'intro_text' => [
            'label' => $ll . 'tx_aiassistant_assistant_profile.intro_text',
            'description' => $ll . 'tx_aiassistant_assistant_profile.intro_text_desc',
            'config' => [
                'type' => 'text',
                'rows' => 3,
            ],
        ],
        'initial_message' => [
            'label' => $ll . 'tx_aiassistant_assistant_profile.initial_message',
            'description' => $ll . 'tx_aiassistant_assistant_profile.initial_message_desc',
            'config' => [
                'type' => 'text',
                'rows' => 3,
            ],
        ],
        'identity_prompt' => [
            'label' => $ll . 'tx_aiassistant_assistant_profile.identity_prompt',
            'description' => $ll . 'tx_aiassistant_assistant_profile.identity_prompt_desc',
            'config' => [
                'type' => 'text',
                'rows' => 5,
            ],
        ],
        'behavior_rules' => [
            'label' => $ll . 'tx_aiassistant_assistant_profile.behavior_rules',
            'description' => $ll . 'tx_aiassistant_assistant_profile.behavior_rules_desc',
            'config' => [
                'type' => 'text',
                'rows' => 5,
            ],
        ],
        'retrieval_rules' => [
            'label' => $ll . 'tx_aiassistant_assistant_profile.retrieval_rules',
            'description' => $ll . 'tx_aiassistant_assistant_profile.retrieval_rules_desc',
            'config' => [
                'type' => 'text',
                'rows' => 5,
            ],
        ],
        'output_rules' => [
            'label' => $ll . 'tx_aiassistant_assistant_profile.output_rules',
            'description' => $ll . 'tx_aiassistant_assistant_profile.output_rules_desc',
            'config' => [
                'type' => 'text',
                'rows' => 5,
            ],
        ],
        'chat_pipeline_steps' => [
            'label' => $ll . 'tx_aiassistant_assistant_profile.chat_pipeline_steps',
            'description' => $ll . 'tx_aiassistant_assistant_profile.chat_pipeline_steps_desc',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_aiassistant_assistant_pipeline_step',
                'foreign_field' => 'assistant_profile',
                'foreign_sortby' => 'sorting',
                'appearance' => [
                    'collapseAll' => true,
                    'expandSingle' => true,
                    'useSortable' => true,
                ],
            ],
        ]
    ],
];
