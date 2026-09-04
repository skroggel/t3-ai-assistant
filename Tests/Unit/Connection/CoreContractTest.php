<?php
declare(strict_types=1);

namespace Madj2k\AiAssistant\Tests\Unit\Connection;

use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep;
use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile;
use Madj2k\AiAssistant\Connection\Domain\Model\AiConnection;
use Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection;
use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig;
use Madj2k\AiCore\Assistant\Configuration\AssistantConfigurationInterface;
use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineFailureStrategy;
use Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface;
use Madj2k\AiCore\Indexing\Configuration\IndexingConfigurationInterface;
use PHPUnit\Framework\TestCase;

final class CoreContractTest extends TestCase
{
    public function testExtbaseModelsImplementCoreConfigurationContracts(): void
    {
        $aiConnection = new AiConnection();

        self::assertInstanceOf(AiConnectionConfigurationInterface::class, $aiConnection);
        self::assertSame('', $aiConnection->getBaseUrl());
        self::assertSame('', $aiConnection->getDefaultModel());
        self::assertSame('', $aiConnection->getEmbeddingModel());
        self::assertSame(1536, $aiConnection->getEmbeddingDimension());
        self::assertInstanceOf(VectorStoreConnectionConfigurationInterface::class, new VectorStoreConnection());
        self::assertInstanceOf(AssistantConfigurationInterface::class, new AssistantProfile());
        self::assertInstanceOf(PipelineStepConfigurationInterface::class, new AssistantPipelineStep());
        self::assertInstanceOf(IndexingConfigurationInterface::class, new IndexerConfig());
    }

    public function testVectorStoreConnectionNormalizesAllowedCollections(): void
    {
        $connection = new VectorStoreConnection();
        $connection->setDefaultCollection('public');
        $connection->setCollections("products\npublic, archive");

        self::assertSame(['products', 'public', 'archive'], $connection->getCollectionList());
    }

    public function testPipelineStepExposesVectorStoreConnectionOverride(): void
    {
        $connection = new VectorStoreConnection();
        $pipelineStep = new AssistantPipelineStep();

        self::assertNull($pipelineStep->getRetrievalVectorStoreConnection());

        $pipelineStep->setRetrievalVectorStoreConnection($connection);

        self::assertSame($connection, $pipelineStep->getRetrievalVectorStoreConnection());
    }

    public function testPipelineStepNormalizesLegacyFallbackStrategy(): void
    {
        $pipelineStep = new AssistantPipelineStep();
        self::assertSame(AssistantPipelineFailureStrategy::Continue, $pipelineStep->getFailureStrategy());

        $pipelineStep->setFailureStrategy('fallback');

        self::assertSame(AssistantPipelineFailureStrategy::Continue, $pipelineStep->getFailureStrategy());
        self::assertSame('continue', $pipelineStep->getFailureStrategyValue());
    }
}
