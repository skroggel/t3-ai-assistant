<?php
declare(strict_types=1);

$ll = 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_connection_ai.xlf:';

return [
    'ctrl' => [
        'title' => $ll . 'tx_aiassistant_connection_ai',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'sortby' => 'sorting',
        'rootLevel' => 1,
        'searchFields' => 'title,connector_identifier,base_url,default_model,embedding_model',
        'iconfile' => 'EXT:ai_assistant/Resources/Public/Icons/Extension.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => '
                title, connector_identifier,
                --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_connection_ai.xlf:tx_aiassistant_connection_ai.tab_credentials,
                    base_url, api_key, organization, project,
                --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_connection_ai.xlf:tx_aiassistant_connection_ai.tab_defaults,
                    default_model, default_temperature, embedding_model, embedding_temperature, additional_options
            ',
        ]
    ],
    'columns' => [
        'title' => [
            'label' => $ll . 'tx_aiassistant_connection_ai.title',
            'description' => $ll . 'tx_aiassistant_connection_ai.title_desc',
            'config' => [
                'type' => 'input',
                'required' => true,
                'eval' => 'trim',
                'max' => 255,
                'size' => 40,
            ],
        ],
        'connector_identifier' => [
            'label' => $ll . 'tx_aiassistant_connection_ai.connector_identifier',
            'description' => $ll . 'tx_aiassistant_connection_ai.connector_identifier_desc',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => \Madj2k\AiAssistant\Connection\TCA\AiConnectorItems::class . '->addItems',
                'default' => 'openai',
                'required' => true,
            ],
        ],
        'base_url' => [
            'label' => $ll . 'tx_aiassistant_connection_ai.base_url',
            'description' => $ll . 'tx_aiassistant_connection_ai.base_url_desc',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'max' => 512,
                'size' => 60,
                'placeholder' => 'https://api.openai.com/v1',
                'default' => 'https://api.openai.com/v1',
            ],
        ],
        'api_key' => [
            'label' => $ll . 'tx_aiassistant_connection_ai.api_key',
            'description' => $ll . 'tx_aiassistant_connection_ai.api_key_desc',
            'config' => [
                'type' => 'password',
                'eval' => 'trim',
                'max' => 1024,
                'size' => 40,
                'passwordGenerator' => false,
                'hashed' => false,
                'required' => true,
            ],
        ],
        'organization' => [
            'label' => $ll . 'tx_aiassistant_connection_ai.organization',
            'description' => $ll . 'tx_aiassistant_connection_ai.organization_desc',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'max' => 255,
                'size' => 40,
            ],
        ],
        'project' => [
            'label' => $ll . 'tx_aiassistant_connection_ai.project',
            'description' => $ll . 'tx_aiassistant_connection_ai.project_desc',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'max' => 255,
                'size' => 40,
            ],
        ],
        'default_model' => [
            'label' => $ll . 'tx_aiassistant_connection_ai.default_model',
            'description' => $ll . 'tx_aiassistant_connection_ai.default_model_desc',
            'config' => [
                'type' => 'input',
                'required' => true,
                'eval' => 'trim',
                'max' => 255,
                'size' => 40,
                'placeholder' => 'gpt-4o-mini',
            ],
        ],
        'default_temperature' => [
            'label' => $ll . 'tx_aiassistant_connection_ai.default_temperature',
            'description' => $ll . 'tx_aiassistant_connection_ai.default_temperature_desc',
            'config' => [
                'type' => 'number',
                'format' => 'decimal',
                'default' => 0.2,
            ],
        ],
        'embedding_model' => [
            'label' => $ll . 'tx_aiassistant_connection_ai.embedding_model',
            'description' => $ll . 'tx_aiassistant_connection_ai.embedding_model_desc',
            'config' => [
                'type' => 'input',
                'required' => true,
                'eval' => 'trim',
                'max' => 255,
                'size' => 40,
                'placeholder' => 'text-embedding-3-small',
            ],
        ],
        'embedding_temperature' => [
            'label' => $ll . 'tx_aiassistant_connection_ai.embedding_temperature',
            'description' => $ll . 'tx_aiassistant_connection_ai.embedding_temperature_desc',
            'config' => [
                'type' => 'number',
                'format' => 'decimal',
                'default' => 0.0,
            ],
        ],
        'additional_options' => [
            'label' => $ll . 'tx_aiassistant_connection_ai.additional_options',
            'description' => $ll . 'tx_aiassistant_connection_ai.additional_options_desc',
            'config' => [
                'type' => 'text',
                'rows' => 5,
            ],
        ]
    ],
];
