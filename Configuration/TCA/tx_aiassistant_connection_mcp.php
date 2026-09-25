<?php
declare(strict_types=1);

$ll = 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_connection_mcp.xlf:';

return [
    'ctrl' => [
        'title' => $ll . 'tx_aiassistant_connection_mcp',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'enablecolumns' => ['disabled' => 'hidden'],
        'sortby' => 'sorting',
        'rootLevel' => -1,
        'searchFields' => 'title,identifier,transport,endpoint,allowed_tools',
        'iconfile' => 'EXT:ai_assistant/Resources/Public/Icons/Extension.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => 'title, identifier, transport, endpoint, authentication, bearer_token, oauth_token_endpoint, oauth_client_id, oauth_client_secret, oauth_scope, timeout, max_rounds, allowed_tools',
        ],
    ],
    'columns' => [
        'title' => [
            'label' => $ll . 'tx_aiassistant_connection_mcp.title',
            'config' => ['type' => 'input', 'required' => true, 'eval' => 'trim', 'max' => 255],
        ],
        'identifier' => [
            'label' => $ll . 'tx_aiassistant_connection_mcp.identifier',
            'config' => ['type' => 'input', 'required' => true, 'eval' => 'trim', 'max' => 100],
        ],
        'transport' => [
            'label' => $ll . 'tx_aiassistant_connection_mcp.transport',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [['label' => $ll . 'tx_aiassistant_connection_mcp.transport.streamable_http', 'value' => 'streamable_http']],
                'default' => 'streamable_http',
            ],
        ],
        'endpoint' => [
            'label' => $ll . 'tx_aiassistant_connection_mcp.endpoint',
            'config' => ['type' => 'input', 'required' => true, 'eval' => 'trim', 'max' => 2048, 'size' => 60],
        ],
        'bearer_token' => [
            'label' => $ll . 'tx_aiassistant_connection_mcp.bearer_token',
            'config' => ['type' => 'password', 'eval' => 'trim', 'max' => 4096, 'hashed' => false],
        ],
        'authentication' => [
            'label' => $ll . 'tx_aiassistant_connection_mcp.authentication',
            'description' => $ll . 'tx_aiassistant_connection_mcp.authentication.description',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => $ll . 'tx_aiassistant_connection_mcp.authentication.none', 'value' => 'none'],
                    ['label' => $ll . 'tx_aiassistant_connection_mcp.authentication.bearer', 'value' => 'bearer'],
                    ['label' => $ll . 'tx_aiassistant_connection_mcp.authentication.oauth_client_credentials', 'value' => 'oauth_client_credentials'],
                ],
                'default' => 'none',
            ],
        ],
        'oauth_token_endpoint' => [
            'label' => $ll . 'tx_aiassistant_connection_mcp.oauth_token_endpoint',
            'description' => $ll . 'tx_aiassistant_connection_mcp.oauth_token_endpoint.description',
            'config' => ['type' => 'input', 'eval' => 'trim', 'max' => 2048, 'size' => 60],
        ],
        'oauth_client_id' => [
            'label' => $ll . 'tx_aiassistant_connection_mcp.oauth_client_id',
            'description' => $ll . 'tx_aiassistant_connection_mcp.oauth_client_id.description',
            'config' => ['type' => 'input', 'eval' => 'trim', 'max' => 255],
        ],
        'oauth_client_secret' => [
            'label' => $ll . 'tx_aiassistant_connection_mcp.oauth_client_secret',
            'description' => $ll . 'tx_aiassistant_connection_mcp.oauth_client_secret.description',
            'config' => ['type' => 'password', 'eval' => 'trim', 'max' => 4096, 'hashed' => false],
        ],
        'oauth_scope' => [
            'label' => $ll . 'tx_aiassistant_connection_mcp.oauth_scope',
            'description' => $ll . 'tx_aiassistant_connection_mcp.oauth_scope.description',
            'config' => ['type' => 'input', 'eval' => 'trim', 'max' => 2048],
        ],
        'timeout' => [
            'label' => $ll . 'tx_aiassistant_connection_mcp.timeout',
            'config' => ['type' => 'number', 'format' => 'integer', 'default' => 10, 'range' => ['lower' => 1, 'upper' => 120]],
        ],
        'max_rounds' => [
            'label' => $ll . 'tx_aiassistant_connection_mcp.max_rounds',
            'description' => $ll . 'tx_aiassistant_connection_mcp.max_rounds.description',
            'config' => ['type' => 'number', 'format' => 'integer', 'default' => 10, 'range' => ['lower' => 1, 'upper' => 100]],
        ],
        'allowed_tools' => [
            'label' => $ll . 'tx_aiassistant_connection_mcp.allowed_tools',
            'description' => $ll . 'tx_aiassistant_connection_mcp.allowed_tools.description',
            'config' => ['type' => 'text', 'rows' => 4, 'eval' => 'trim'],
        ],
    ],
];
