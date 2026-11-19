<?php
declare(strict_types=1);

$ll = 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_glossary.xlf:';

return [
    'ctrl' => [
        'title' => $ll . 'tx_aiassistant_glossary',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'enablecolumns' => ['disabled' => 'hidden'],
        'sortby' => 'sorting',
        'rootLevel' => -1,
        'searchFields' => 'title,content',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'translationSource' => 'l10n_source',
        'iconfile' => 'EXT:ai_assistant/Resources/Public/Icons/Extension.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => 'title, identifier, content, --div--;LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language, sys_language_uid, l10n_parent',
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
        'content' => [
            'label' => $ll . 'content',
            'description' => $ll . 'content.description',
            'config' => ['type' => 'text', 'required' => true, 'rows' => 30],
        ],
        'sys_language_uid' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => ['type' => 'language'],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_aiassistant_glossary',
                'foreign_table_where' => 'AND {#tx_aiassistant_glossary}.{#sys_language_uid}=0 ORDER BY tx_aiassistant_glossary.title',
                'items' => [['label' => '', 'value' => 0]],
            ],
        ],
        'l10n_source' => ['config' => ['type' => 'passthrough']],
        'l10n_diffsource' => ['config' => ['type' => 'passthrough']],
    ],
];
