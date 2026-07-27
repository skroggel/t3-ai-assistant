<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 3
 * of the License, or any later version.
 */

namespace Madj2k\AiAssistant\Backend\Configuration;

use TYPO3\CMS\Core\Registry;

/**
 * Class BackendRegistryService
 *
 * Wraps TYPO3 registry access for the backend module.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class BackendRegistryService
{
    private const string NAMESPACE = 'ai_assistant';


    /**
     * @param \TYPO3\CMS\Core\Registry $registry TYPO3 registry.
     */
    public function __construct(
        private readonly Registry $registry
    ) {
    }


    /**
     * Reads a registry value.
     *
     * @param string $key Registry key.
     * @param mixed $default Default value.
     * @return mixed Registry value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->registry->get(self::NAMESPACE, $key, $default);
    }


    /**
     * Writes a registry value.
     *
     * @param string $key Registry key.
     * @param mixed $value Registry value.
     */
    public function set(string $key, mixed $value): void
    {
        $this->registry->set(self::NAMESPACE, $key, $value);
    }


    /**
     * Removes a registry value.
     *
     * @param string $key Registry key.
     */
    public function remove(string $key): void
    {
        $this->registry->remove(self::NAMESPACE, $key);
    }


    /**
     * Checks whether the OpenAI API key is present.
     *
     * @return bool True when a key is configured.
     */
    public function hasOpenAiApiKey(): bool
    {
        return trim((string)$this->get('openai.apiKey', '')) !== '';
    }
}
