<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Ai Assistant',
    'description' => 'AI Assistant is a flexible TYPO3 extension for building AI-powered assistants based on Retrieval-Augmented Generation (RAG), configurable processing pipelines, and pluggable AI and vector store integrations.',
    'category' => 'plugin',
    'author' => 'Steffen Kroggel, Maximilian Fäßler',
    'author_email' => 'developer@steffenkroggel.de, maximilian@faesslerweb.de',
    'state' => 'alpha',
    'clearCacheOnLoad' => 0,
    'version' => '14.3.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-14.3.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
