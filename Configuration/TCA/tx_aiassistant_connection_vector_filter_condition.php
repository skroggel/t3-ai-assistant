<?php
declare(strict_types=1);

$ll = 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_connection_vector_filter_condition.xlf:';

return [
    'ctrl' => [
        'title' => $ll . 'tx_aiassistant_connection_vector_filter_condition',
        'label' => 'field',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'sortby' => 'sorting',
        'rootLevel' => -1,
        'hideTable' => true,
        'iconfile' => 'EXT:ai_assistant/Resources/Public/Icons/Extension.svg',
    ],
    'types' => [
        '1' => ['showitem' => 'field, operator, value'],
    ],
    'columns' => [
        'field' => [
            'label' => $ll . 'tx_aiassistant_connection_vector_filter_condition.field',
            'config' => [
                'type' => 'input',
                'required' => true,
                'eval' => 'trim',
                'max' => 255,
                'placeholder' => 'meta.document_type',
            ],
        ],
        'operator' => [
            'label' => $ll . 'tx_aiassistant_connection_vector_filter_condition.operator',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => 'Equals', 'value' => 'equals'],
                    ['label' => 'In list', 'value' => 'in'],
                    ['label' => 'Exists', 'value' => 'exists'],
                ],
                'default' => 'equals',
            ],
        ],
        'value' => [
            'label' => $ll . 'tx_aiassistant_connection_vector_filter_condition.value',
            'description' => $ll . 'tx_aiassistant_connection_vector_filter_condition.value.description',
            'config' => [
                'type' => 'text',
                'rows' => 2,
                'placeholder' => 'faq oder faq,guide',
            ],
        ],
    ],
];
