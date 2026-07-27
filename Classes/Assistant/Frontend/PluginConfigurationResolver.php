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

namespace Madj2k\AiAssistant\Assistant\Frontend;

use TYPO3\CMS\Core\Service\FlexFormService;

/**
 * Class PluginConfigurationResolver
 *
 * Reads the plugin configuration from the current content element.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final readonly class PluginConfigurationResolver
{
    /**
     * Constructor.
     *
     * @param \TYPO3\CMS\Core\Service\FlexFormService $flexFormService FlexForm service.
     */
    public function __construct(
        private FlexFormService $flexFormService,
    ) {
    }


    /**
     * Resolves chat plugin configuration from tt_content data.
     *
     * @param array<string,mixed> $contentData Current content element data.
     * @return \Madj2k\AiAssistant\Assistant\Frontend\PluginConfiguration
     */
    public function resolve(array $contentData): PluginConfiguration
    {
        return new PluginConfiguration(
            (int)($contentData['uid'] ?? 0),
            $this->resolveAssistantProfileUid($contentData),
            $this->resolveRuntimeSettings($contentData)
        );
    }


    /**
     * Resolves the selected assistant profile uid.
     *
     * @param array<string,mixed> $contentData Current content element data.
     * @return int
     */
    private function resolveAssistantProfileUid(array $contentData): int
    {
        $flexFormValue = trim((string)$this->resolveFlexFormValue($contentData, 'settings.assistantProfile'));
        if ($flexFormValue !== '') {
            return (int)$flexFormValue;
        }

        return (int)($contentData['tx_aiassistant_assistant_profile'] ?? 0);
    }


    /**
     * Resolves runtime settings from the FlexForm settings namespace.
     *
     * @param array<string,mixed> $contentData Current content element data.
     * @return array<string,mixed>
     */
    private function resolveRuntimeSettings(array $contentData): array
    {
        /**
         * @var array<string,mixed> $data
         */
        $data = $this->resolveFlexFormData($contentData);

        /**
         * @var mixed $settings
         */
        $settings = $data['settings'] ?? [];

        if (!is_array($settings)) {
            return [];
        }

        unset($settings['assistantProfile']);

        return $this->filterRuntimeSettings($settings);
    }


    /**
     * Filters runtime settings recursively.
     *
     * @param array<string,mixed> $settings Runtime settings.
     * @return array<string,mixed>
     */
    private function filterRuntimeSettings(array $settings): array
    {
        /**
         * @var array<string,mixed> $filtered
         */
        $filtered = [];

        foreach ($settings as $key => $value) {
            if (!is_string($key) || $key === '') {
                continue;
            }

            if (is_array($value)) {
                $nested = $this->filterRuntimeSettings($value);
                if ($nested !== []) {
                    $filtered[$key] = $nested;
                }

                continue;
            }

            if (is_scalar($value)) {
                $value = trim((string)$value);
                if ($value !== '') {
                    $filtered[$key] = $value;
                }
            }
        }

        return $filtered;
    }


    /**
     * Resolves one FlexForm value.
     *
     * @param array<string,mixed> $contentData Current content element data.
     * @param string $path Dot-separated path.
     * @return string
     */
    private function resolveFlexFormValue(array $contentData, string $path): string
    {
        /**
         * @var array<string,mixed> $data
         */
        $data = $this->resolveFlexFormData($contentData);

        /**
         * @var mixed $value
         */
        $value = $data;

        foreach (explode('.', $path) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return '';
            }
            $value = $value[$segment];
        }

        return is_scalar($value) ? (string)$value : '';
    }


    /**
     * Resolves FlexForm data from the current content element.
     *
     * @param array<string,mixed> $contentData Current content element data.
     * @return array<string,mixed>
     */
    private function resolveFlexFormData(array $contentData): array
    {
        $flexForm = trim((string)($contentData['pi_flexform'] ?? ''));
        if ($flexForm === '') {
            return [];
        }

        /**
         * @var array<string,mixed> $data
         */
        $data = $this->flexFormService->convertFlexFormContentToArray($flexForm);

        return $data;
    }
}
