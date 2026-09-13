<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, version 3.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Madj2k\AiAssistant\Assistant\Log;

use Madj2k\AiCore\Assistant\Log\PipelineLoggerInterface;
use Madj2k\AiAssistant\Assistant\Domain\Model\PipelineTrace;
use Madj2k\AiCore\Assistant\DTO\AssistantRequest;
use Madj2k\AiCore\Assistant\Log\PipelineLogMetaData;
use Madj2k\AiAssistant\Config\Config;
use Madj2k\AiCore\Exception\AppException;
use Madj2k\AiAssistant\Assistant\Domain\Repository\PipelineTraceRepository;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ChatPipelineLogger
 *
 * Writes a chronological event log for chat requests. The log is intentionally
 * event based: every pipeline step, LLM request, LLM response and retrieval
 * request is written as one trace row with a shared trace id.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package ai_assistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
class PipelineLogger implements PipelineLoggerInterface
{
    /**
     * @var \Madj2k\AiAssistant\Assistant\Domain\Repository\PipelineTraceRepository
     */
    protected PipelineTraceRepository $pipelineTraceRepository;


    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected LoggerInterface $logger;


    /**
     * @param \Madj2k\AiAssistant\Assistant\Domain\Repository\PipelineTraceRepository|null $pipelineTraceRepository Trace repository.
     * @param \Psr\Log\LoggerInterface|null $logger Logger.
     */
    public function __construct(
        ?PipelineTraceRepository $pipelineTraceRepository = null,
        ?LoggerInterface         $logger = null
    ) {
        $this->pipelineTraceRepository = $pipelineTraceRepository ?? new PipelineTraceRepository();
        $this->logger = $logger ?? GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }


    /**
     * Creates a trace context for one chat request.
     *
     * @param \Madj2k\AiCore\Assistant\DTO\AssistantRequest $assistantRequest
     * @param string $route Route name.
     * @return PipelineLogMetaData
     */
    public function createMetaData(
        AssistantRequest $assistantRequest,
        string           $route = '',
    ): PipelineLogMetaData {
        return new PipelineLogMetaData(
            $this->createTraceId(),
            $assistantRequest->query,
            $assistantRequest->chatIdentifier,
            $assistantRequest->assistantProfile,
            $route,
        );
    }


    /**
     * Logs the start of a chat request.
     *
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData $logMetaData Trace context.
     * @param array<string,mixed> $payload Additional payload.
     * @return void
     * @throws \Madj2k\AiCore\Exception\AppException
     */
    public function startChat(PipelineLogMetaData $logMetaData, array $payload = []): void
    {
        $this->log('debug', 'chat.started', $logMetaData, $payload);
    }


    /**
     * Logs the end of a chat request.
     *
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData $logMetaData Trace context.
     * @param array<string,mixed> $payload Additional payload.
     * @return void
     * @throws \Madj2k\AiCore\Exception\AppException
     */
    public function finishChat(PipelineLogMetaData $logMetaData, array $payload = []): void
    {
        $payload['duration_ms'] = $this->calculateDuration($logMetaData->getStartedAt());
        $this->log('debug', 'chat.finished', $logMetaData, $payload);
    }


    /**
     * Logs a failed chat request.
     *
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData $logMetaData Trace context.
     * @param array<string,mixed> $payload Additional payload.
     * @return void
     * @throws \Madj2k\AiCore\Exception\AppException
     */
    public function failChat(PipelineLogMetaData $logMetaData, array $payload = []): void
    {
        $payload['duration_ms'] = $this->calculateDuration($logMetaData->getStartedAt());
        $this->log('error', 'chat.failed', $logMetaData, $payload);
    }


    /**
     * Logs the start of a pipeline step.
     *
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData $logMetaData Trace context.
     * @param string $stepTitle Step title.
     * @param string $processorType Processor type.
     * @param array<string,mixed> $payload Additional payload.
     * @return float Step start time as microtime.
     * @throws \Madj2k\AiCore\Exception\AppException
     */
    public function startStep(
        PipelineLogMetaData $logMetaData,
        string              $stepTitle,
        string              $processorType,
        array               $payload = []
    ): float {
        $startedAt = microtime(true);
        $this->log('debug', 'step.started', $logMetaData, array_merge($payload, [
            'step_title' => $stepTitle,
            'processor_type' => $processorType,
            'started_at_microtime' => $startedAt,
        ]));

        return $startedAt;
    }


    /**
     * Logs the end of a pipeline step.
     *
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData $logMetaData Trace context.
     * @param string $stepTitle Step title.
     * @param string $processorType Processor type.
     * @param float $startedAt Step start time as microtime.
     * @param array<string,mixed> $payload Additional payload.
     * @return void
     * @throws \Madj2k\AiCore\Exception\AppException
     */
    public function finishStep(
        PipelineLogMetaData $logMetaData,
        string              $stepTitle,
        string              $processorType,
        float               $startedAt,
        array               $payload = []
    ): void {
        $this->log('debug', 'step.finished', $logMetaData, array_merge($payload, [
            'step_title' => $stepTitle,
            'processor_type' => $processorType,
            'duration_ms' => $this->calculateDuration($startedAt),
        ]));
    }


    /**
     * Logs an LLM request including role based messages.
     *
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData $logMetaData Trace context.
     * @param string $stepTitle Step title.
     * @param string $processorType Processor type.
     * @param array<int,array<string,mixed>> $messages Role based messages.
     * @param array<string,mixed> $options LLM options.
     * @return void
     * @throws \Madj2k\AiCore\Exception\AppException
     */
    public function logLlmRequest(
        PipelineLogMetaData $logMetaData,
        string              $stepTitle,
        string              $processorType,
        array               $messages,
        array               $options = []
    ): void {
        $this->log('debug', 'llm.request', $logMetaData, [
            'step_title' => $stepTitle,
            'processor_type' => $processorType,
            'model' => (string)($options['model'] ?? null),
            'temperature' => $options['temperature'] ?? null,
            'max_tokens' => $options['max_tokens'] ?? null,
            'message_count' => count($messages),
            'messages' => $this->normalizeMessages($messages),
        ]);
    }


    /**
     * Logs an LLM response.
     *
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData $logMetaData Trace context.
     * @param string $stepTitle Step title.
     * @param string $processorType Processor type.
     * @param string $response Response text.
     * @param array<string,mixed> $payload Additional payload.
     * @return void
     * @throws \Madj2k\AiCore\Exception\AppException
     */
    public function logLlmResponse(
        PipelineLogMetaData $logMetaData,
        string              $stepTitle,
        string              $processorType,
        string              $response,
        array               $payload = []
    ): void {
        $this->log('debug', 'llm.response', $logMetaData, array_merge($payload, [
            'step_title' => $stepTitle,
            'processor_type' => $processorType,
            'response' => $response,
            'response_length' => mb_strlen($response, 'UTF-8'),
        ]));
    }


    /**
     * Logs a retrieval request.
     *
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData $logMetaData Trace context.
     * @param string $stepTitle Step title.
     * @param string $connectorType Connector type.
     * @param array<string,mixed> $payload Retrieval request payload.
     * @return void
     * @throws \Madj2k\AiCore\Exception\AppException
     */
    public function logRetrievalRequest(
        PipelineLogMetaData $logMetaData,
        string              $stepTitle,
        string              $connectorType,
        array               $payload
    ): void {
        $this->log('debug', 'retrieval.request', $logMetaData, array_merge($payload, [
            'step_title' => $stepTitle,
            'processor_type' => $connectorType,
        ]));
    }


    /**
     * Logs a retrieval response.
     *
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData $logMetaData Trace context.
     * @param string $stepTitle Step title.
     * @param string $connectorType Connector type.
     * @param array<string,mixed> $payload Retrieval response payload.
     * @return void
     * @throws \Madj2k\AiCore\Exception\AppException
     */
    public function logRetrievalResponse(
        PipelineLogMetaData $logMetaData,
        string              $stepTitle,
        string              $connectorType,
        array               $payload
    ): void {
        $this->log('debug', 'retrieval.response', $logMetaData, array_merge($payload, [
            'step_title' => $stepTitle,
            'processor_type' => $connectorType,
        ]));
    }


    /**
     * Logs a generic pipeline event.
     *
     * @param string $eventName Event name.
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData $logMetaData Trace context.
     * @param array<string,mixed> $payload Event payload.
     * @return void
     * @throws \Madj2k\AiCore\Exception\AppException
     */
    public function event(string $eventName, PipelineLogMetaData $logMetaData, array $payload = []): void
    {
        $this->log('debug', $eventName, $logMetaData, $payload);
    }


    /**
     * Logs an error event.
     *
     * @param string $eventName Event name.
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData $logMetaData Trace context.
     * @param array<string,mixed> $payload Event payload.
     * @return void
     * @throws \Madj2k\AiCore\Exception\AppException
     */
    public function error(string $eventName, PipelineLogMetaData $logMetaData, array $payload = []): void
    {
        $this->log('error', $eventName, $logMetaData, $payload);
    }


    /**
     * Writes one trace event when tracing is enabled.
     *
     * @param string $level Log level.
     * @param string $eventName Event name.
     * @param \Madj2k\AiCore\Assistant\Log\PipelineLogMetaData $logMetaData Trace context.
     * @param array<string,mixed> $payload Event payload.
     * @return void
     * @throws \Madj2k\AiCore\Exception\AppException
     */
    protected function log(string $level, string $eventName, PipelineLogMetaData $logMetaData, array $payload = []): void
    {
        if (!$this->isEnabledForLevel($level)) {
            return;
        }

        $payload = array_merge($logMetaData->toPayload(), $payload, [
            'logged_at_microtime' => microtime(true),
        ]);
        $payload = $this->sanitizeArray($payload);

        try {
            $trace = new PipelineTrace();
            $trace->setChatIdentifier($logMetaData->getChatIdentifier());
            $trace->setRoute($logMetaData->getRoute());
            $trace->setEventName($eventName);
            $trace->setLevel($level);
            $trace->setPayload($this->truncatedJson($payload));

            $this->pipelineTraceRepository->add($trace);
            if ($this->shouldAlsoWritePsrLog()) {
                $this->logger->log($level === 'error' ? 'error' : 'debug', $eventName, $payload);
            }
        } catch (\Throwable $e) {
            $this->logger->warning('Chat pipeline log could not be written.', [
                'event_name' => $eventName,
                'exception_message' => $e->getMessage(),
            ]);
        }
    }


    /**
     * Checks if a level should be written for the configured mode.
     *
     * @param string $level Log level.
     * @return bool
     * @throws AppException
     */
    protected function isEnabledForLevel(string $level): bool
    {
        $mode = $this->getMode();
        if (in_array($mode, ['verbose', 'full'], true)) {
            return true;
        }

        return $level === 'error' && $mode === 'errors';
    }


    /**
     * @return bool True when trace events should also be written to the PSR log.
     * @throws \Madj2k\AiCore\Exception\AppException
     */
    protected function shouldAlsoWritePsrLog(): bool
    {
        return (bool)Config::get('chat.pipelineLog.writePsrLog', false);
    }


    /**
     * @return string Configured tracing mode.
     * @throws \Madj2k\AiCore\Exception\AppException
     */
    protected function getMode(): string
    {
        $mode = trim((string)Config::get('chat.pipelineLog.mode', 'off'));
        return strtolower($mode);
    }


    /**
     * @return string New trace identifier.
     */
    protected function createTraceId(): string
    {
        try {
            return bin2hex(random_bytes(16));
        } catch (\Throwable) {
            return str_replace('.', '', uniqid('trace', true));
        }
    }


    /**
     * @param float $startedAt Start time as microtime.
     * @return int Duration in milliseconds.
     */
    protected function calculateDuration(float $startedAt): int
    {
        return max(0, (int)round((microtime(true) - $startedAt) * 1000));
    }


    /**
     * @param array<int,array<string,mixed>> $messages Role based messages.
     * @return array<int,array<string,mixed>>
     */
    protected function normalizeMessages(array $messages): array
    {
        $normalizedMessages = [];
        foreach ($messages as $index => $message) {
            if (!is_array($message)) {
                continue;
            }

            $normalizedMessages[] = [
                'index' => $index,
                'role' => (string)($message['role'] ?? ''),
                'source' => (string)($message['source'] ?? $message['metadata']['source'] ?? ''),
                'content' => (string)($message['content'] ?? ''),
                'metadata' => is_array($message['metadata'] ?? null) ? $message['metadata'] : [],
            ];
        }

        return $normalizedMessages;
    }


    /**
     * @param array<string,mixed> $payload Payload.
     * @return array<string,mixed>
     * @throws AppException
     */
    protected function sanitizeArray(array $payload): array
    {
        $sanitized = [];
        foreach ($payload as $key => $value) {
            $sanitized[$key] = $this->sanitizeValue((string)$key, $value);
        }

        return $sanitized;
    }


    /**
     * @param mixed $value Value.
     * @return mixed Sanitized value.
     * @throws \Madj2k\AiCore\Exception\AppException
     */
    protected function sanitizeValue(string $key, mixed $value): mixed
    {
        if (is_array($value)) {
            $sanitized = [];
            foreach ($value as $childKey => $childValue) {
                $sanitized[$childKey] = $this->sanitizeValue((string)$childKey, $childValue);
            }

            return $sanitized;
        }

        if (!is_string($value)) {
            return $value;
        }

        $value = $this->truncate($value);
        if ((bool)Config::get('chat.pipelineLog.maskSensitive', true)) {
            $lowerKey = strtolower($key);
            if (preg_match('/(authorization|token|secret|password|apikey|api_key|client_secret)/', $lowerKey) === 1) {
                return '[masked]';
            }

            $value = preg_replace('/Bearer\s+[A-Za-z0-9._\-]+/i', 'Bearer [masked]', $value) ?? $value;
            $value = preg_replace('/([A-Za-z0-9._%+\-]+)@([A-Za-z0-9.\-]+\.[A-Za-z]{2,})/', '[masked-email]', $value) ?? $value;
        }

        return $value;
    }


    /**
     * @param string $value Value.
     * @return string Truncated value.
     * @throws \Madj2k\AiCore\Exception\AppException
     */
    protected function truncate(string $value): string
    {
        $maxChars = max(50, (int)Config::get(
            'chat.pipelineLog.maxChars', 12000)
        );
        if (mb_strlen($value, 'UTF-8') <= $maxChars) {
            return $value;
        }

        return mb_substr($value, 0, $maxChars, 'UTF-8') . '...';
    }


    /**
     * @param $data
     * @return string
     * @throws \Madj2k\AiCore\Exception\AppException
     */
    function truncatedJson($data): string
    {

        $maxChars = max(50, (int)Config::get(
            'chat.pipelineLog.maxChars', 12000)
        );

        $result = [];
        foreach ($data as $key => $value) {

            $valueJson = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($valueJson === false) {
                $result[$key] = [
                    'truncated' => false,
                    'error' => json_last_error_msg(),
                ];
                continue;
            }

            if (mb_strlen($valueJson) <= $maxChars) {
                $result[$key] = $value;
                continue;
            }

            $previewLength = max(0, $maxChars - 100);

            $result[$key] = [
                'truncated' => true,
                'originalLength' => mb_strlen($valueJson),
                'preview' => mb_substr($valueJson, 0, $previewLength),
            ];
        }

        return json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
