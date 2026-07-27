<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Ai Chat',
    'description' => 'AI Assistant for TYPO3: RAG-based AI chat, semantic search, configurable assistant pipelines, vector store retrieval, content indexing and extensible AI integrations.',
    'category' => 'plugin',
    'author' => 'Maximilian Fäßler',
    'author_email' => 'maximilian@faesslerweb.de',
    'state' => 'alpha',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
