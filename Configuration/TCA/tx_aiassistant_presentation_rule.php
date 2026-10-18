<?php
declare(strict_types=1);

$ll = 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_presentation_rule.xlf:';

return [
    'ctrl' => [
        'title' => $ll . 'tx_aiassistant_presentation_rule', 'label' => 'title',
        'tstamp' => 'tstamp', 'crdate' => 'crdate', 'cruser_id' => 'cruser_id',
        'delete' => 'deleted', 'enablecolumns' => ['disabled' => 'hidden'],
        'sortby' => 'sorting', 'rootLevel' => -1, 'searchFields' => 'title,identifier,content',
        'iconfile' => 'EXT:ai_assistant/Resources/Public/Icons/Extension.svg',
    ],
    'types' => ['1' => ['showitem' => 'title, identifier, content']],
    'columns' => [
        'title' => ['label' => $ll . 'title', 'config' => ['type' => 'input', 'required' => true, 'eval' => 'trim', 'max' => 255]],
        'identifier' => ['label' => $ll . 'identifier', 'description' => $ll . 'identifier.description', 'config' => ['type' => 'input', 'required' => true, 'eval' => 'trim,lower', 'max' => 128]],
        'content' => ['label' => $ll . 'content', 'config' => ['type' => 'text', 'required' => true, 'rows' => 15]],
    ],
];
