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
use Madj2k\AiCore\Connection\Ai\DTO\AiRequest;
use Madj2k\AiCore\Connection\Ai\DTO\AiResponse;
use Madj2k\AiCore\Connection\Configuration\AiConnectionConfigurationInterface;
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
        self::assertSame(2, $result['embeddingDimension']);
        self::assertTrue($this->hasCheckMessage($result['checks'], 'Collection "public" exists'));
    }


    public function testReportsConfiguredCollectionMissingFromRemoteVectorStore(): void
    {
        $result = $this->createTester(['archive'])->test($this->createAssistant('public'));

        self::assertSame('error', $result['status']);
        self::assertTrue($this->hasCheckMessage($result['checks'], 'Collection "public" does not exist'));
    }


    public function testReportsExistingCollectionWithIncompatibleVectorConfiguration(): void
    {
        $result = $this->createTester(
            ['public'],
            collectionCompatible: false,
        )->test($this->createAssistant('public'));

        self::assertSame('error', $result['status']);
        self::assertTrue($this->hasCheckMessage(
            $result['checks'],
            'is not compatible with the effective embedding configuration',
        ));
    }


    public function testRejectsCollectionOutsideEffectiveConnectionConfiguration(): void
    {
        $assistant = $this->createAssistant('public');
        $this->getRetriever($assistant)->setRetrievalCollection('missing');

        $result = $this->createTester(['public', 'missing'])->test($assistant);

        self::assertSame('error', $result['status']);
        self::assertTrue($this->hasCheckMessage(
            $result['checks'],
            'Collection "missing" is not configured for the effective vector store connection',
        ));
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
        self::assertTrue($this->hasCheckMessage($result['checks'], 'vector store "Private vector store"'));
    }


    public function testReportsEmbeddingDimensionMismatchForRetriever(): void
    {
        $assistant = $this->createAssistant('public');
        $assistant->getAiConnection()?->setEmbeddingDimension(3);

        $result = $this->createTester(['public'])->test($assistant);

        self::assertSame('error', $result['status']);
        self::assertTrue($this->hasCheckMessage(
            $result['checks'],
            'AI connection returns 2 dimensions, but it is configured for 3',
        ));
    }


    public function testReportsSharedVectorStoreDimensionOnlyOnce(): void
    {
        $assistant = $this->createAssistant('public');
        $secondRetriever = new AssistantPipelineStep();
        $secondRetriever->setTitle('Archive');
        $secondRetriever->setType(AssistantPipelineProcessorType::Retriever);
        $secondRetriever->setProcessorIdentifier('aiassistant.retriever.default');
        $secondRetriever->setStage(AssistantPipelineStage::Retrieval);
        $assistant->addChatPipelineStep($secondRetriever);

        $result = $this->createTester(['public'])->test($assistant);

        self::assertSame('ok', $result['status']);
        self::assertSame(1, $this->countChecksByLabel($result['checks'], 'AI embedding configuration'));
    }


    public function testRejectsMissingEmbeddingDimension(): void
    {
        $assistant = $this->createAssistant('public');
        $assistant->getAiConnection()?->setEmbeddingDimension(0);

        $result = $this->createTester(['public'])->test($assistant);

        self::assertSame('error', $result['status']);
        self::assertTrue($this->hasCheckMessage(
            $result['checks'],
            'embedding dimension must be greater than zero',
        ));
    }


    public function testReportsEmptyChatProbeResponse(): void
    {
        $result = $this->createTester(['public'], '')->test($this->createAssistant('public'));

        self::assertSame('error', $result['status']);
        self::assertTrue($this->hasCheckMessage($result['checks'], 'Chat test returned an empty response.'));
    }


    public function testReportsUnsupportedModelOverrideAndAffectedStep(): void
    {
        $assistant = $this->createAssistant('public');
        $this->getStep($assistant, AssistantPipelineProcessorType::AnswerGenerator)
            ->setModel('foreign-provider-model');

        $result = $this->createTester(
            ['public'],
            unsupportedModels: ['foreign-provider-model'],
        )->test($assistant);

        self::assertSame('error', $result['status']);
        self::assertTrue($this->hasCheckMessage(
            $result['checks'],
            'Used by: "Answer". Model is not supported by this provider.',
        ));
    }


    /**
     * @param array<int, string> $remoteCollections Remote collection names.
     * @param string $chatResponse Chat probe response content.
     * @param array<int, string> $unsupportedModels Model overrides rejected by the test connector.
     * @param bool $collectionCompatible Whether the remote collection matches the requested vector configuration.
     */
    private function createTester(
        array $remoteCollections,
        string $chatResponse = 'OK',
        array $unsupportedModels = [],
        bool $collectionCompatible = true,
    ): BackendAssistantTester
    {
        $aiConnector = $this->createStub(AiConnectorInterface::class);
        $aiConnector->method('getIdentifier')->willReturn('test-ai');
        $aiConnector->method('embed')->willReturn(new EmbeddingResponse([0.1, 0.2]));
        $aiConnector->method('chat')->willReturnCallback(
            static function (
                AiConnectionConfigurationInterface $connection,
                AiRequest $request,
            ) use ($chatResponse, $unsupportedModels): AiResponse {
                if (in_array($request->getModel(), $unsupportedModels, true)) {
                    throw new \RuntimeException('Model is not supported by this provider.');
                }

                return new AiResponse($chatResponse);
            },
        );

        $vectorConnector = $this->createStub(VectorStoreConnectorInterface::class);
        $vectorConnector->method('getIdentifier')->willReturn('test-vector');
        $vectorConnector->method('listCollections')->willReturn($remoteCollections);
        $vectorConnector->method('ensureCollection')->willReturn($collectionCompatible);

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
        $aiConnection->setEmbeddingDimension(2);

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
        return $this->getStep($assistant, AssistantPipelineProcessorType::Retriever);
    }


    /**
     * Returns the first pipeline step of the requested type.
     *
     * @param \Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile $assistant Assistant profile.
     * @param \Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType $type Pipeline step type.
     * @return \Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep Pipeline step.
     */
    private function getStep(
        AssistantProfile $assistant,
        AssistantPipelineProcessorType $type,
    ): AssistantPipelineStep {
        foreach ($assistant->getChatPipelineSteps() as $step) {
            if ($step->getType() === $type) {
                return $step;
            }
        }

        self::fail(sprintf('Pipeline step of type "%s" not found.', $type->value));
    }


    /**
     * Determines whether any diagnostic check contains the expected message fragment.
     *
     * @param array<int, array{status: string, label: string, message: string}> $checks Diagnostic checks.
     * @param string $expectedMessage Expected message fragment.
     * @return bool True when a matching check exists.
     */
    private function hasCheckMessage(array $checks, string $expectedMessage): bool
    {
        foreach ($checks as $check) {
            if (str_contains($check['message'], $expectedMessage)) {
                return true;
            }
        }

        return false;
    }


    /**
     * Counts diagnostic checks with the expected label.
     *
     * @param array<int, array{status: string, label: string, message: string}> $checks Diagnostic checks.
     * @param string $expectedLabel Expected check label.
     * @return int Number of matching checks.
     */
    private function countChecksByLabel(array $checks, string $expectedLabel): int
    {
        $matches = 0;
        foreach ($checks as $check) {
            if ($check['label'] === $expectedLabel) {
                ++$matches;
            }
        }

        return $matches;
    }
}
