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
                'label' => 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_config.xlf:registry.chat_memory_max_prompts.label',
                'description' => 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_config.xlf:registry.chat_memory_max_prompts.description',
                'type' => 'number',
                'default' => '10',
                'maxLength' => 8,
                'allowDelete' => false,
            ],
            [
                'key' => 'chat.pipelineLog.mode',
                'label' => 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_config.xlf:registry.pipeline_log_mode.label',
                'description' => 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_config.xlf:registry.pipeline_log_mode.description',
                'type' => 'select',
                'default' => 'errors',
                'maxLength' => 32,
                'allowDelete' => false,
                'options' => [
                    ['value' => 'off', 'label' => 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_config.xlf:registry.pipeline_log_mode.off'],
                    ['value' => 'errors', 'label' => 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_config.xlf:registry.pipeline_log_mode.errors'],
                    ['value' => 'verbose', 'label' => 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_config.xlf:registry.pipeline_log_mode.verbose'],
                ],
            ],
            [
                'key' => 'chat.pipelineLog.writePsrLog',
                'label' => 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_config.xlf:registry.pipeline_log_write_psr.label',
                'description' => 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_config.xlf:registry.pipeline_log_write_psr.description',
                'type' => 'select',
                'default' => '0',
                'maxLength' => 1,
                'allowDelete' => false,
                'options' => [
                    ['value' => '0', 'label' => 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_config.xlf:registry.no'],
                    ['value' => '1', 'label' => 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_config.xlf:registry.yes'],
                ],
            ],
            [
                'key' => 'chat.pipelineLog.maskSensitive',
                'label' => 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_config.xlf:registry.pipeline_log_mask_sensitive.label',
                'description' => 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_config.xlf:registry.pipeline_log_mask_sensitive.description',
                'type' => 'select',
                'default' => '1',
                'maxLength' => 1,
                'allowDelete' => false,
                'options' => [
                    ['value' => '0', 'label' => 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_config.xlf:registry.no'],
                    ['value' => '1', 'label' => 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_config.xlf:registry.yes'],
                ],
            ],
            [
                'key' => 'chat.pipelineLog.maxChars',
                'label' => 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_config.xlf:registry.pipeline_log_max_chars.label',
                'description' => 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_config.xlf:registry.pipeline_log_max_chars.description',
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
