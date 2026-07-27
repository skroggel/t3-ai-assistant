<?php

use Madj2k\AiAssistant\Controller\ChatController;
use Madj2k\AiAssistant\Controller\IndexController;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\Writer\FileWriter;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die();

(static function() {
    $GLOBALS['TYPO3_CONF_VARS']['LOG']['Madj2k']['AiAssistant']['writerConfiguration'] = [
        LogLevel::INFO => [
            FileWriter::class => [
                'disabled' => false,
                'logFile' => Environment::getVarPath() . '/log/tx_aiassistant.log',
            ],
        ],
    ];

    ExtensionUtility::configurePlugin(
        'AiAssistant',
        'Chat',
        [
            IndexController::class => 'index',
            ChatController::class => 'stream'
        ],
        // non-cacheable actions
        [
            IndexController::class => 'index',
            ChatController::class => 'stream'
        ],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );
})();
