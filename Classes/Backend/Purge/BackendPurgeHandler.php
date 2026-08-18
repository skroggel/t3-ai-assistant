<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension ai_assistant.
 *
 * For the full copyright information, please read the LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiAssistant\Backend\Purge;

use Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection;
use Madj2k\AiAssistant\Connection\Domain\Repository\VectorStoreConnectionRepository;
use Madj2k\AiCore\Connection\Resolver\VectorStoreConnectorResolver as VectorStoreConnectorRegistry;
use Madj2k\AiCore\Connection\VectorStore\DTO\VectorCollection;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Handles vector collection purge actions.
 */
final class BackendPurgeHandler
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Connection\Domain\Repository\VectorStoreConnectionRepository $vectorStoreConnectionRepository Vector store connection repository.
     * @param \Madj2k\AiCore\Connection\Resolver\VectorStoreConnectorResolver $vectorStoreConnectorRegistry Vector store connector registry.
     * @param \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool Connection pool.
     */
    public function __construct(
        private readonly VectorStoreConnectionRepository $vectorStoreConnectionRepository,
        private readonly VectorStoreConnectorRegistry $vectorStoreConnectorRegistry,
        private readonly ConnectionPool $connectionPool
    ) {
    }


    /**
     * Checks whether the request contains a purge action.
     *
     * @param object $request PSR-7 backend request or Extbase request.
     * @return bool True when supported.
     */
    public function supports(object $request): bool
    {
        return $this->getRequestArgument($request, 'aiassistantBackendAction') === 'purgeVectorCollection';
    }


    /**
     * Handles purge actions.
     *
     * @param object $request PSR-7 backend request or Extbase request.
     * @param array<string, mixed> $state Mutable module state.
     * @return void
     */
    public function handle(object $request, array &$state): void
    {
        if (!$this->supports($request)) {
            return;
        }

        $connectionUid = (int)$this->getRequestArgument($request, 'vectorStoreConnection');
        $collectionName = trim($this->getRequestArgument($request, 'collection'));
        $confirmation = trim($this->getRequestArgument($request, 'confirmCollection'));

        if ($connectionUid <= 0 || $collectionName === '') {
            $this->setResult($state, 'error', $this->translate('templates_backend_config.purge_error_missing_data'));
            return;
        }

        if ($confirmation !== $collectionName) {
            $this->setResult($state, 'error', $this->translate('templates_backend_config.purge_error_confirmation_mismatch'));
            return;
        }

        $connection = $this->vectorStoreConnectionRepository->findByUid($connectionUid);
        if (!$connection instanceof VectorStoreConnection) {
            $this->setResult($state, 'error', $this->translate('templates_backend_config.purge_error_connection_missing', [$connectionUid]));
            return;
        }

        $remoteError = '';
        $remoteMissing = false;
        try {
            $connector = $this->vectorStoreConnectorRegistry->get($connection->getConnectorIdentifier());
            $remoteCollections = $connector->listCollections($connection);
            if (!in_array($collectionName, $remoteCollections, true)) {
                $remoteMissing = true;
            } else {
                $connector->deleteCollection(
                    $connection,
                    new VectorCollection($collectionName, $connection->getVectorSize(), $connection->getDistance())
                );
            }
        } catch (\Throwable $exception) {
            $remoteError = $exception->getMessage();
        }

        $deletedSources = $this->deleteSourceState($connectionUid, $collectionName);

        if ($remoteError === '' && !$remoteMissing) {
            $this->setResult($state, 'ok', $this->translate(
                'templates_backend_config.purge_success',
                [
                $collectionName,
                $connectionUid,
                    $deletedSources,
                ]
            ));
            return;
        }

        if ($remoteMissing) {
            $this->setResult($state, 'warning', $this->translate(
                'templates_backend_config.purge_warning_remote_missing',
                [
                    $collectionName,
                    $connectionUid,
                    $deletedSources,
                ]
            ));
            return;
        }

        $this->setResult($state, 'warning', $this->translate(
            'templates_backend_config.purge_warning_remote_failed',
            [
            $remoteError,
                $deletedSources,
            ]
        ));
    }


    /**
     * Stores a visible module result.
     *
     * @param array<string, mixed> $state Mutable module state.
     * @param string $status Result status.
     * @param string $message Result message.
     * @return void
     */
    private function setResult(array &$state, string $status, string $message): void
    {
        $state['purgeResult'] = [
            'status' => $status,
            'message' => $message,
        ];
    }


    /**
     * Deletes local source state for one vector store connection and collection.
     *
     * @param int $connectionUid Vector store connection uid.
     * @param string $collection Collection.
     * @return int Deleted rows.
     */
    private function deleteSourceState(int $connectionUid, string $collection): int
    {
        $connection = $this->connectionPool->getConnectionForTable('tx_aiassistant_indexer_source');

        return $connection->delete(
            'tx_aiassistant_indexer_source',
            [
                'vector_store_connection' => $connectionUid,
                'collection' => $collection,
            ]
        );
    }


    /**
     * Resolves a request argument from PSR-7, Extbase or raw POST data.
     *
     * @param object $request PSR-7 backend request or Extbase request.
     * @param string $name Argument name.
     * @return string Argument value.
     */
    private function getRequestArgument(object $request, string $name): string
    {
        if ($request instanceof ServerRequestInterface) {
            $parsedBody = $request->getParsedBody();
            if (is_array($parsedBody) && array_key_exists($name, $parsedBody)) {
                return trim((string)$parsedBody[$name]);
            }
        }

        if (method_exists($request, 'hasArgument') && method_exists($request, 'getArgument') && $request->hasArgument($name)) {
            return trim((string)$request->getArgument($name));
        }

        if (array_key_exists($name, $_POST)) {
            return trim((string)$_POST[$name]);
        }

        return '';
    }


    /**
     * Translates a backend module label.
     *
     * @param string $key Language key.
     * @param array<int, mixed> $arguments Translation arguments.
     * @return string Translated label.
     */
    private function translate(string $key, array $arguments = []): string
    {
        $label = LocalizationUtility::translate(
            'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_config.xlf:' . $key,
            null,
            $arguments
        );

        return (string)($label ?? $key);
    }
}
