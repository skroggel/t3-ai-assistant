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

use Madj2k\AiCore\Assistant\Contract\ContractRegistry;

/**
 * Provides registered response contracts as TCA select items.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final readonly class ContractItems
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiCore\Assistant\Contract\ContractRegistry $contractRegistry Contract registry.
     */
    public function __construct(private ContractRegistry $contractRegistry)
    {
    }

    /**
     * Adds registered contracts to the TCA item list.
     *
     * @param array<string,mixed> $parameters TCA parameters.
     * @return void
     */
    public function items(array &$parameters): void
    {
        $parameters['items'] = [];
        foreach ($this->contractRegistry->all() as $contract) {
            if ($contract->getIdentifier() === 'pipeline_decision') {
                continue;
            }

            $parameters['items'][] = [
                'label' => ucwords(str_replace(['.', '_', '-'], ' ', $contract->getIdentifier())),
                'value' => $contract->getIdentifier(),
            ];
        }
    }
}
