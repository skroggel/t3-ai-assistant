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

namespace Madj2k\AiAssistant\Indexing\Registry;

use Madj2k\AiAssistant\Exception\IndexingException;
use Madj2k\AiAssistant\Indexing\Indexer\IndexerInterface;

/**
 * Class IndexerRegistry
 *
 * Resolves tagged indexer services by identifier or source type.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class IndexerRegistry
{
    /**
     * Indexers.
     *
     * @var iterable<\Madj2k\AiAssistant\Indexing\Indexer\IndexerInterface>
     */
    protected iterable $indexers;


    /**
     * Constructor.
     *
     * @param iterable<\Madj2k\AiAssistant\Indexing\Indexer\IndexerInterface> $indexers Indexers.
     */
    public function __construct(iterable $indexers)
    {
        $this->indexers = $indexers;
    }


    /**
     * Returns an indexer by identifier.
     *
     * @param string $identifier Indexer identifier.
     * @return \Madj2k\AiAssistant\Indexing\Indexer\IndexerInterface Indexer.
     * @throws \Madj2k\AiAssistant\Exception\IndexingException
     */
    public function get(string $identifier): IndexerInterface
    {
        foreach ($this->indexers as $indexer) {
            if ($indexer->getIdentifier() === $identifier) {
                return $indexer;
            }
        }

        throw new IndexingException(sprintf('No indexer registered for identifier "%s".', $identifier), 1760001001);
    }


    /**
     * Returns all indexers for a source type.
     *
     * @param string $sourceType Source type.
     * @return array<int, \Madj2k\AiAssistant\Indexing\Indexer\IndexerInterface> Indexers.
     */
    public function findBySourceType(string $sourceType): array
    {
        $result = [];
        foreach ($this->indexers as $indexer) {
            if ($indexer->getSourceType() === $sourceType) {
                $result[] = $indexer;
            }
        }

        return $result;
    }


    /**
     * Returns all registered indexers.
     *
     * @return array<int, \Madj2k\AiAssistant\Indexing\Indexer\IndexerInterface> Indexers.
     */
    public function all(): array
    {
        return is_array($this->indexers)
            ? $this->indexers
            : iterator_to_array($this->indexers);
    }


    /**
     * Returns registered identifiers.
     *
     * @return array<int, string> Identifiers.
     */
    public function getIdentifiers(): array
    {
        return array_map(
            static fn (IndexerInterface $indexer): string => $indexer->getIdentifier(),
            $this->all()
        );
    }
}
