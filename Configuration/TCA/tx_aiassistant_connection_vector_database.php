<?php
declare(strict_types=1);

$ll = 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_connection_vector_database.xlf:';

return [
    'ctrl' => [
        'title' => $ll . 'tx_aiassistant_connection_vector_database',
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
        'searchFields' => 'title,connector_identifier,endpoint,default_collection',
        'iconfile' => 'EXT:ai_assistant/Resources/Public/Icons/Extension.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => '
                title, connector_identifier,
                --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_connection_vector_database.xlf:tx_aiassistant_connection_vector_database.tab_credentials,
                    endpoint, api_key,
                --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_connection_vector_database.xlf:tx_aiassistant_connection_vector_database.tab_defaults,
                    default_collection, collections, distance, additional_options
            ',
        ]
    ],
    'columns' => [
        'title' => [
            'label' => $ll . 'tx_aiassistant_connection_vector_database.title',
            'description' => $ll . 'tx_aiassistant_connection_vector_database.title_desc',
            'config' => [
                'type' => 'input',
                'required' => true,
                'eval' => 'trim',
                'max' => 255,
                'size' => 40,
            ],
        ],
        'connector_identifier' => [
            'label' => $ll . 'tx_aiassistant_connection_vector_database.connector_identifier',
            'description' => $ll . 'tx_aiassistant_connection_vector_database.connector_identifier_desc',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => \Madj2k\AiAssistant\Connection\TCA\VectorStoreConnectorItems::class . '->addItems',
                'default' => 'qdrant',
                'required' => true,
            ],
        ],
        'endpoint' => [
            'label' => $ll . 'tx_aiassistant_connection_vector_database.endpoint',
            'description' => $ll . 'tx_aiassistant_connection_vector_database.endpoint_desc',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'max' => 512,
                'size' => 60,
                'placeholder' => 'http://localhost:6333',
            ],
        ],
        'api_key' => [
            'label' => $ll . 'tx_aiassistant_connection_vector_database.api_key',
            'description' => $ll . 'tx_aiassistant_connection_vector_database.api_key_desc',
            'config' => [
                'type' => 'password',
                'eval' => 'trim',
                'max' => 1024,
                'size' => 40,
                'passwordGenerator' => false,
                'hashed' => false,
            ],
        ],
        'default_collection' => [
            'label' => $ll . 'tx_aiassistant_connection_vector_database.default_collection',
            'description' => $ll . 'tx_aiassistant_connection_vector_database.default_collection_desc',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'max' => 255,
                'size' => 40,
            ],
        ],
        'collections' => [
            'label' => $ll . 'tx_aiassistant_connection_vector_database.collections',
            'description' => $ll . 'tx_aiassistant_connection_vector_database.collections_desc',
            'config' => [
                'type' => 'text',
                'rows' => 5,
                'eval' => 'trim',
                'placeholder' => "public-content\nproduct-content",
            ],
        ],
        'distance' => [
            'label' => $ll . 'tx_aiassistant_connection_vector_database.distance',
            'description' => $ll . 'tx_aiassistant_connection_vector_database.distance_desc',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => 'Cosine',
                'items' => [
                    ['label' => $ll . 'tx_aiassistant_connection_vector_database.distance.Cosine', 'value' => 'Cosine'],
                    ['label' => $ll . 'tx_aiassistant_connection_vector_database.distance.Dot', 'value' => 'Dot'],
                    ['label' => $ll . 'tx_aiassistant_connection_vector_database.distance.Euclid', 'value' => 'Euclid']
                ],
            ],
        ],
        'additional_options' => [
            'label' => $ll . 'tx_aiassistant_connection_vector_database.additional_options',
            'description' => $ll . 'tx_aiassistant_connection_vector_database.additional_options_desc',
            'config' => [
                'type' => 'text',
                'rows' => 5,
            ],
        ]
    ],
];
