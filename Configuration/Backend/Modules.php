<?php

declare(strict_types=1);

use Madj2k\AiAssistant\Controller\BackendController;

return [
    'web_aiassistant' => [
        'parent' => 'web',
        'position' => [],
        'access' => 'user,group',
        'workspaces' => 'live',
        'iconIdentifier' => 'aiassistant-module',
        'labels' => 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_config.xlf',
        'extensionName' => 'AiAssistant',
        'controllerActions' => [
            BackendController::class => [
                'configuration',
                'diagnostics',
                'indexerStatus',
                'purge',
                'logs',
            ],
        ],
    ],
];
