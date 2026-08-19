<?php
declare(strict_types=1);

namespace Madj2k\AiAssistant\Tests\Unit\Backend\Diagnostics;

use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep;
use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile;
use Madj2k\AiAssistant\Backend\Diagnostics\BackendAssistantTester;
use Madj2k\AiAssistant\Connection\Domain\Model\AiConnection;
use Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineFailureStrategy;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineStage;
use Madj2k\AiCore\Assistant\Log\PipelineLoggerInterface;
use Madj2k\AiCore\Assistant\Pipeline\PipelineValidator;
use Madj2k\AiCore\Assistant\Pipeline\Processor\ProcessorInterface;
use Madj2k\AiCore\Assistant\Pipeline\Processor\Retrieval\RetrieverProcessor;
use Madj2k\AiCore\Assistant\Pipeline\Registry\ProcessorRegistry;
use Madj2k\AiCore\Connection\Ai\AiConnectorInterface;
use Madj2k\AiCore\Connection\Ai\DTO\EmbeddingResponse;
use Madj2k\AiCore\Connection\Health\ConnectionHealthChecker;
use Madj2k\AiCore\Connection\Resolver\AiConnectorResolver;
use Madj2k\AiCore\Connection\Resolver\VectorStoreConnectorResolver;
use Madj2k\AiCore\Connection\VectorStore\VectorStoreConnectorInterface;
use PHPUnit\Framework\TestCase;

final class BackendAssistantTesterTest extends TestCase
{
    public function testValidatesEffectiveCollectionAgainstRemoteVectorStore(): void
    {
        $result = $this->createTester(['public'])->test($this->createAssistant('public'));

        self::assertSame('ok', $result['status']);
        self::assertSame('Assistant diagnostics succeeded.', $result['message']);
        self::assertStringContainsString('Collection "public" exists', $result['checks'][2]['message']);
    }


    public function testReportsConfiguredCollectionMissingFromRemoteVectorStore(): void
    {
        $result = $this->createTester(['archive'])->test($this->createAssistant('public'));

        self::assertSame('error', $result['status']);
        self::assertStringContainsString('Collection "public" does not exist', $result['checks'][2]['message']);
    }


    public function testRejectsCollectionOutsideEffectiveConnectionConfiguration(): void
    {
        $assistant = $this->createAssistant('public');
        $this->getRetriever($assistant)->setRetrievalCollection('missing');

        $result = $this->createTester(['public', 'missing'])->test($assistant);

        self::assertSame('error', $result['status']);
        self::assertStringContainsString(
            'Collection "missing" is not configured for the effective vector store connection',
            $result['checks'][2]['message'],
        );
    }


    public function testUsesRetrieverConnectionOverride(): void
    {
        $assistant = $this->createAssistant('public');
        $override = new VectorStoreConnection();
        $override->setTitle('Private vector store');
        $override->setConnectorIdentifier('test-vector');
        $override->setEndpoint('https://private-vector.example.test');
        $override->setDefaultCollection('private');
        $this->getRetriever($assistant)->setRetrievalVectorStoreConnection($override);

        $result = $this->createTester(['private'])->test($assistant);

        self::assertSame('ok', $result['status']);
        self::assertStringContainsString('vector store "Private vector store"', $result['checks'][2]['message']);
    }


    /**
     * @param array<int, string> $remoteCollections Remote collection names.
     */
    private function createTester(array $remoteCollections): BackendAssistantTester
    {
        $aiConnector = $this->createStub(AiConnectorInterface::class);
        $aiConnector->method('getIdentifier')->willReturn('test-ai');
        $aiConnector->method('embed')->willReturn(new EmbeddingResponse([0.1, 0.2]));

        $vectorConnector = $this->createStub(VectorStoreConnectorInterface::class);
        $vectorConnector->method('getIdentifier')->willReturn('test-vector');
        $vectorConnector->method('listCollections')->willReturn($remoteCollections);

        $aiConnectorResolver = new AiConnectorResolver([$aiConnector]);
        $vectorStoreConnectorResolver = new VectorStoreConnectorResolver([$vectorConnector]);
        $retrieverProcessor = new RetrieverProcessor(
            $aiConnectorResolver,
            $vectorStoreConnectorResolver,
            $this->createStub(PipelineLoggerInterface::class),
        );
        $answerProcessor = $this->createStub(ProcessorInterface::class);
        $answerProcessor->method('getIdentifier')->willReturn('test.answer');
        $answerProcessor->method('supports')->willReturnCallback(
            static fn (AssistantPipelineProcessorType $type): bool => $type === AssistantPipelineProcessorType::AnswerGenerator,
        );
        $processorRegistry = new ProcessorRegistry([$retrieverProcessor, $answerProcessor]);

        return new BackendAssistantTester(
            new PipelineValidator(),
            $processorRegistry,
            new ConnectionHealthChecker($aiConnectorResolver, $vectorStoreConnectorResolver),
            $vectorStoreConnectorResolver,
        );
    }


    private function createAssistant(string $collection): AssistantProfile
    {
        $aiConnection = new AiConnection();
        $aiConnection->setTitle('AI');
        $aiConnection->setConnectorIdentifier('test-ai');

        $vectorStoreConnection = new VectorStoreConnection();
        $vectorStoreConnection->setTitle('Vector store');
        $vectorStoreConnection->setConnectorIdentifier('test-vector');
        $vectorStoreConnection->setEndpoint('https://vector.example.test');
        $vectorStoreConnection->setDefaultCollection($collection);

        $retriever = new AssistantPipelineStep();
        $retriever->setTitle('Knowledge');
        $retriever->setType(AssistantPipelineProcessorType::Retriever);
        $retriever->setProcessorIdentifier('aiassistant.retriever.default');
        $retriever->setStage(AssistantPipelineStage::Retrieval);

        $answer = new AssistantPipelineStep();
        $answer->setTitle('Answer');
        $answer->setType(AssistantPipelineProcessorType::AnswerGenerator);
        $answer->setProcessorIdentifier('test.answer');
        $answer->setStage(AssistantPipelineStage::PreAnswer);
        $answer->setFailureStrategy(AssistantPipelineFailureStrategy::Stop);

        $assistant = new AssistantProfile();
        $assistant->setTitle('Test assistant');
        $assistant->setAiConnection($aiConnection);
        $assistant->setVectorStoreConnection($vectorStoreConnection);
        $assistant->addChatPipelineStep($retriever);
        $assistant->addChatPipelineStep($answer);

        return $assistant;
    }


    private function getRetriever(AssistantProfile $assistant): AssistantPipelineStep
    {
        foreach ($assistant->getChatPipelineSteps() as $step) {
            if ($step instanceof AssistantPipelineStep && $step->getType() === AssistantPipelineProcessorType::Retriever) {
                return $step;
            }
        }

        self::fail('Retriever step not found.');
    }
}
