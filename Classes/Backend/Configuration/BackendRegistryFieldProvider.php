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

namespace Madj2k\AiAssistant\Backend\Configuration;

/**
 * Class BackendRegistryFieldProvider
 *
 * Provides the remaining central backend configuration fields.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final class BackendRegistryFieldProvider
{
    /**
     * @param iterable<\Madj2k\AiAssistant\Backend\Configuration\BackendRegistryFieldContributorInterface> $fieldContributors
     */
    public function __construct(
        private readonly iterable $fieldContributors = []
    ) {

    }


    /**
     * Returns all editable registry fields.
     *
     * @return array<int, array<string, mixed>> Field definitions.
     */
    public function getFields(): array
    {
        $fields = [
            [
                'key' => 'chat.memory.maxPrompts',
                'label' => 'Memory prompts',
                'description' => 'Maximum number of recent chat prompts that are kept in memory.',
                'type' => 'number',
                'default' => '10',
                'maxLength' => 8,
                'allowDelete' => false,
            ],
            [
                'key' => 'chat.pipelineLog.mode',
                'label' => 'Pipeline log mode',
                'description' => 'Controls how much pipeline logging is written.',
                'type' => 'select',
                'default' => 'errors',
                'maxLength' => 32,
                'allowDelete' => false,
                'options' => [
                    ['value' => 'off', 'label' => 'off'],
                    ['value' => 'errors', 'label' => 'errors'],
                    ['value' => 'verbose', 'label' => 'verbose'],
                ],
            ],
            [
                'key' => 'chat.pipelineLog.writePsrLog',
                'label' => 'Write PSR log',
                'description' => 'Writes pipeline events to the TYPO3 PSR logger in addition to the database log.',
                'type' => 'select',
                'default' => '0',
                'maxLength' => 1,
                'allowDelete' => false,
                'options' => [
                    ['value' => '0', 'label' => 'no'],
                    ['value' => '1', 'label' => 'yes'],
                ],
            ],
            [
                'key' => 'chat.pipelineLog.maskSensitive',
                'label' => 'Mask sensitive values',
                'description' => 'Masks sensitive values before they are written into the pipeline log.',
                'type' => 'select',
                'default' => '1',
                'maxLength' => 1,
                'allowDelete' => false,
                'options' => [
                    ['value' => '0', 'label' => 'no'],
                    ['value' => '1', 'label' => 'yes'],
                ],
            ],
            [
                'key' => 'chat.pipelineLog.maxChars',
                'label' => 'Maximum log characters',
                'description' => 'Maximum number of characters stored for long prompt, response or payload values.',
                'type' => 'number',
                'default' => '12000',
                'maxLength' => 8,
                'allowDelete' => false,
            ],
        ];

        foreach ($this->fieldContributors as $fieldContributor) {
            array_push($fields, ...$fieldContributor->getFields());
        }

        return $fields;
    }
}
