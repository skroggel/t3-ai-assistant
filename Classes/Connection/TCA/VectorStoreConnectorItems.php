<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension ai_assistant.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiAssistant\Connection\TCA;

use Madj2k\AiAssistant\Connection\Registry\VectorStoreConnectorRegistry;

/**
 * Class VectorStoreConnectorItems
 *
 * Provides registered vector store connector options for TCA select fields.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class VectorStoreConnectorItems
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Connection\Registry\VectorStoreConnectorRegistry $vectorStoreConnectorRegistry Vector store connector registry.
     */
    public function __construct(
        protected readonly VectorStoreConnectorRegistry $vectorStoreConnectorRegistry
    ) {
    }


    /**
     * Adds registered vector store connectors to the TCA item list.
     *
     * @param array<string, mixed> $parameters TCA parameters.
     * @return void
     */
    public function addItems(array &$parameters): void
    {
        $this->appendItems($parameters);
    }


    /**
     * Adds registered vector store connectors to the TCA item list.
     *
     * @param array<string, mixed> $parameters TCA parameters.
     * @return void
     */
    public function items(array &$parameters): void
    {
        $this->appendItems($parameters);
    }


    /**
     * Appends registered vector store connectors to the given TCA parameters.
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

        foreach (array_keys($this->vectorStoreConnectorRegistry->all()) as $identifier) {
            /**
             * @var string $identifier
             */
            $identifier = trim((string)$identifier);

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
