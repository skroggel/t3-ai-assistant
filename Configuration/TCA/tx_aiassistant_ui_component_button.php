<?php
declare(strict_types=1);

$ll = 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_ui_component.xlf:';

return [
    'ctrl' => [
        'title' => $ll . 'button',
        'label' => 'label',
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
        '1' => [
            'showitem' => 'label, type, prompt_template, url, placeholders',
        ],
    ],
    'columns' => [
        'label' => [
            'label' => $ll . 'button.label',
            'config' => ['type' => 'input', 'required' => true, 'eval' => 'trim', 'max' => 255],
        ],
        'type' => [
            'label' => $ll . 'button.type',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => $ll . 'button.type.prompt', 'value' => 'prompt'],
                    ['label' => $ll . 'button.type.link', 'value' => 'link'],
                ],
                'default' => 'prompt',
            ],
        ],
        'prompt_template' => [
            'label' => $ll . 'button.prompt_template',
            'description' => $ll . 'button.prompt_template.description',
            'displayCond' => 'FIELD:type:=:prompt',
            'config' => [
                'type' => 'text',
                'rows' => 4,
                'placeholder' => 'Diesen Schritt überspringen oder zeige mir {{value}}.',
            ],
        ],
        'url' => [
            'label' => $ll . 'button.url',
            'displayCond' => 'FIELD:type:=:link',
            'config' => ['type' => 'input', 'eval' => 'trim', 'max' => 2048],
        ],
        'placeholders' => [
            'label' => $ll . 'button.placeholders',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'max' => 2048,
                'placeholder' => 'value, label, context.currentStep',
            ],
        ],
    ],
];
