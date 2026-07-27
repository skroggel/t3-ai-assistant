<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Madj2k\AiAssistant\Assistant\Pipeline\Processor\Retrieval;

use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep;
use Madj2k\AiAssistant\Assistant\Context\Context;
use Madj2k\AiAssistant\Assistant\Log\PipelineLogMetaData;
use Madj2k\AiAssistant\Assistant\Log\PipelineLogger;
use Madj2k\AiAssistant\Assistant\DTO\RetrievalDocument;
use Madj2k\AiAssistant\Assistant\Pipeline\Processor\AbstractRetrieverProcessor;
use Madj2k\AiAssistant\Connection\Ai\DTO\EmbeddingRequest;
use Madj2k\AiAssistant\Connection\VectorStore\DTO\VectorSearchRequest;
use Madj2k\AiAssistant\Connection\Registry\AiConnectorRegistry;
use Madj2k\AiAssistant\Connection\Registry\VectorStoreConnectorRegistry;

/**
 * Class RetrievalProcessor
 *
 * Pipeline processor that delegates retrieval to a connector.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final readonly class RetrieverProcessor extends AbstractRetrieverProcessor
{

    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Connection\Registry\AiConnectorRegistry $aiConnectorRegistry AI connector registry.
     * @param \Madj2k\AiAssistant\Connection\Registry\VectorStoreConnectorRegistry $vectorStoreConnectorRegistry Vector store connector registry.
     * @param \Madj2k\AiAssistant\Assistant\Log\PipelineLogger $pipelineLogger Pipeline logger.
     */
    public function __construct(
        private AiConnectorRegistry          $aiConnectorRegistry,
        private VectorStoreConnectorRegistry $vectorStoreConnectorRegistry,
        private PipelineLogger               $pipelineLogger,
    ) {
    }


    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return 'aiassistant.retriever.default';
    }


    /**
     * @inheritDoc
     */
    public function canProcess(Context $context, AssistantPipelineStep $step): bool
    {
        return trim($context->getCurrentQuery()) !== ''
            && trim($context->getAssistant()->getCollection()) !== '';
    }


    /**
     * @inheritDoc
     */
    public function process(Context $context, AssistantPipelineStep $step, ?PipelineLogMetaData $logContext = null): void
    {

        if ($logContext instanceof PipelineLogMetaData) {
            $this->pipelineLogger->logRetrievalRequest($logContext, $step->getTitle(), $this->getIdentifier(), [
                'query' => $context->getCurrentQuery(),
                'collection' => $context->getAssistant()->getCollection(),
                'max_retrieval_results' => $step->getMaxRetrievalResults(),
                'score_threshold' => $step->getScoreThreshold(),
                'prompt_metadata_fields' => $step->getPromptMetadataFieldList(),
            ]);
        }

        $aiConnection = $context->getAssistant()->getAiConnection();
        if ($aiConnection === null) {
            throw new \RuntimeException('No AI connection configured for assistant profile.', 1780573301);
        }

        $embedding = $this->aiConnectorRegistry
            ->get($aiConnection->getConnectorIdentifier())
            ->embed($aiConnection, new EmbeddingRequest($context->getCurrentQuery()))
            ->getEmbedding();

        $vectorStoreConnection = $context->getAssistant()->getVectorStoreConnection();
        if ($vectorStoreConnection === null) {
            throw new \RuntimeException('No vector store connection configured for assistant profile.', 1780573302);
        }

        $rows = $this->vectorStoreConnectorRegistry
            ->get($vectorStoreConnection->getConnectorIdentifier())
            ->search($vectorStoreConnection, new VectorSearchRequest(
                collection: $context->getAssistant()->getCollection(),
                vector: $embedding,
                limit: $step->getMaxRetrievalResults(),
                params: [
                    'hnsw_ef' => 128,
                    'exact' => false,
                ],
                withPayload: true,
                withVector: false,
                vectorName: $context->getAssistant()->getCollection()
            ));


        $documents = [];
        foreach ($rows as $row) {
            $payload = $row->getPayload();
            $score = $row->getScore();

            if ($step->getScoreThreshold() > 0.0 && $score < $step->getScoreThreshold()) {
                continue;
            }

            $documentMetadata = $this->extractMetadata($payload, $step);
            $documents[] = new RetrievalDocument(
                id: $row->getId(),
                score: $score,
                text: $this->extractText($payload),
                documentMetadata: $documentMetadata,
            );
        }

        if ($logContext instanceof PipelineLogMetaData) {
            $this->pipelineLogger->logRetrievalResponse($logContext, $step->getTitle(), $this->getIdentifier(), [
                'result_count' => count($documents),
                'results' => $this->normalizeRawResults($rows),
            ]);
        }


        $context->getRetrieval()->setProcessorIdentifier($this->getIdentifier());
        $context->getRetrieval()->setRawResults($this->normalizeRawResults($rows));

        foreach ($documents as $document) {
            $context->getRetrieval()->addResult($document);
        }

        // trace
        $context->getProcessingTrace()->add('retriever.completed',
            $step->getUid(),
            $context->getCurrentQuery(),
            $documents,
        );

    }


    /**
     * Normalizes raw connector rows for logging and context storage.
     *
     * @param array<int,mixed> $rows Raw connector rows.
     * @return array<int,mixed> Normalized raw rows.
     */
    private function normalizeRawResults(array $rows): array
    {
        $rawResults = [];

        foreach ($rows as $row) {
            if (is_object($row)) {
                if (method_exists($row, 'toArray')) {
                    $rawResults[] = $row->toArray();
                }
                continue;
            }

            $rawResults[] = $row;
        }

        return $rawResults;
    }
}
