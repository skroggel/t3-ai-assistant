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
 */

namespace Madj2k\AiAssistant\Indexing\Enum;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Enum IndexerType
 *
 * Defines the available indexer configuration types used by TCA.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
enum IndexerType: string
{
    case File = 'file';
    case Page = 'page';
    case External = 'external';
    case Shopware = 'shopware';

    /**
     * Returns TCA select items.
     *
     * @return array<int, array<string, string>> TCA select items.
     */
    public static function getTcaItems(): array
    {
        $items = [
            [
                'label' => 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_indexer.xlf:tx_aiassistant_indexer.type.file',
                'value' => self::File->value,
            ],
            [
                'label' => 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_indexer.xlf:tx_aiassistant_indexer.type.page',
                'value' => self::Page->value,
            ],
            [
                'label' => 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_indexer.xlf:tx_aiassistant_indexer.type.external',
                'value' => self::External->value,
            ],
        ];

        if (ExtensionManagementUtility::isLoaded('ai_assistant_premium')) {
            $items[] = [
                'label' => 'LLL:EXT:ai_assistant_premium/Resources/Private/Language/locallang_tx_aiassistant_indexer.xlf:tx_aiassistant_indexer.type.shopware',
                'value' => self::Shopware->value,
            ];
        }

        return $items;
    }


    /**
     * Returns TCA type configuration.
     *
     * @return array<string, array<string, string>> TCA type configuration.
     */
    public static function getTcaTypes(): array
    {
        $types = [
            self::File->value => [
                'showitem' => '--palette--;;base, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_indexer.xlf:tx_aiassistant_indexer.tab_connections, --palette--;;connections, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_indexer.xlf:tx_aiassistant_indexer.tab_chunking, --palette--;;chunking, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_indexer.xlf:tx_aiassistant_indexer.tab_file, --palette--;;file, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_indexer.xlf:tx_aiassistant_indexer.tab_metadata, additional_metadata',
            ],
            self::Page->value => [
                'showitem' => '--palette--;;base, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_indexer.xlf:tx_aiassistant_indexer.tab_connections, --palette--;;connections, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_indexer.xlf:tx_aiassistant_indexer.tab_chunking, --palette--;;chunking, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_indexer.xlf:tx_aiassistant_indexer.tab_page, --palette--;;page, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_indexer.xlf:tx_aiassistant_indexer.tab_metadata, additional_metadata',
            ],
            self::External->value => [
                'showitem' => '--palette--;;base, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_indexer.xlf:tx_aiassistant_indexer.tab_connections, --palette--;;connections, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_indexer.xlf:tx_aiassistant_indexer.tab_external, --palette--;;external, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_indexer.xlf:tx_aiassistant_indexer.tab_chunking, --palette--;;chunking, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_indexer.xlf:tx_aiassistant_indexer.tab_metadata, additional_metadata',
            ],
        ];

        if (ExtensionManagementUtility::isLoaded('ai_assistant_premium')) {
            $types[self::Shopware->value] = [
                'showitem' => '--palette--;;base, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_indexer.xlf:tx_aiassistant_indexer.tab_connections, --palette--;;connections, --div--;LLL:EXT:ai_assistant_premium/Resources/Private/Language/locallang_tx_aiassistant_indexer.xlf:tx_aiassistant_indexer.tab_shopware, --palette--;;shopware, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_indexer.xlf:tx_aiassistant_indexer.tab_chunking, --palette--;;chunking, --div--;LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_indexer.xlf:tx_aiassistant_indexer.tab_metadata, additional_metadata',
            ];
        }

        return $types;
    }
}
