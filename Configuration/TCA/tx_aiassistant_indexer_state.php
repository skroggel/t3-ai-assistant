<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */

$ll = 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_indexer_state.xlf:';

return [
    'ctrl' => [
        'title' => $ll . 'tx_aiassistant_indexer_state',
        'label' => 'indexer_identifier',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'indexer_identifier,source_type,scope,status',
        'iconfile' => 'EXT:ai_assistant/Resources/Public/Icons/Extension.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => 'indexer_identifier, source_type, indexer_uid, scope, cursor_value, status, last_run_started_at, last_run_finished_at, last_error',
        ],
    ],
    'columns' => [
        'indexer_identifier' => [
            'label' => $ll . 'tx_aiassistant_indexer_state.indexer_identifier',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
                'max' => 255,
            ],
        ],
        'source_type' => [
            'label' => $ll . 'tx_aiassistant_indexer_state.source_type',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
                'max' => 50,
            ],
        ],
        'indexer_uid' => [
            'label' => $ll . 'tx_aiassistant_indexer_state.indexer_uid',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
                'format' => 'integer',
            ],
        ],
        'scope' => [
            'label' => $ll . 'tx_aiassistant_indexer_state.scope',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
                'max' => 64,
            ],
        ],
        'cursor_value' => [
            'label' => $ll . 'tx_aiassistant_indexer_state.cursor_value',
            'config' => [
                'type' => 'text',
                'readOnly' => true,
                'rows' => 3,
            ],
        ],
        'status' => [
            'label' => $ll . 'tx_aiassistant_indexer_state.status',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'eval' => 'trim',
                'max' => 16,
            ],
        ],
        'last_run_started_at' => [
            'label' => $ll . 'tx_aiassistant_indexer_state.last_run_started_at',
            'config' => [
                'type' => 'datetime',
                'readOnly' => true,
                'format' => 'datetime',
            ],
        ],
        'last_run_finished_at' => [
            'label' => $ll . 'tx_aiassistant_indexer_state.last_run_finished_at',
            'config' => [
                'type' => 'datetime',
                'readOnly' => true,
                'format' => 'datetime',
            ],
        ],
        'last_error' => [
            'label' => $ll . 'tx_aiassistant_indexer_state.last_error',
            'config' => [
                'type' => 'text',
                'readOnly' => true,
                'rows' => 5,
            ],
        ],
    ],
];
