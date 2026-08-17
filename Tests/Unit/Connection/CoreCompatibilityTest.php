<?php
declare(strict_types=1);

namespace Madj2k\AiAssistant\Tests\Unit\Connection;

use Madj2k\AiAssistant\Assistant\Application\Orchestrator as LegacyOrchestrator;
use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep;
use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile;
use Madj2k\AiAssistant\Assistant\Pipeline\Pipeline as LegacyPipeline;
use Madj2k\AiCore\Assistant\Application\Orchestrator;
use Madj2k\AiCore\Assistant\Configuration\AssistantConfigurationInterface;
use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineFailureStrategy;
use Madj2k\AiCore\Assistant\Pipeline\Pipeline;
use Madj2k\AiAssistant\Connection\Ai\OpenAiConnector as LegacyOpenAiConnector;
use Madj2k\AiAssistant\Connection\Domain\Model\AiConnection;
use Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection;
use Madj2k\AiAssistant\Connection\Registry\AiConnectorRegistry as LegacyAiConnectorRegistry;
use Madj2k\AiAssistant\Indexing\Service\TextChunkerService;
use Madj2k\AiAssistant\Indexing\Adapter\PlainAdapter as LegacyPlainAdapter;
use Madj2k\AiAssistant\Indexing\DTO\IndexableMetadata as LegacyIndexableMetadata;
use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig;
use Madj2k\AiAssistant\Indexing\Indexer\IndexerInterface as LegacyIndexerInterface;
use Madj2k\AiAssistant\Indexing\Registry\IndexerRegistry as LegacyIndexerRegistry;
use Madj2k\AiCore\DTO\DocumentMetadata;
use Madj2k\AiCore\Connection\Ai\OpenAiConnector;
use Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Resolver\AiConnectorResolver;
use Madj2k\AiCore\Indexing\TextChunker;
use Madj2k\AiCore\Indexing\Adapter\PlainAdapter;
use Madj2k\AiCore\Indexing\Configuration\IndexingConfigurationInterface;
use Madj2k\AiCore\Indexing\Indexer\IndexerInterface;
use Madj2k\AiCore\Indexing\Registry\IndexerRegistry;
use PHPUnit\Framework\TestCase;

final class CoreCompatibilityTest extends TestCase
{
    public function testLegacyNamesResolveToCoreClasses(): void
    {
        self::assertInstanceOf(OpenAiConnector::class, new LegacyOpenAiConnector());
        self::assertInstanceOf(
            AiConnectorResolver::class,
            new LegacyAiConnectorRegistry([new LegacyOpenAiConnector()]),
        );
        self::assertInstanceOf(TextChunker::class, new TextChunkerService());
        self::assertInstanceOf(PlainAdapter::class, new LegacyPlainAdapter());
        self::assertInstanceOf(DocumentMetadata::class, new LegacyIndexableMetadata());
    }

    public function testExtbaseModelsImplementCoreConfigurationContracts(): void
    {
        self::assertInstanceOf(AiConnectionConfigurationInterface::class, new AiConnection());
        self::assertInstanceOf(VectorStoreConnectionConfigurationInterface::class, new VectorStoreConnection());
        self::assertInstanceOf(AssistantConfigurationInterface::class, new AssistantProfile());
        self::assertInstanceOf(PipelineStepConfigurationInterface::class, new AssistantPipelineStep());
        self::assertInstanceOf(IndexingConfigurationInterface::class, new IndexerConfig());
    }

    public function testPipelineStepNormalizesLegacyFallbackStrategy(): void
    {
        $pipelineStep = new AssistantPipelineStep();
        self::assertSame(AssistantPipelineFailureStrategy::Continue, $pipelineStep->getFailureStrategy());

        $pipelineStep->setFailureStrategy('fallback');

        self::assertSame(AssistantPipelineFailureStrategy::Continue, $pipelineStep->getFailureStrategy());
        self::assertSame('continue', $pipelineStep->getFailureStrategyValue());
    }

    public function testLegacyAssistantRuntimeNamesResolveToCoreClasses(): void
    {
        self::assertTrue(is_a(LegacyOrchestrator::class, Orchestrator::class, true));
        self::assertTrue(is_a(LegacyPipeline::class, Pipeline::class, true));
        self::assertTrue(is_a(LegacyIndexerInterface::class, IndexerInterface::class, true));
        self::assertTrue(is_a(LegacyIndexerRegistry::class, IndexerRegistry::class, true));
    }
}
