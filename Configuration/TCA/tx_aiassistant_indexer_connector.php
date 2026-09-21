<?php
declare(strict_types=1);

$ll = 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_indexer_connector.xlf:';

return [
    'ctrl' => [
        'title' => $ll . 'tx_aiassistant_indexer_connector',
        'label' => 'title',
        'type' => 'type',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'sortby' => 'sorting',
        'rootLevel' => -1,
        'searchFields' => 'title,type,base_url,client_id',
        'iconfile' => 'EXT:ai_assistant/Resources/Public/Icons/Extension.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => 'title, type, base_url, download_base_url, download_path, client_id, client_secret, verify_tls, lookback_days, custom_fields, indexed_fields',
        ]
    ],
    'columns' => [
        'title' => [
            'label' => $ll . 'tx_aiassistant_indexer_connector.title',
            'description' => $ll . 'tx_aiassistant_indexer_connector.title_desc',
            'config' => [
                'type' => 'input',
                'required' => true,
                'eval' => 'trim',
                'max' => 255,
                'size' => 40,
            ],
        ],
        'type' => [
            'label' => $ll . 'tx_aiassistant_indexer_connector.type',
            'description' => $ll . 'tx_aiassistant_indexer_connector.type_desc',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => \Madj2k\AiAssistant\Indexing\TCA\ConnectorItems::class . '->items',
                'required' => true,
            ],
        ],
        'base_url' => [
            'label' => $ll . 'tx_aiassistant_indexer_connector.base_url',
            'description' => $ll . 'tx_aiassistant_indexer_connector.base_url_desc',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'max' => 512,
                'size' => 60,
            ],
        ],
        'download_base_url' => [
            'label' => $ll . 'tx_aiassistant_indexer_connector.download_base_url',
            'description' => $ll . 'tx_aiassistant_indexer_connector.download_base_url_desc',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'max' => 512,
                'size' => 60,
            ],
        ],
        'download_path' => [
            'label' => $ll . 'tx_aiassistant_indexer_connector.download_path',
            'description' => $ll . 'tx_aiassistant_indexer_connector.download_path_desc',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'max' => 512,
                'size' => 60,
            ],
        ],
        'client_id' => [
            'label' => $ll . 'tx_aiassistant_indexer_connector.client_id',
            'description' => $ll . 'tx_aiassistant_indexer_connector.client_id_desc',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'max' => 512,
                'size' => 60,
            ],
        ],
        'client_secret' => [
            'label' => $ll . 'tx_aiassistant_indexer_connector.client_secret',
            'description' => $ll . 'tx_aiassistant_indexer_connector.client_secret_desc',
            'config' => [
                'type' => 'password',
                'eval' => 'trim',
                'max' => 512,
                'size' => 40,
                'passwordGenerator' => false,
                'hashed' => false,
            ],
        ],
        'verify_tls' => [
            'label' => $ll . 'tx_aiassistant_indexer_connector.verify_tls',
            'description' => $ll . 'tx_aiassistant_indexer_connector.verify_tls_desc',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 1,
            ],
        ],
        'lookback_days' => [
            'label' => $ll . 'tx_aiassistant_indexer_connector.lookback_days',
            'description' => $ll . 'tx_aiassistant_indexer_connector.lookback_days_desc',
            'config' => [
                'type' => 'number',
                'format' => 'integer',
                'default' => 1,
            ],
        ],
        'custom_fields' => [
            'label' => $ll . 'tx_aiassistant_indexer_connector.custom_fields',
            'description' => $ll . 'tx_aiassistant_indexer_connector.custom_fields_desc',
            'config' => [
                'type' => 'text',
                'rows' => 3,
            ],
        ],
        'indexed_fields' => [
            'label' => $ll . 'tx_aiassistant_indexer_connector.indexed_fields',
            'description' => $ll . 'tx_aiassistant_indexer_connector.indexed_fields_desc',
            'config' => [
                'type' => 'text',
                'rows' => 3,
            ],
        ]
    ],
];
