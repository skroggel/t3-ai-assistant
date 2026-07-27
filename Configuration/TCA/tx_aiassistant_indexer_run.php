<?php
declare(strict_types=1);

$ll = 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_indexer_run.xlf:';

return [
    'ctrl' => [
        'title' => $ll . 'tx_aiassistant_indexer_run',
        'label' => 'source_type',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'source_type,status',
        'iconfile' => 'EXT:ai_assistant/Resources/Public/Icons/Extension.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => 'source_type, indexer_uid, status, is_dry_run, started_at, finished_at, items_processed, items_indexed, items_skipped, items_failed, items_removed, chunks_total, message',
        ]
    ],
    'columns' => [
        'source_type' => [
            'label' => $ll . 'tx_aiassistant_indexer_run.source_type',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
                'max' => 50,
            ],
        ],
        'indexer_uid' => [
            'label' => $ll . 'tx_aiassistant_indexer_run.indexer_uid',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
                'format' => 'integer',
            ],
        ],
        'status' => [
            'label' => $ll . 'tx_aiassistant_indexer_run.status',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
                'max' => 16,
            ],
        ],
        'is_dry_run' => [
            'label' => $ll . 'tx_aiassistant_indexer_run.is_dry_run',
            'config' => [
                'type' => 'check',
                'readOnly' => true,
                'renderType' => 'checkboxToggle',
            ],
        ],
        'started_at' => [
            'label' => $ll . 'tx_aiassistant_indexer_run.started_at',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
                'format' => 'integer',
            ],
        ],
        'finished_at' => [
            'label' => $ll . 'tx_aiassistant_indexer_run.finished_at',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
                'format' => 'integer',
            ],
        ],
        'items_processed' => [
            'label' => $ll . 'tx_aiassistant_indexer_run.items_processed',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
                'format' => 'integer',
            ],
        ],
        'items_indexed' => [
            'label' => $ll . 'tx_aiassistant_indexer_run.items_indexed',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
                'format' => 'integer',
            ],
        ],
        'items_skipped' => [
            'label' => $ll . 'tx_aiassistant_indexer_run.items_skipped',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
                'format' => 'integer',
            ],
        ],
        'items_failed' => [
            'label' => $ll . 'tx_aiassistant_indexer_run.items_failed',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
                'format' => 'integer',
            ],
        ],
        'items_removed' => [
            'label' => $ll . 'tx_aiassistant_indexer_run.items_removed',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
                'format' => 'integer',
            ],
        ],
        'chunks_total' => [
            'label' => $ll . 'tx_aiassistant_indexer_run.chunks_total',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
                'format' => 'integer',
            ],
        ],
        'message' => [
            'label' => $ll . 'tx_aiassistant_indexer_run.message',
            'config' => [
                'type' => 'text',
                'readOnly' => true,
                'rows' => 5,
            ],
        ]
    ],
];
