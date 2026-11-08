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


namespace Madj2k\AiAssistant\Assistant\TCA;

use Madj2k\AiCore\Assistant\Contract\DecisionContract\DecisionContractInterface;

/**
 * Provides registered decision contract parts as TCA checkbox items.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final readonly class DecisionContractItems
{
    /**
     * Constructor.
     *
     * @param iterable<\Madj2k\AiCore\Assistant\Contract\DecisionContract\DecisionContractInterface> $contracts Registered decision contracts.
     */
    public function __construct(private iterable $contracts)
    {
    }

    /**
     * Adds registered decision contracts to the TCA item list.
     *
     * @param array<string,mixed> $parameters TCA parameters.
     * @return void
     */
    public function items(array &$parameters): void
    {
        $items = is_array($parameters['items'] ?? null) ? $parameters['items'] : [];
        foreach ($this->contracts as $contract) {
            if (!$contract instanceof DecisionContractInterface) {
                continue;
            }

            $value = $contract->getIdentifier();
            $alreadyExists = false;
            foreach ($items as $item) {
                $itemValue = is_array($item)
                    ? (string)($item['value'] ?? '')
                    : (method_exists($item, 'getValue') ? (string)$item->getValue() : '');
                if ($itemValue === $value) {
                    $alreadyExists = true;
                    break;
                }
            }
            if ($alreadyExists) {
                continue;
            }

            $items[] = [
                'label' => ucwords(str_replace(['.', '_', '-'], ' ', $contract->getIdentifier())),
                'value' => $value,
            ];
        }

        $parameters['items'] = array_values($items);
    }
}
