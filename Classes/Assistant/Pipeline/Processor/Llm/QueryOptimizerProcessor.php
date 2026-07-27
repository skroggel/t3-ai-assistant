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


namespace Madj2k\AiAssistant\Assistant\Pipeline\Processor\Llm;

use Madj2k\AiAssistant\Assistant\Context\Context;
use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep;
use Madj2k\AiAssistant\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiAssistant\Assistant\Log\PipelineLogMetaData;
use Madj2k\AiAssistant\Assistant\Log\PipelineLogger;
use Madj2k\AiAssistant\Assistant\Pipeline\Processor\AbstractLlmProcessor;
use Madj2k\AiAssistant\Assistant\Prompt\PromptBuilder;
use Madj2k\AiAssistant\Connection\Registry\AiConnectorRegistry;

/**
 * Class QueryOptimizerProcessor
 *
 * Rewrites the user query for retrieval while preserving the original query.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class QueryOptimizerProcessor extends AbstractLlmProcessor
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Connection\Registry\AiConnectorRegistry $aiConnectorRegistry AI connector registry.
     * @param \Madj2k\AiAssistant\Assistant\Prompt\PromptBuilder $promptBuilder Prompt builder.
     * @param \Madj2k\AiAssistant\Assistant\Log\PipelineLogger $pipelineLogger Pipeline logger.
     */
    public function __construct(
        AiConnectorRegistry $aiConnectorRegistry,
        PromptBuilder $promptBuilder,
        PipelineLogger $pipelineLogger,
    ) {
        parent::__construct($aiConnectorRegistry, $promptBuilder, $pipelineLogger);
    }


    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return 'aiassistant.query_optimizer.default';
    }


    /**
     * @inheritDoc
     */
    public function supports(AssistantPipelineProcessorType $type): bool
    {
        return $type === AssistantPipelineProcessorType::QueryOptimizer;
    }


    /**
     * @inheritDoc
     */
    public function canProcess(Context $context, AssistantPipelineStep $step): bool
    {
        return trim($context->getRequest()->getQuery()) !== '';
    }


    /**
     * @inheritDoc
     */
    public function process(Context $context, AssistantPipelineStep $step, ?PipelineLogMetaData $logContext = null): void
    {
        $messages = $this->promptBuilder->buildMessages($context, $step);
        $optimizedQuery = $this->normalizeOptimizedQuery(
            $this->callAi($context, $messages, $step, $logContext)
        );

        if ($optimizedQuery !== '') {
            $context->setCurrentQuery($optimizedQuery);
        }

        // trace
        $context->getProcessingTrace()->add('query_optimizer.completed',
            $step->getUid(),
            $messages,
            $context->getCurrentQuery()
        );
    }


    /**
     * Normalizes the LLM output to a single query string.
     *
     * @param string $optimizedQuery Raw optimized query.
     * @return string Normalized query.
     */
    private function normalizeOptimizedQuery(string $optimizedQuery): string
    {
        $optimizedQuery = trim($optimizedQuery);
        if ($optimizedQuery === '') {
            return '';
        }

        if (str_contains($optimizedQuery, "\n")) {
            $lines = array_values(array_filter(
                array_map('trim', preg_split('/\R+/', $optimizedQuery) ?: []),
                static fn (string $line): bool => $line !== ''
            ));

            return $lines[0] ?? '';
        }

        return $optimizedQuery;
    }
}
