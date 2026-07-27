<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension ai_assistant.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiAssistant\Backend\Diagnostics;

use Madj2k\AiAssistant\Assistant\Domain\Repository\PipelineTraceRepository;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class BackendDiagnosticsProvider
 *
 * Builds pipeline log view data from pipeline trace records.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class BackendDiagnosticsProvider
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Assistant\Domain\Repository\PipelineTraceRepository $pipelineTraceRepository Pipeline trace repository.
     */
    public function __construct(
        private readonly PipelineTraceRepository $pipelineTraceRepository
    ) {
    }


    /**
     * Builds pipeline log view data.
     *
     * @param object $extbaseRequest Extbase request.
     * @param \Psr\Http\Message\ServerRequestInterface $backendRequest Backend request.
     * @return array<string, mixed> View data.
     */
    public function getViewData(object $extbaseRequest, ServerRequestInterface $backendRequest): array
    {
        /** @var int $limit */
        $limit = $this->resolveLimit($extbaseRequest, $backendRequest);

        /** @var string $chatIdentifier */
        $chatIdentifier = $this->resolveStringArgument($extbaseRequest, $backendRequest, 'chatIdentifier');

        /** @var array<int, array<string, mixed>> $traceRows */
        $traceRows = [];

        foreach ($this->pipelineTraceRepository->findRecentRows($limit, $chatIdentifier) as $trace) {
            /** @var string $payloadRaw */
            $payloadRaw = (string)($trace['payload'] ?? '');

            /** @var mixed $payload */
            $payload = json_decode($payloadRaw, true);
            if (!is_array($payload)) {
                $payload = [];
            }

            /** @var string|false $payloadPretty */
            $payloadPretty = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if (!is_string($payloadPretty)) {
                $payloadPretty = $payloadRaw;
            }
            $payloadPretty = str_replace(['\\r\\n', '\\n', '\\t'], ["\n", "\n", "\t"], $payloadPretty);

            $traceRows[] = [
                'uid' => (int)($trace['uid'] ?? 0),
                'crdate' => (int)($trace['crdate'] ?? 0),
                'level' => (string)($trace['level'] ?? ''),
                'eventName' => (string)($trace['event_name'] ?? ''),
                'route' => (string)($trace['route'] ?? ''),
                'chatIdentifier' => (string)($trace['chat_identifier'] ?? ''),
                'queryText' => (string)($trace['query_text'] ?? ''),
                'payload' => $payloadRaw,
                'payloadDecoded' => $payload,
                'payloadPretty' => $payloadPretty,
            ];
        }

        return [
            'chatTraces' => $traceRows,
            'chatTraceLimit' => $limit,
            'chatTraceFilterChatIdentifier' => $chatIdentifier,
            'chatTraceChatIdentifiers' => $this->pipelineTraceRepository->findRecentChatIdentifiers(100),
            'chatTraceTotal' => $this->pipelineTraceRepository->countAll($chatIdentifier),
            'hasDiagnosticIssues' => false,
            'diagnosticIssueCount' => 0,
        ];
    }


    /**
     * Resolves the requested log limit.
     *
     * @param object $extbaseRequest Extbase request.
     * @param \Psr\Http\Message\ServerRequestInterface $backendRequest Backend request.
     * @return int Limit.
     */
    private function resolveLimit(object $extbaseRequest, ServerRequestInterface $backendRequest): int
    {
        /** @var string $rawLimit */
        $rawLimit = $this->resolveStringArgument($extbaseRequest, $backendRequest, 'limit');

        if ($rawLimit === '') {
            return 100;
        }

        return max(1, min(500, (int)$rawLimit));
    }


    /**
     * Resolves an argument from Extbase arguments or raw backend query parameters.
     *
     * @param object $extbaseRequest Extbase request.
     * @param \Psr\Http\Message\ServerRequestInterface $backendRequest Backend request.
     * @param string $name Argument name.
     * @return string Argument value.
     */
    private function resolveStringArgument(object $extbaseRequest, ServerRequestInterface $backendRequest, string $name): string
    {
        if (method_exists($extbaseRequest, 'hasArgument') && $extbaseRequest->hasArgument($name)) {
            return trim((string)$extbaseRequest->getArgument($name));
        }

        /** @var array<string, mixed> $queryParams */
        $queryParams = $backendRequest->getQueryParams();
        if (array_key_exists($name, $queryParams)) {
            return trim((string)$queryParams[$name]);
        }

        return '';
    }
}
