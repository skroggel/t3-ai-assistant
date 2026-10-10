<?php
declare(strict_types=1);

$ll = 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_ui_component.xlf:';

return [
    'ctrl' => [
        'title' => $ll . 'tx_aiassistant_ui_component',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'enablecolumns' => ['disabled' => 'hidden'],
        'sortby' => 'sorting',
        'rootLevel' => -1,
        'searchFields' => 'title,identifier,description',
        'iconfile' => 'EXT:ai_assistant/Resources/Public/Icons/Extension.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => '
                title, identifier, preset, css_class, button_actions,
                --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_ui_component.xlf:definition_tab,
                    description, template, actions, data_schema, placeholders,
                --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_ui_component.xlf:override_tab,
                    override_description, override_template, override_actions, override_data_schema, override_placeholders
            ',
        ],
    ],
    'columns' => [
        'title' => [
            'label' => $ll . 'title',
            'config' => ['type' => 'input', 'required' => true, 'eval' => 'trim', 'max' => 255],
        ],
        'identifier' => [
            'label' => $ll . 'identifier',
            'description' => $ll . 'identifier.description',
            'config' => ['type' => 'input', 'required' => true, 'eval' => 'trim,lower', 'max' => 128],
        ],
        'preset' => [
            'label' => $ll . 'preset',
            'description' => $ll . 'preset.description',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'required' => true,
                'items' => [
                    ['label' => $ll . 'preset.select_list', 'value' => 'select-list'],
                    ['label' => $ll . 'preset.link_list', 'value' => 'link-list'],
                    ['label' => $ll . 'preset.link', 'value' => 'link'],
                    ['label' => $ll . 'preset.buttons', 'value' => 'buttons'],
                    ['label' => $ll . 'preset.progress_indicator', 'value' => 'progress-indicator'],
                    ['label' => $ll . 'preset.decorative', 'value' => 'decorative'],
                    ['label' => $ll . 'preset.headline', 'value' => 'headline'],
                    ['label' => $ll . 'preset.custom', 'value' => 'custom'],
                ],
                'default' => 'custom',
            ],
        ],
        'css_class' => [
            'label' => $ll . 'css_class',
            'description' => $ll . 'css_class.description',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'max' => 255,
                'placeholder' => 'my-chat-choice-list',
            ],
        ],
        'description' => [
            'label' => $ll . 'description',
            'displayCond' => 'FIELD:preset:=:custom',
            'config' => [
                'type' => 'text',
                'rows' => 5
            ],
        ],
        'template' => [
            'label' => $ll . 'template',
            'description' => $ll . 'template.description',
            'displayCond' => 'FIELD:preset:=:custom',
            'config' => [
                'type' => 'text',
                'required' => true,
                'rows' => 10,
            ],
        ],
        'actions' => [
            'label' => $ll . 'actions',
            'description' => $ll . 'actions.description',
            'displayCond' => 'FIELD:preset:=:custom',
            'config' => [
                'type' => 'text',
                'rows' => 10,
            ],
        ],
        'button_actions' => [
            'label' => $ll . 'button_actions',
            'description' => $ll . 'button_actions.description',
            'displayCond' => 'FIELD:preset:=:buttons',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_aiassistant_ui_component_button',
                'foreign_field' => 'ui_component',
                'foreign_sortby' => 'sorting',
                'appearance' => [
                    'collapseAll' => true,
                    'expandSingle' => true,
                    'useSortable' => true,
                ],
            ],
        ],
        'override_description' => [
            'label' => $ll . 'override_description',
            'description' => $ll . 'override_description.description',
            'displayCond' => 'FIELD:preset:!=:custom',
            'config' => ['type' => 'text', 'rows' => 5],
        ],
        'override_template' => [
            'label' => $ll . 'override_template',
            'description' => $ll . 'override_template.description',
            'displayCond' => 'FIELD:preset:!=:custom',
            'config' => [
                'type' => 'text',
                'rows' => 10,
            ],
        ],
        'override_actions' => [
            'label' => $ll . 'override_actions',
            'description' => $ll . 'override_actions.description',
            'displayCond' => 'FIELD:preset:!=:custom',
            'config' => [
                'type' => 'text',
                'rows' => 10,
            ],
        ],
        'override_data_schema' => [
            'label' => $ll . 'override_data_schema',
            'description' => $ll . 'override_data_schema.description',
            'displayCond' => 'FIELD:preset:!=:custom',
            'config' => [
                'type' => 'text',
                'rows' => 10
            ],
        ],
        'override_placeholders' => [
            'label' => $ll . 'override_placeholders',
            'description' => $ll . 'override_placeholders.description',
            'displayCond' => 'FIELD:preset:!=:custom',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'max' => 2048
            ],
        ],
        'data_schema' => [
            'label' => $ll . 'data_schema',
            'description' => $ll . 'data_schema.description',
            'displayCond' => 'FIELD:preset:=:custom',
            'config' => [
                'type' => 'text',
                'rows' => 10,
            ],
        ],
        'placeholders' => [
            'label' => $ll . 'placeholders',
            'description' => $ll . 'placeholders.description',
            'displayCond' => 'FIELD:preset:=:custom',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'max' => 2048,
            ],
        ],
    ],
];
