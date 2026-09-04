<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension ai_assistant.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiAssistant\Backend\Diagnostics;

use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile;
use Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection;
use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\Assistant\Pipeline\PipelineValidator;
use Madj2k\AiCore\Assistant\Pipeline\Processor\Retrieval\RetrieverProcessor;
use Madj2k\AiCore\Assistant\Pipeline\Registry\ProcessorRegistry;
use Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface;
use Madj2k\AiCore\Connection\Health\ConnectionHealthChecker;
use Madj2k\AiCore\Connection\Resolver\VectorStoreConnectorResolver;

/**
 * Class BackendAssistantTester
 *
 * Validates one assistant pipeline and its effective external connections.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final readonly class BackendAssistantTester
{
    /**
     * @param \Madj2k\AiCore\Assistant\Pipeline\PipelineValidator $pipelineValidator Pipeline validator.
     * @param \Madj2k\AiCore\Assistant\Pipeline\Registry\ProcessorRegistry $processorRegistry Processor registry.
     * @param \Madj2k\AiCore\Connection\Health\ConnectionHealthChecker $connectionHealthChecker Connection health checker.
     * @param \Madj2k\AiCore\Connection\Resolver\VectorStoreConnectorResolver $vectorStoreConnectorResolver Vector store connector resolver.
     */
    public function __construct(
        private PipelineValidator $pipelineValidator,
        private ProcessorRegistry $processorRegistry,
        private ConnectionHealthChecker $connectionHealthChecker,
        private VectorStoreConnectorResolver $vectorStoreConnectorResolver,
    ) {
    }


    /**
     * Tests one assistant without executing its chat pipeline.
     *
     * @param \Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile $assistant Assistant profile.
     * @return array{status: string, message: string, checks: array<int, array{status: string, label: string, message: string}>, embeddingDimension: int|null} Test result.
     */
    public function test(AssistantProfile $assistant): array
    {
        /** @var array<int, \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface> $steps */
        $steps = iterator_to_array($assistant->getChatPipelineSteps(), false);
        $checks = [];

        try {
            $warnings = $this->pipelineValidator->validate($steps, $this->processorRegistry);
            $checks[] = $this->check(
                $warnings === [] ? 'ok' : 'warning',
                'Pipeline',
                $warnings === [] ? 'Pipeline configuration is valid.' : implode(' ', $warnings),
            );
        } catch (\Throwable $exception) {
            $checks[] = $this->check('error', 'Pipeline', $exception->getMessage());
        }

        $aiConnection = $assistant->getAiConnection();
        $embeddingDimension = null;
        if ($aiConnection === null) {
            $checks[] = $this->check('error', 'AI connection', 'No AI connection is configured.');
        } else {
            try {
                $embeddingResponse = $this->connectionHealthChecker->probeAiEmbedding(
                    $aiConnection,
                    'TYPO3 AI Assistant profile diagnostics',
                );
                $embeddingDimension = count($embeddingResponse->getEmbedding());
                $checks[] = $this->check(
                    $embeddingDimension > 0 ? 'ok' : 'error',
                    'AI embedding',
                    $embeddingDimension > 0
                        ? sprintf('Embedding test succeeded with %d dimensions.', $embeddingDimension)
                        : 'AI connection returned an empty embedding.',
                );
            } catch (\Throwable $exception) {
                $checks[] = $this->check('error', 'AI embedding', $exception->getMessage());
            }

            try {
                $chatResponse = $this->connectionHealthChecker->probeAiChat(
                    $aiConnection,
                    'Reply with OK.',
                );
                $chatHealthy = trim($chatResponse->getContent()) !== '';
                $checks[] = $this->check(
                    $chatHealthy ? 'ok' : 'error',
                    'AI chat',
                    $chatHealthy ? 'Chat test returned a response.' : 'Chat test returned an empty response.',
                );
            } catch (\Throwable $exception) {
                $checks[] = $this->check('error', 'AI chat', $exception->getMessage());
            }

            $checks = array_merge($checks, $this->testModelOverrides($steps, $aiConnection));
        }

        /**
         * @var array<string, array{collections?: array<int, string>, error?: string}> $remoteCollections
         */
        $remoteCollections = [];
        /** @var array<string, true> $checkedDimensionConnections */
        $checkedDimensionConnections = [];
        foreach ($steps as $step) {
            try {
                $processor = $this->processorRegistry->get($step->getProcessorIdentifier(), $step->getType());
            } catch (\Throwable) {
                continue;
            }

            if (!$processor instanceof RetrieverProcessor) {
                continue;
            }

            $connection = $step->getRetrievalVectorStoreConnection()
                ?? $assistant->getVectorStoreConnection();
            $stepLabel = sprintf('Retriever "%s"', trim($step->getTitle()));

            if ($connection === null) {
                $checks[] = $this->check('error', $stepLabel, 'No effective vector store connection is configured.');
                continue;
            }

            $connectionKey = (string)spl_object_id($connection);
            if (
                $embeddingDimension !== null
                && $embeddingDimension > 0
                && !isset($checkedDimensionConnections[$connectionKey])
            ) {
                $configuredVectorSize = $connection->getVectorSize();
                $connectionLabel = $this->connectionLabel($connection);
                $checks[] = $this->check(
                    $embeddingDimension === $configuredVectorSize ? 'ok' : 'error',
                    sprintf('Vector store "%s"', $connectionLabel),
                    $embeddingDimension === $configuredVectorSize
                        ? sprintf(
                            'AI embedding dimension %d matches the configured vector size.',
                            $embeddingDimension,
                        )
                        : sprintf(
                            'AI connection returns %d dimensions, but the configured vector size is %d. Align the embedding configuration and reindex into a compatible collection.',
                            $embeddingDimension,
                            $configuredVectorSize,
                        ),
                );
                $checkedDimensionConnections[$connectionKey] = true;
            }

            $collection = trim($step->getRetrievalCollection()) !== ''
                ? trim($step->getRetrievalCollection())
                : trim($connection->getDefaultCollection());
            if ($collection === '') {
                $checks[] = $this->check('error', $stepLabel, 'No effective collection is configured.');
                continue;
            }

            if (!in_array($collection, $connection->getCollectionList(), true)) {
                $checks[] = $this->check(
                    'error',
                    $stepLabel,
                    sprintf('Collection "%s" is not configured for the effective vector store connection.', $collection),
                );
                continue;
            }

            if (!isset($remoteCollections[$connectionKey])) {
                $remoteCollections[$connectionKey] = $this->loadRemoteCollections($connection);
            }
            $remoteResult = $remoteCollections[$connectionKey];

            if (isset($remoteResult['error'])) {
                $checks[] = $this->check('error', $stepLabel, $remoteResult['error']);
                continue;
            }
            if (!in_array($collection, $remoteResult['collections'] ?? [], true)) {
                $checks[] = $this->check(
                    'error',
                    $stepLabel,
                    sprintf(
                        'Collection "%s" does not exist on vector store "%s".',
                        $collection,
                        $this->connectionLabel($connection),
                    ),
                );
                continue;
            }

            $checks[] = $this->check(
                'ok',
                $stepLabel,
                sprintf(
                    'Collection "%s" exists on vector store "%s".',
                    $collection,
                    $this->connectionLabel($connection),
                ),
            );
        }

        $status = 'ok';
        foreach ($checks as $check) {
            if ($check['status'] === 'error') {
                $status = 'error';
                break;
            }
            if ($check['status'] === 'warning') {
                $status = 'warning';
            }
        }

        return [
            'status' => $status,
            'message' => match ($status) {
                'error' => 'Assistant diagnostics found configuration errors.',
                'warning' => 'Assistant diagnostics completed with warnings.',
                default => 'Assistant diagnostics succeeded.',
            },
            'checks' => $checks,
            'embeddingDimension' => $embeddingDimension,
        ];
    }


    /**
     * Tests each distinct provider-specific model override used by LLM steps.
     *
     * @param array<int, \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface> $steps Pipeline steps.
     * @param \Madj2k\AiAssistant\Connection\Domain\Model\AiConnection $aiConnection Effective AI connection.
     * @return array<int, array{status: string, label: string, message: string}> Diagnostic checks.
     */
    private function testModelOverrides(array $steps, \Madj2k\AiAssistant\Connection\Domain\Model\AiConnection $aiConnection): array
    {
        /** @var array<string, array<int, string>> $stepsByModel */
        $stepsByModel = [];
        foreach ($steps as $step) {
            if (!$this->isLlmStep($step)) {
                continue;
            }

            $model = trim($step->getModel());
            if ($model === '') {
                continue;
            }

            $stepTitle = trim($step->getTitle());
            $stepsByModel[$model][] = $stepTitle !== '' ? $stepTitle : $step->getProcessorIdentifier();
        }

        $checks = [];
        foreach ($stepsByModel as $model => $stepTitles) {
            $stepList = implode(', ', array_map(
                static fn (string $title): string => sprintf('"%s"', $title),
                $stepTitles,
            ));

            try {
                $response = $this->connectionHealthChecker->probeAiChat(
                    $aiConnection,
                    'Reply with OK.',
                    $model,
                );
                $healthy = trim($response->getContent()) !== '';
                $checks[] = $this->check(
                    $healthy ? 'ok' : 'error',
                    sprintf('Model override "%s"', $model),
                    $healthy
                        ? sprintf('Chat test succeeded. Used by: %s.', $stepList)
                        : sprintf('Chat test returned an empty response. Used by: %s.', $stepList),
                );
            } catch (\Throwable $exception) {
                $checks[] = $this->check(
                    'error',
                    sprintf('Model override "%s"', $model),
                    sprintf('Chat test failed. Used by: %s. %s', $stepList, $exception->getMessage()),
                );
            }
        }

        return $checks;
    }


    /**
     * Determines whether a pipeline step performs an LLM chat request.
     *
     * @param \Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface $step Pipeline step.
     * @return bool True for LLM-based pipeline step types.
     */
    private function isLlmStep(PipelineStepConfigurationInterface $step): bool
    {
        return in_array($step->getType(), [
            AssistantPipelineProcessorType::QueryOptimizer,
            AssistantPipelineProcessorType::ContextOptimizer,
            AssistantPipelineProcessorType::AnswerGenerator,
            AssistantPipelineProcessorType::QualityGate,
        ], true);
    }


    /**
     * Loads remote collection names without modifying the vector store.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface $connection Vector store connection.
     * @return array{collections?: array<int, string>, error?: string} Collection result.
     */
    private function loadRemoteCollections(VectorStoreConnectionConfigurationInterface $connection): array
    {
        try {
            return [
                'collections' => $this->vectorStoreConnectorResolver
                    ->get($connection->getConnectorIdentifier())
                    ->listCollections($connection),
            ];
        } catch (\Throwable $exception) {
            return ['error' => $exception->getMessage()];
        }
    }


    /**
     * Returns a human-readable vector store label.
     *
     * @param \Madj2k\AiCore\Connection\Configuration\VectorStoreConnectionConfigurationInterface $connection Vector store connection.
     * @return string Connection label.
     */
    private function connectionLabel(VectorStoreConnectionConfigurationInterface $connection): string
    {
        if ($connection instanceof VectorStoreConnection && trim($connection->getTitle()) !== '') {
            return trim($connection->getTitle());
        }

        return $connection->getConnectorIdentifier();
    }


    /**
     * Creates one displayable check result.
     *
     * @param string $status Check status.
     * @param string $label Check label.
     * @param string $message Check message.
     * @return array{status: string, label: string, message: string} Check result.
     */
    private function check(string $status, string $label, string $message): array
    {
        return [
            'status' => $status,
            'label' => $label,
            'message' => $message,
        ];
    }
}
