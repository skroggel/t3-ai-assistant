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

namespace Madj2k\AiAssistant\Indexing\TCA;

use Madj2k\AiCore\Indexing\Registry\IndexerRegistry;

/**
 * Class IndexerItems
 *
 * Provides registered indexers as TCA select items.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final class IndexerItems
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiCore\Indexing\Registry\IndexerRegistry $indexerRegistry Indexer registry.
     */
    public function __construct(
        protected readonly IndexerRegistry $indexerRegistry
    ) {
    }


    /**
     * Adds registered indexers to the TCA item list.
     *
     * @param array<string, mixed> $parameters TCA parameters.
     * @return void
     */
    public function addItems(array &$parameters): void
    {
        $this->appendItems($parameters);
    }


    /**
     * Adds registered indexers to the TCA item list.
     *
     * @param array<string, mixed> $parameters TCA parameters.
     * @return void
     */
    public function items(array &$parameters): void
    {
        $this->appendItems($parameters);
    }


    /**
     * Appends registered indexers to the given TCA parameters.
     *
     * @param array<string, mixed> $parameters TCA parameters.
     * @return void
     */
    protected function appendItems(array &$parameters): void
    {
        /**
         * @var array<int, array<string, mixed>> $items
         */
        $items = is_array($parameters['items'] ?? null)
            ? $parameters['items']
            : [];

        foreach ($this->indexerRegistry->all() as $indexer) {
            $identifier = trim($indexer->getIdentifier());

            if ($identifier === '' || $this->hasItem($items, $identifier)) {
                continue;
            }

            $items[] = [
                'label' => $this->createLabel($identifier),
                'value' => $identifier,
            ];
        }

        $parameters['items'] = $items;
    }


    /**
     * Checks whether an item already exists.
     *
     * @param array<int, array<string, mixed>> $items Items.
     * @param string $value Value.
     * @return bool Whether the item exists.
     */
    protected function hasItem(array $items, string $value): bool
    {
        foreach ($items as $item) {
            if ((string)($item['value'] ?? '') === $value) {
                return true;
            }
        }

        return false;
    }


    /**
     * Creates a readable label from an identifier.
     *
     * @param string $identifier Identifier.
     * @return string Label.
     */
    protected function createLabel(string $identifier): string
    {
        return ucwords(str_replace(['.', '_', '-'], ' ', $identifier));
    }
}
