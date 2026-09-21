<?php
declare(strict_types=1);

$ll = 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_indexer_source.xlf:';

return [
    'ctrl' => [
        'title' => $ll . 'tx_aiassistant_indexer_source',
        'label' => 'source_id',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'source_type,source_id,source_hash,storage_source_hash,filename,path,content_checksum,collection,vector_store_connection',
        'iconfile' => 'EXT:ai_assistant/Resources/Public/Icons/Extension.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => 'source_type, indexer_uid, vector_store_connection, source_id, source_hash, language, language_id, collection, page_id, path, filename, content_checksum, storage_source_hash, file_mtime, file_ctime, last_changed, last_indexed, status, locked_until, lock_token, last_error',
        ]
    ],
    'columns' => [
        'source_type' => [
            'label' => $ll . 'tx_aiassistant_indexer_source.source_type',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
                'max' => 50,
            ],
        ],
        'indexer_uid' => [
            'label' => $ll . 'tx_aiassistant_indexer_source.indexer_uid',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
                'format' => 'integer',
            ],
        ],
        'source_id' => [
            'label' => $ll . 'tx_aiassistant_indexer_source.source_id',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
                'max' => 1024,
            ],
        ],
        'source_hash' => [
            'label' => $ll . 'tx_aiassistant_indexer_source.source_hash',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
                'max' => 64,
            ],
        ],
        'language' => [
            'label' => $ll . 'tx_aiassistant_indexer_source.language',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
                'max' => 16,
            ],
        ],
        'language_id' => [
            'label' => $ll . 'tx_aiassistant_indexer_source.language_id',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
                'format' => 'integer',
            ],
        ],
        'collection' => [
            'label' => $ll . 'tx_aiassistant_indexer_source.collection',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
                'max' => 255,
            ],
        ],
        'vector_store_connection' => [
            'label' => $ll . 'tx_aiassistant_indexer_source.vector_store_connection',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
                'format' => 'integer',
            ],
        ],
        'page_id' => [
            'label' => $ll . 'tx_aiassistant_indexer_source.page_id',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
                'format' => 'integer',
            ],
        ],
        'path' => [
            'label' => $ll . 'tx_aiassistant_indexer_source.path',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
                'max' => 1024,
            ],
        ],
        'filename' => [
            'label' => $ll . 'tx_aiassistant_indexer_source.filename',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
                'max' => 255,
            ],
        ],
        'content_checksum' => [
            'label' => $ll . 'tx_aiassistant_indexer_source.content_checksum',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
                'max' => 64,
            ],
        ],
        'storage_source_hash' => [
            'label' => $ll . 'tx_aiassistant_indexer_source.storage_source_hash',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
                'max' => 64,
            ],
        ],
        'file_mtime' => [
            'label' => $ll . 'tx_aiassistant_indexer_source.file_mtime',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
                'format' => 'integer',
            ],
        ],
        'file_ctime' => [
            'label' => $ll . 'tx_aiassistant_indexer_source.file_ctime',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
                'format' => 'integer',
            ],
        ],
        'last_indexed' => [
            'label' => $ll . 'tx_aiassistant_indexer_source.last_indexed',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
                'format' => 'integer',
            ],
        ],
        'last_changed' => [
            'label' => $ll . 'tx_aiassistant_indexer_source.last_changed',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
                'format' => 'integer',
            ],
        ],
        'status' => [
            'label' => $ll . 'tx_aiassistant_indexer_source.status',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
                'max' => 16,
            ],
        ],
        'locked_until' => [
            'label' => $ll . 'tx_aiassistant_indexer_source.locked_until',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
                'format' => 'integer',
            ],
        ],
        'lock_token' => [
            'label' => $ll . 'tx_aiassistant_indexer_source.lock_token',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
                'max' => 64,
            ],
        ],
        'last_error' => [
            'label' => $ll . 'tx_aiassistant_indexer_source.last_error',
            'config' => [
                'type' => 'text',
                'readOnly' => true,
                'rows' => 5,
            ],
        ]
    ],
];
