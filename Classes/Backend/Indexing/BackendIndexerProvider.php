<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension ai_assistant.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiAssistant\Backend\Indexing;

use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig;
use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerRun;
use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerSource;
use Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerConfigRepository;
use Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerRunRepository;
use Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerSourceRepository;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class BackendIndexerProvider
 *
 * Builds the readonly indexer status view data.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class BackendIndexerProvider
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerConfigRepository $indexerConfigRepository Indexer repository.
     * @param \Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerRunRepository $indexerRunRepository Run repository.
     * @param \Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerSourceRepository $indexerSourceRepository Source repository.
     */
    public function __construct(
        private readonly IndexerConfigRepository $indexerConfigRepository,
        private readonly IndexerRunRepository $indexerRunRepository,
        private readonly IndexerSourceRepository $indexerSourceRepository
    ) {
    }


    /**
     * Builds indexer status view data.
     *
     * @param object $extbaseRequest Extbase request.
     * @param \Psr\Http\Message\ServerRequestInterface $backendRequest Backend request.
     * @param array<string, mixed> $state Module state.
     * @return array<string, mixed> View data.
     */
    public function getViewData(object $extbaseRequest, ServerRequestInterface $backendRequest, array $state): array
    {
        /** @var \TYPO3\CMS\Backend\Routing\UriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        /** @var array<int, array<string, mixed>> $indexers */
        $indexers = [];

        foreach ($this->indexerConfigRepository->findAll() as $indexerConfig) {
            if (!$indexerConfig instanceof IndexerConfig) {
                continue;
            }

            /** @var int $uid */
            $uid = (int)$indexerConfig->getUid();

            /** @var \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerRun|null $lastRun */
            $lastRun = $this->indexerRunRepository->findLastBySourceType($indexerConfig->getType(), $uid);

            /** @var array<string, mixed> $sourceStatus */
            $sourceStatus = $this->buildSourceStatus($uid);
            $lastRunData = $this->buildLastRunData($lastRun);
            $lastRunMessages = $this->buildLastRunMessages($lastRun);
            $failedCount = max(
                (int)$sourceStatus['failed'],
                (int)($lastRunData['items_failed'] ?? 0)
            );

            $indexers[] = [
                'uid' => $uid,
                'title' => $indexerConfig->getTitle(),
                'type' => $indexerConfig->getType(),
                'indexerIdentifier' => $indexerConfig->getIndexerIdentifier(),
                'collection' => $indexerConfig->getCollection(),
                'hidden' => false,
                'sourceCount' => $sourceStatus['count'],
                'indexedCount' => $sourceStatus['indexed'],
                'skippedCount' => $sourceStatus['skipped'],
                'failedCount' => $failedCount,
                'pendingCount' => $sourceStatus['pending'],
                'processingCount' => $sourceStatus['processing'],
                'unknownCount' => $sourceStatus['unknown'],
                'lastIndexed' => $sourceStatus['lastIndexed'],
                'stats' => [
                    'sources' => $sourceStatus['count'],
                    'indexed' => $sourceStatus['indexed'],
                    'skipped' => $sourceStatus['skipped'],
                    'failed' => $failedCount,
                    'pending' => $sourceStatus['pending'],
                    'processing' => $sourceStatus['processing'],
                    'unknown' => $sourceStatus['unknown'],
                    'last_indexed' => $sourceStatus['lastIndexed'],
                ],
                'statsPresentation' => [
                    'isEmpty' => $sourceStatus['count'] === 0,
                    'isStale' => false,
                ],
                'lastRunStatus' => $lastRun instanceof IndexerRun ? $lastRun->getStatus() : '',
                'lastRunStartedAt' => $lastRun instanceof IndexerRun ? $lastRun->getStartedAt() : 0,
                'lastRunFinishedAt' => $lastRun instanceof IndexerRun ? $lastRun->getFinishedAt() : 0,
                'lastRunMessage' => $lastRun instanceof IndexerRun ? $lastRun->getMessage() : '',
                'lastRun' => $lastRunData,
                'lastRunPresentation' => [
                    'text' => $lastRunData !== []
                        ? sprintf(
                            'Processed %d, indexed %d, skipped %d, failed %d',
                            (int)$lastRunData['items_processed'],
                            (int)$lastRunData['items_indexed'],
                            (int)$lastRunData['items_skipped'],
                            (int)$lastRunData['items_failed']
                        )
                        : '',
                    'hasMessages' => $lastRunMessages !== [],
                    'messageCount' => count($lastRunMessages),
                ],
                'lastRunMessages' => $lastRunMessages,
                'editUrl' => (string)$uriBuilder->buildUriFromRoute('record_edit', [
                    'edit' => [
                        'tx_aiassistant_indexer' => [
                            $uid => 'edit',
                        ],
                    ],
                    'returnUrl' => (string)$backendRequest->getUri(),
                ]),
            ];
        }

        return [
            'indexers' => $indexers,
        ];
    }


    /**
     * Builds source status data for one indexer.
     *
     * @param int $indexerUid Indexer uid.
     * @return array<string, int> Source status.
     */
    private function buildSourceStatus(int $indexerUid): array
    {
        /** @var array<string, int> $status */
        $status = [
            'count' => 0,
            'indexed' => 0,
            'skipped' => 0,
            'failed' => 0,
            'pending' => 0,
            'processing' => 0,
            'unknown' => 0,
            'lastIndexed' => 0,
        ];

        foreach ($this->indexerSourceRepository->findAll() as $source) {
            if (!$source instanceof IndexerSource) {
                continue;
            }

            if ($source->getIndexerUid() !== $indexerUid) {
                continue;
            }

            $status['count']++;
            $status['lastIndexed'] = max($status['lastIndexed'], $this->getLastIndexed($source));

            /** @var string $sourceStatus */
            $sourceStatus = strtolower(trim($this->getStatus($source)));

            if ($sourceStatus === '') {
                $sourceStatus = 'unknown';
            }

            if (!array_key_exists($sourceStatus, $status)) {
                $sourceStatus = 'unknown';
            }

            $status[$sourceStatus]++;
        }

        return $status;
    }


    /**
     * Builds template-friendly data for the last run.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerRun|null $lastRun Last run.
     * @return array<string, mixed> Last run data.
     */
    private function buildLastRunData(?IndexerRun $lastRun): array
    {
        if (!$lastRun instanceof IndexerRun) {
            return [];
        }

        return [
            'uid' => $lastRun->getUid(),
            'status' => $lastRun->getStatus(),
            'started_at' => $lastRun->getStartedAt(),
            'finished_at' => $lastRun->getFinishedAt(),
            'items_processed' => $lastRun->getItemsProcessed(),
            'items_indexed' => $lastRun->getItemsIndexed(),
            'items_skipped' => $lastRun->getItemsSkipped(),
            'items_failed' => $lastRun->getItemsFailed(),
            'items_removed' => $lastRun->getItemsRemoved(),
            'chunks_total' => $lastRun->getChunksTotal(),
            'message' => $lastRun->getMessage(),
        ];
    }


    /**
     * Builds compact messages from the structured last-run details.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerRun|null $lastRun Last run.
     * @return array<int, array{severity:string,badgeClass:string,title:string,text:string}> Message rows.
     */
    private function buildLastRunMessages(?IndexerRun $lastRun): array
    {
        if (!$lastRun instanceof IndexerRun) {
            return [];
        }

        $messageData = $lastRun->getMessageData();
        $details = is_array($messageData['details'] ?? null) ? $messageData['details'] : [];

        /** @var array<int, array{severity:string,badgeClass:string,title:string,text:string}> $messages */
        $messages = [];
        foreach ($details as $key => $detail) {
            if (!is_array($detail)) {
                continue;
            }

            $messages[] = $this->buildRunMessage((string)$key, $detail);
        }

        return $messages;
    }


    /**
     * Builds one template-friendly run message.
     *
     * @param string $key Detail key.
     * @param array<string, mixed> $detail Detail data.
     * @return array{severity:string,badgeClass:string,title:string,text:string} Message row.
     */
    private function buildRunMessage(string $key, array $detail): array
    {
        $severity = $this->resolveDetailSeverity($key, $detail);
        $type = trim((string)($detail['type'] ?? $key));

        if ($type === 'json_duplicate_record_ids') {
            return [
                'severity' => $severity,
                'badgeClass' => $this->resolveSeverityBadgeClass($severity),
                'title' => 'Duplicate JSON record identifiers',
                'text' => $this->buildDuplicateJsonRecordMessage($detail),
            ];
        }

        $filename = trim((string)($detail['filename'] ?? $detail['path'] ?? $detail['local_path'] ?? 'Source'));
        $message = trim((string)($detail['message'] ?? $type));

        return [
            'severity' => $severity,
            'badgeClass' => $this->resolveSeverityBadgeClass($severity),
            'title' => $type !== '' ? $type : 'Indexer message',
            'text' => trim($filename . ($message !== '' ? ': ' . $message : '')),
        ];
    }


    /**
     * Builds a compact duplicate JSON record message.
     *
     * @param array<string, mixed> $detail Detail data.
     * @return string Message text.
     */
    private function buildDuplicateJsonRecordMessage(array $detail): string
    {
        $filename = trim((string)($detail['filename'] ?? $detail['path'] ?? 'JSON file'));
        $field = trim((string)($detail['source_id_field'] ?? 'source_id_field'));
        $duplicates = is_array($detail['duplicates'] ?? null) ? $detail['duplicates'] : [];

        /** @var array<int, string> $parts */
        $parts = [];
        foreach ($duplicates as $duplicate) {
            if (!is_array($duplicate)) {
                continue;
            }

            $id = trim((string)($duplicate['id'] ?? ''));
            $records = is_array($duplicate['records'] ?? null) ? array_map('intval', $duplicate['records']) : [];
            $recordsText = $records !== [] ? implode(', ', $records) : 'n/a';
            $parts[] = sprintf(
                '"%s" in %s records %s',
                $id !== '' ? $id : 'n/a',
                $field,
                $recordsText
            );
        }

        return sprintf(
            '%s: %s. File was not indexed.',
            $filename,
            $parts !== [] ? implode('; ', $parts) : 'duplicate identifiers found'
        );
    }


    /**
     * Resolves a message severity from detail metadata.
     *
     * @param string $key Detail key.
     * @param array<string, mixed> $detail Detail data.
     * @return string Severity.
     */
    private function resolveDetailSeverity(string $key, array $detail): string
    {
        $severity = strtolower(trim((string)($detail['severity'] ?? '')));
        if (in_array($severity, ['error', 'warning', 'info'], true)) {
            return $severity;
        }

        $type = strtolower(trim((string)($detail['type'] ?? $key)));
        if (str_contains($key, 'error') || str_contains($type, 'error')) {
            return 'error';
        }

        if (str_contains($key, 'warning') || str_contains($type, 'warning')) {
            return 'warning';
        }

        return 'info';
    }


    /**
     * Resolves an existing badge class for one severity.
     *
     * @param string $severity Severity.
     * @return string Badge modifier.
     */
    private function resolveSeverityBadgeClass(string $severity): string
    {
        return match ($severity) {
            'error' => 'error',
            'warning' => 'warn',
            default => 'empty',
        };
    }


    /**
     * Returns the normalized status of an indexer source.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerSource $source Indexer source.
     * @return string Source status.
     */
    private function getStatus(IndexerSource $source): string
    {
        if (method_exists($source, 'getStatus')) {
            return (string)$source->getStatus();
        }

        if (method_exists($source, 'getState')) {
            return (string)$source->getState();
        }

        if (method_exists($source, 'getLastError') && trim((string)$source->getLastError()) !== '') {
            return 'failed';
        }

        if ($this->getLastIndexed($source) > 0) {
            return 'indexed';
        }

        return 'pending';
    }


    /**
     * Returns the last indexed timestamp of an indexer source.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerSource $source Indexer source.
     * @return int Last indexed timestamp.
     */
    private function getLastIndexed(IndexerSource $source): int
    {
        if (method_exists($source, 'getLastIndexed')) {
            return (int)$source->getLastIndexed();
        }

        if (method_exists($source, 'getIndexedAt')) {
            return (int)$source->getIndexedAt();
        }

        if (method_exists($source, 'getProcessedAt')) {
            return (int)$source->getProcessedAt();
        }

        return 0;
    }
}
