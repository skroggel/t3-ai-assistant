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
namespace Madj2k\AiAssistant\Assistant\Context\Retrieval;

use Madj2k\AiAssistant\Assistant\DTO\RetrievalDocument;

/**
 * Class RetrievalState
 *
 * Holds raw retrieval results and the answer context derived from them.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class RetrievalResult
{
    /**
     * Raw retrieval results.
     *
     * @var array<int, \Madj2k\AiAssistant\Assistant\DTO\RetrievalDocument>
     */
    protected array $results = [];


    /**
     * Raw retrieval results.
     *
     * @var array<int, mixed>
     */
    protected array $rawResults = [];


    /**
     * Answer context derived from raw retrieval results.
     *
     * @var string
     */
    protected string $answerContext = '';


    /**
     * Processor identifier that produced the current raw retrieval results.
     *
     * @var string
     */
    protected string $processorIdentifier = '';



    /**
     * Returns the raw retrieval results.
     *
     * @return array<int, \Madj2k\AiAssistant\Assistant\DTO\RetrievalDocument> Raw retrieval results.
     */
    public function getResults(): array
    {
        return $this->results;
    }


    /**
     * Sets the raw retrieval results.
     *
     * @param array<int, \Madj2k\AiAssistant\Assistant\DTO\RetrievalDocument> $results Raw retrieval results.
     * @return void
     */
    public function setResults(array $results): void
    {
        $this->results = [];

        foreach ($results as $result) {
            if ($result instanceof RetrievalDocument) {
                $this->results[] = $result;
            }
        }
    }


    /**
     * Adds a raw retrieval result.
     *
     * @param \Madj2k\AiAssistant\Assistant\DTO\RetrievalDocument $result Raw retrieval result.
     * @return void
     */
    public function addResult(RetrievalDocument $result): void
    {
        $this->results[] = $result;
    }


    /**
     * Removes all raw retrieval results.
     *
     * @return void
     */
    public function clearResults(): void
    {
        $this->results = [];
    }


    /**
     * Returns the raw retrieval results.
     *
     * @return array<int, mixed> Raw retrieval results.
     */
    public function getRawResults(): array
    {
        return $this->rawResults;
    }


    /**
     * Sets the raw retrieval results.
     *
     * @param array<int, mixed> $rawResults Raw retrieval results.
     * @return void
     */
    public function setRawResults(array $rawResults): void
    {
        $this->rawResults = [];
        foreach ($rawResults as $rawResult) {
            if (is_object($rawResult)) {
                if (method_exists($rawResult, 'toArray')) {
                    $this->rawResults[] = $rawResult->toArray();
                }
            } else {
                $this->rawResults[] = $rawResult;
            }
        }
    }


    /**
     * Adds a raw retrieval result.
     *
     * @param mixed $rawResult Raw retrieval result.
     * @return void
     */
    public function addRawResult(mixed $rawResult): void
    {
        if (is_object($rawResult)) {
            if (method_exists($rawResult, 'toArray')) {
                $this->rawResults[] = $rawResult->toArray();
            }
        } else {
            $this->rawResults[] = $rawResult;
        }
    }


    /**
     * Removes all raw retrieval results.
     *
     * @return void
     */
    public function clearRawResults(): void
    {
        $this->rawResults = [];
    }


    /**
     * Returns the processor identifier that produced the current raw retrieval results.
     *
     * @return string Processor identifier.
     */
    public function getProcessorIdentifier(): string
    {
        return $this->processorIdentifier;
    }


    /**
     * Sets the processor identifier that produced the current raw retrieval results.
     *
     * @param string $processorIdentifier Processor identifier.
     * @return void
     */
    public function setProcessorIdentifier(string $processorIdentifier): void
    {
        $this->processorIdentifier = trim($processorIdentifier);
    }


    /**
     * Returns the answer context derived from raw retrieval results.
     *
     * @return string Answer context derived from raw retrieval results.
     */
    public function getAnswerContext(): string
    {
        return $this->answerContext;
    }


    /**
     * Sets the answer context derived from raw retrieval results.
     *
     * @param string $answerContext Answer context derived from raw retrieval results.
     * @return void
     */
    public function setAnswerContext(string $answerContext): void
    {
        $this->answerContext = trim($answerContext);
    }
}
