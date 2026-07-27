<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension ai_assistant.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiAssistant\Connection\VectorStore;

use Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection;
use Madj2k\AiAssistant\Connection\VectorStore\DTO\VectorCollection;
use Madj2k\AiAssistant\Connection\VectorStore\DTO\VectorDeleteResult;
use Madj2k\AiAssistant\Connection\VectorStore\DTO\VectorDocument;
use Madj2k\AiAssistant\Connection\VectorStore\DTO\VectorSearchRequest;
use Madj2k\AiAssistant\Connection\VectorStore\DTO\VectorSearchResult;
use Madj2k\AiAssistant\Connection\VectorStore\DTO\VectorWriteResult;
use Madj2k\AiAssistant\Exception\VectorDatabaseException;

/**
 * Interface VectorStoreConnectorInterface
 *
 * Defines the contract for shared vector store connectors.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
interface VectorStoreConnectorInterface
{
    /**
     * Returns the connector identifier.
     *
     * @return string Connector identifier.
     */
    public function getIdentifier(): string;


    /**
     * Ensures that the collection exists.
     *
     * @param \Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection $connection Vector store connection.
     * @param \Madj2k\AiAssistant\Connection\VectorStore\DTO\VectorCollection $collection Vector collection.
     * @return bool True if collection exists.
     * @throws \Madj2k\AiAssistant\Exception\VectorDatabaseException
     */
    public function ensureCollection(VectorStoreConnection $connection, VectorCollection $collection): bool;


    /**
     * Writes vector documents.
     *
     * @param \Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection $connection Vector store connection.
     * @param \Madj2k\AiAssistant\Connection\VectorStore\DTO\VectorCollection $collection Vector collection.
     * @param array<int, \Madj2k\AiAssistant\Connection\VectorStore\DTO\VectorDocument> $documents Vector documents.
     * @return \Madj2k\AiAssistant\Connection\VectorStore\DTO\VectorWriteResult Write result.
     * @throws \Madj2k\AiAssistant\Exception\VectorDatabaseException
     */
    public function upsert(VectorStoreConnection $connection, VectorCollection $collection, array $documents): VectorWriteResult;


    /**
     * Searches vector documents.
     *
     * @param \Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection $connection Vector store connection.
     * @param \Madj2k\AiAssistant\Connection\VectorStore\DTO\VectorSearchRequest $request Search request.
     * @return array<int, \Madj2k\AiAssistant\Connection\VectorStore\DTO\VectorSearchResult> Search results.
     * @throws \Madj2k\AiAssistant\Exception\VectorDatabaseException
     */
    public function search(VectorStoreConnection $connection, VectorSearchRequest $request): array;


    /**
     * Lists known collection names in the vector store.
     *
     * @param \Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection $connection Vector store connection.
     * @return array<int, string> Collection names.
     * @throws \Madj2k\AiAssistant\Exception\VectorDatabaseException
     */
    public function listCollections(VectorStoreConnection $connection): array;


    /**
     * Deletes vector documents by source hash.
     *
     * @param \Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection $connection Vector store connection.
     * @param \Madj2k\AiAssistant\Connection\VectorStore\DTO\VectorCollection $collection Vector collection.
     * @param string $sourceHash Source hash.
     * @return \Madj2k\AiAssistant\Connection\VectorStore\DTO\VectorDeleteResult Delete result.
     * @throws \Madj2k\AiAssistant\Exception\VectorDatabaseException
     */
    public function deleteBySourceHash(VectorStoreConnection $connection, VectorCollection $collection, string $sourceHash): VectorDeleteResult;


    /**
     * Deletes a collection.
     *
     * @param \Madj2k\AiAssistant\Connection\Domain\Model\VectorStoreConnection $connection Vector store connection.
     * @param \Madj2k\AiAssistant\Connection\VectorStore\DTO\VectorCollection $collection Vector collection.
     * @return \Madj2k\AiAssistant\Connection\VectorStore\DTO\VectorDeleteResult Delete result.
     * @throws \Madj2k\AiAssistant\Exception\VectorDatabaseException
     */
    public function deleteCollection(VectorStoreConnection $connection, VectorCollection $collection): VectorDeleteResult;
}
