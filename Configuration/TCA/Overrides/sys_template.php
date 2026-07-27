<?php
defined('TYPO3') || die();

call_user_func(
    function($extKey)
    {

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            $extKey,
            'Configuration/TypoScript',
            'Ai Chat'
        );

    },
    'ai_assistant'
);
