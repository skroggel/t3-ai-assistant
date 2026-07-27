<?php
declare(strict_types=1);

$ll = 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_pipeline_trace.xlf:';

return [
    'ctrl' => [
        'title' => $ll . 'tx_aiassistant_pipeline_trace',
        'label' => 'event_name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'level,event_name,route,chat_identifier,trace_id,processor_type,query_text',
        'iconfile' => 'EXT:ai_assistant/Resources/Public/Icons/Extension.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => 'level, event_name, route, chat_identifier, trace_id, step_title, processor_type, duration_ms, query_text, payload',
        ]
    ],
    'columns' => [
        'level' => [
            'label' => $ll . 'tx_aiassistant_pipeline_trace.level',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
                'max' => 16,
            ],
        ],
        'event_name' => [
            'label' => $ll . 'tx_aiassistant_pipeline_trace.event_name',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
                'max' => 64,
            ],
        ],
        'route' => [
            'label' => $ll . 'tx_aiassistant_pipeline_trace.route',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
                'max' => 32,
            ],
        ],
        'chat_identifier' => [
            'label' => $ll . 'tx_aiassistant_pipeline_trace.chat_identifier',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
                'max' => 191,
            ],
        ],
        'trace_id' => [
            'label' => $ll . 'tx_aiassistant_pipeline_trace.trace_id',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
                'max' => 64,
            ],
        ],
        'step_title' => [
            'label' => $ll . 'tx_aiassistant_pipeline_trace.step_title',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
                'max' => 255,
            ],
        ],
        'processor_type' => [
            'label' => $ll . 'tx_aiassistant_pipeline_trace.processor_type',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
                'max' => 64,
            ],
        ],
        'duration_ms' => [
            'label' => $ll . 'tx_aiassistant_pipeline_trace.duration_ms',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
                'format' => 'integer',
            ],
        ],
        'query_text' => [
            'label' => $ll . 'tx_aiassistant_pipeline_trace.query_text',
            'config' => [
                'type' => 'text',
                'readOnly' => true,
                'rows' => 3,
            ],
        ],
        'payload' => [
            'label' => $ll . 'tx_aiassistant_pipeline_trace.payload',
            'config' => [
                'type' => 'text',
                'readOnly' => true,
                'rows' => 12,
            ],
        ]
    ],
];
