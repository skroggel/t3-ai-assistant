<?php
declare(strict_types=1);

$ll = 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_indexer.xlf:';

return [
    'ctrl' => [
        'title' => $ll . 'tx_aiassistant_indexer',
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
        'rootLevel' => 1,
        'searchFields' => 'title,type,indexer_identifier,adapter_identifier,collection',
        'iconfile' => 'EXT:ai_assistant/Resources/Public/Icons/Extension.svg',
    ],
    'types' => \Madj2k\AiAssistant\Indexing\Enum\IndexerType::getTcaTypes(),
    'palettes' => [
        'base' => [
            'showitem' => 'title, --linebreak--, type, --linebreak--, indexer_identifier, --linebreak--,collection',
        ],
        'connections' => [
            'showitem' => 'ai_connection, --linebreak--, vector_store_connection',
        ],
        'chunking' => [
            'showitem' => 'chunk_size, --linebreak--, chunk_overlap, --linebreak--, max_chunks, --linebreak--, min_chunk_chars',
        ],
        'file' => [
            'showitem' => 'import_path, --linebreak--, include_subfolders',
        ],
        'page' => [
            'showitem' => 'root_pages, --linebreak--, page_fields, --linebreak--, content_types, --linebreak--, content_fields, --linebreak--, additional_content_fields, --linebreak--, additional_metadata',
        ],
        'external' => [
            'showitem' => 'connector_uid, --linebreak--, adapter_identifier',
        ],
    ],
    'columns' => [
        'title' => [
            'label' => $ll . 'tx_aiassistant_indexer.title',
            'description' => $ll . 'tx_aiassistant_indexer.title_desc',
            'config' => [
                'type' => 'input',
                'placeholder' => $ll . 'tx_aiassistant_indexer.title_placeholder',
                'required' => true,
                'eval' => 'trim',
                'max' => 255,
                'size' => 40,
            ],
        ],
        'type' => [
            'label' => $ll . 'tx_aiassistant_indexer.type',
            'description' => $ll . 'tx_aiassistant_indexer.type_desc',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => \Madj2k\AiAssistant\Indexing\Enum\IndexerType::getTcaItems(),
                'required' => true,
                'default' => \Madj2k\AiAssistant\Indexing\Enum\IndexerType::Page->value,
            ],
        ],
        'indexer_identifier' => [
            'label' => $ll . 'tx_aiassistant_indexer.indexer_identifier',
            'description' => $ll . 'tx_aiassistant_indexer.indexer_identifier_desc',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => \Madj2k\AiAssistant\Indexing\TCA\IndexerItems::class . '->items',
                'required' => true,
                'default' => 'aiassistant.indexer.page',
            ],
        ],
        'adapter_identifier' => [
            'label' => $ll . 'tx_aiassistant_indexer.adapter_identifier',
            'description' => $ll . 'tx_aiassistant_indexer.adapter_identifier_desc',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => \Madj2k\AiAssistant\Indexing\TCA\AdapterItems::class . '->items',
            ],
        ],
        'additional_metadata' => [
            'label' => $ll . 'tx_aiassistant_indexer.additional_metadata',
            'description' => $ll . 'tx_aiassistant_indexer.additional_metadata_desc',
            'config' => [
                'type' => 'text',
                'placeholder' => $ll . 'tx_aiassistant_indexer.additional_metadata_placeholder',
                'rows' => 5,
            ],
        ],
        'ai_connection' => [
            'label' => $ll . 'tx_aiassistant_indexer.ai_connection',
            'description' => $ll . 'tx_aiassistant_indexer.ai_connection_desc',
            'config' => [
                'type' => 'group',
                'allowed' => 'tx_aiassistant_connection_ai',
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ],
        'vector_store_connection' => [
            'label' => $ll . 'tx_aiassistant_indexer.vector_store_connection',
            'description' => $ll . 'tx_aiassistant_indexer.vector_store_connection_desc',
            'config' => [
                'type' => 'group',
                'allowed' => 'tx_aiassistant_connection_vector_database',
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ],
        'collection' => [
            'label' => $ll . 'tx_aiassistant_indexer.collection',
            'description' => $ll . 'tx_aiassistant_indexer.collection_desc',
            'config' => [
                'type' => 'input',
                'placeholder' => $ll . 'tx_aiassistant_indexer.collection_placeholder',
                'default' => 'aiassistant_default',
                'required' => true,
                'eval' => 'trim',
                'max' => 255,
                'size' => 40,
            ],
        ],
        'connector_uid' => [
            'label' => $ll . 'tx_aiassistant_indexer.connector_uid',
            'description' => $ll . 'tx_aiassistant_indexer.connector_uid_desc',
            'config' => [
                'type' => 'group',
                'allowed' => 'tx_aiassistant_indexer_connector',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'chunk_size' => [
            'label' => $ll . 'tx_aiassistant_indexer.chunk_size',
            'description' => $ll . 'tx_aiassistant_indexer.chunk_size_desc',
            'config' => [
                'type' => 'number',
                'placeholder' => $ll . 'tx_aiassistant_indexer.chunk_size_placeholder',
                'default' => 500,
                'format' => 'integer',
            ],
        ],
        'chunk_overlap' => [
            'label' => $ll . 'tx_aiassistant_indexer.chunk_overlap',
            'description' => $ll . 'tx_aiassistant_indexer.chunk_overlap_desc',
            'config' => [
                'type' => 'number',
                'placeholder' => $ll . 'tx_aiassistant_indexer.chunk_overlap_placeholder',
                'default' => 50,
                'format' => 'integer',
            ],
        ],
        'max_chunks' => [
            'label' => $ll . 'tx_aiassistant_indexer.max_chunks',
            'description' => $ll . 'tx_aiassistant_indexer.max_chunks_desc',
            'config' => [
                'type' => 'number',
                'placeholder' => $ll . 'tx_aiassistant_indexer.max_chunks_placeholder',
                'default' => 200,
                'format' => 'integer',
            ],
        ],
        'min_chunk_chars' => [
            'label' => $ll . 'tx_aiassistant_indexer.min_chunk_chars',
            'description' => $ll . 'tx_aiassistant_indexer.min_chunk_chars_desc',
            'config' => [
                'type' => 'number',
                'placeholder' => $ll . 'tx_aiassistant_indexer.min_chunk_chars_placeholder',
                'default' => 40,
                'format' => 'integer',
            ],
        ],
        'import_path' => [
            'label' => $ll . 'tx_aiassistant_indexer.import_path',
            'description' => $ll . 'tx_aiassistant_indexer.import_path_desc',
            'config' => [
                'type' => 'text',
                'required' => true,
                'placeholder' => $ll . 'tx_aiassistant_indexer.import_path_placeholder',
                'rows' => 3,
            ],
        ],
        'include_subfolders' => [
            'label' => $ll . 'tx_aiassistant_indexer.include_subfolders',
            'description' => $ll . 'tx_aiassistant_indexer.include_subfolders_desc',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 0,
            ],
        ],
        'root_pages' => [
            'label' => $ll . 'tx_aiassistant_indexer.root_pages',
            'description' => $ll . 'tx_aiassistant_indexer.root_pages_desc',
            'config' => [
                'type' => 'group',
                'allowed' => 'pages',
                'size' => 5,
                'maxitems' => 999,
            ],
        ],
        'page_fields' => [
            'label' => $ll . 'tx_aiassistant_indexer.page_fields',
            'description' => $ll . 'tx_aiassistant_indexer.page_fields_desc',
            'config' => [
                'type' => 'text',
                'default' => 'title,subtitle,description,keywords,abstract',
                'placeholder' => $ll . 'tx_aiassistant_indexer.page_fields_placeholder',
                'rows' => 4,
            ],
        ],
        'content_types' => [
            'label' => $ll . 'tx_aiassistant_indexer.content_types',
            'description' => $ll . 'tx_aiassistant_indexer.content_types_desc',
            'config' => [
                'type' => 'text',
                'default' => 'text,textpic,textmedia,bullets',
                'placeholder' => $ll . 'tx_aiassistant_indexer.content_types_placeholder',
                'rows' => 3,
            ],
        ],
        'content_fields' => [
            'label' => $ll . 'tx_aiassistant_indexer.content_fields',
            'description' => $ll . 'tx_aiassistant_indexer.content_fields_desc',
            'config' => [
                'type' => 'text',
                'default' => 'header,subheader,bodytext',
                'placeholder' => $ll . 'tx_aiassistant_indexer.content_fields_placeholder',
                'rows' => 4,
            ],
        ],
        'additional_content_fields' => [
            'label' => $ll . 'tx_aiassistant_indexer.additional_content_fields',
            'description' => $ll . 'tx_aiassistant_indexer.additional_content_fields_desc',
            'config' => [
                'type' => 'text',
                'default' => 'tx_news_domain_model_news.title',
                'placeholder' => $ll . 'tx_aiassistant_indexer.additional_content_fields_placeholder',
                'rows' => 4,
            ],
        ]
    ],
];
