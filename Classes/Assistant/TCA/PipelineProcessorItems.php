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
namespace Madj2k\AiAssistant\Assistant\TCA;

use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;
use Madj2k\AiCore\Assistant\Pipeline\Processor\ProcessorInterface;
use Madj2k\AiCore\Assistant\Pipeline\Registry\ProcessorRegistry;

/**
 * Class PipelineProcessorItems
 *
 * Provides registered assistant pipeline processors as TCA select items.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final readonly class PipelineProcessorItems
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Assistant\Pipeline\Registry\ProcessorRegistry $processorRegistry Processor registry.
     */
    public function __construct(
        protected ProcessorRegistry $processorRegistry
    ) {
    }


    /**
     * Adds registered processors to the TCA item list.
     *
     * @param array<string, mixed> $parameters TCA parameters.
     * @return void
     */
    public function addItems(array &$parameters): void
    {
        $this->appendItems($parameters);
    }


    /**
     * Adds registered processors to the TCA item list.
     *
     * @param array<string, mixed> $parameters TCA parameters.
     * @return void
     */
    public function items(array &$parameters): void
    {
        $this->appendItems($parameters);
    }


    /**
     * Appends registered processors to the given TCA parameters.
     *
     * @param array<string, mixed> $parameters TCA parameters.
     * @return void
     */
    protected function appendItems(array &$parameters): void
    {
        $selectedType = $this->resolveSelectedType($parameters);
        $selectedIdentifier = $this->resolveSelectedIdentifier($parameters);

        $items = [];
        $processorsByIdentifier = [];

        foreach ($this->processorRegistry->all() as $processor) {
            if (!$processor instanceof ProcessorInterface) {
                continue;
            }

            $identifier = trim($processor->getIdentifier());
            if ($identifier === '') {
                continue;
            }

            $processorsByIdentifier[$identifier] = $processor;

            if ($selectedType instanceof AssistantPipelineProcessorType && !$processor->supports($selectedType)) {
                continue;
            }

            if ($this->hasItem($items, $identifier)) {
                continue;
            }

            $items[] = [
                'label' => $this->createLabel($identifier),
                'value' => $identifier,
            ];
        }

        if (
            $selectedIdentifier !== ''
            && !$this->hasItem($items, $selectedIdentifier)
            && isset($processorsByIdentifier[$selectedIdentifier])
        ) {
            array_unshift($items, [
                'label' => $this->createInvalidSelectionLabel($selectedIdentifier),
                'value' => $selectedIdentifier,
            ]);
        }

        $parameters['items'] = $items;
    }


    /**
     * Resolves the selected pipeline processor type from the current TCA row.
     *
     * @param array<string, mixed> $parameters TCA parameters.
     * @return \Madj2k\AiAssistant\Assistant\Enum\AssistantPipelineProcessorType|null Selected type.
     */
    protected function resolveSelectedType(array $parameters): ?AssistantPipelineProcessorType
    {
        $row = is_array($parameters['row'] ?? null) ? $parameters['row'] : [];
        $type = $this->normalizeTcaValue($row['type'] ?? null);

        if ($type === '') {
            return null;
        }

        return AssistantPipelineProcessorType::tryFrom($type);
    }


    /**
     * Resolves the currently stored processor identifier from the current TCA row.
     *
     * @param array<string, mixed> $parameters TCA parameters.
     * @return string Selected processor identifier.
     */
    protected function resolveSelectedIdentifier(array $parameters): string
    {
        $row = is_array($parameters['row'] ?? null) ? $parameters['row'] : [];

        return $this->normalizeTcaValue($row['processor_identifier'] ?? null);
    }


    /**
     * Normalizes scalar TCA values that may arrive wrapped in arrays.
     *
     * @param mixed $value Raw TCA value.
     * @return string Normalized value.
     */
    protected function normalizeTcaValue(mixed $value): string
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        return is_scalar($value) ? trim((string)$value) : '';
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


    /**
     * Creates a readable label for a stored processor that does not match the selected type.
     *
     * @param string $identifier Processor identifier.
     * @return string Label.
     */
    protected function createInvalidSelectionLabel(string $identifier): string
    {
        return '[Invalid for selected type] ' . $this->createLabel($identifier);
    }
}
