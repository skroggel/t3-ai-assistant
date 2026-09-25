<?php
declare(strict_types=1);

use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile;
use Madj2k\AiAssistant\Assistant\Domain\Model\PipelineTrace;
use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep;
use Madj2k\AiAssistant\Connection\Domain\Model\AiConnection;
use Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection;
use Madj2k\AiAssistant\Connection\Domain\Model\McpConnection;
use Madj2k\AiAssistant\Indexing\Domain\Model\ConnectorConfig;
use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig;
use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerRun;
use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerState;
use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerSource;

return [
    AiConnection::class => [
        'tableName' => 'tx_aiassistant_connection_ai',
    ],
    VectorStoreConnection::class => [
        'tableName' => 'tx_aiassistant_connection_vector_database',
    ],
    McpConnection::class => [
        'tableName' => 'tx_aiassistant_connection_mcp',
    ],
    AssistantPipelineStep::class => [
        'tableName' => 'tx_aiassistant_assistant_pipeline_step',
    ],
    AssistantProfile::class => [
        'tableName' => 'tx_aiassistant_assistant_profile',
    ],
    ConnectorConfig::class => [
        'tableName' => 'tx_aiassistant_indexer_connector',
    ],
    IndexerConfig::class => [
        'tableName' => 'tx_aiassistant_indexer',
    ],
    IndexerRun::class => [
        'tableName' => 'tx_aiassistant_indexer_run',
    ],
    IndexerState::class => [
        'tableName' => 'tx_aiassistant_indexer_state',
    ],
    IndexerSource::class => [
        'tableName' => 'tx_aiassistant_indexer_source',
    ],
    PipelineTrace::class => [
        'tableName' => 'tx_aiassistant_pipeline_trace',
    ],
];
