<?php
declare(strict_types=1);

namespace Madj2k\AiAssistant\Config;

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

use Madj2k\AiAssistant\Exception\AppException;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class Config
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class Config
{
    private const string REGISTRY_NAMESPACE = 'ai_assistant';

    /**
     * @var array<string, mixed>|null
     */
    private static ?array $settings = null;

    /**
     * init loads TypoScript settings into memory.
     *
     * @return void
     */
    public static function init(): void
    {
        if (self::$settings !== null) {
            return;
        }

        self::$settings = [];

        try {
            /** @var ConfigurationManagerInterface $configurationManager */
            $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
            $settings = $configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'AiAssistant',
                'Chat'
            );

            if (is_array($settings)) {
                self::$settings = $settings;
            }
        } catch (\Throwable) {
            self::$settings = [];
        }

        if (!self::$settings) {
            $settings = self::getTypoScriptSettingsFromTsfe();
            if ($settings) {
                self::$settings = $settings;
            }
        }
    }

    /**
     * getTypoScriptSettingsFromTsfe tries to read settings from TSFE setup.
     *
     * @return array<string,mixed>
     */
    private static function getTypoScriptSettingsFromTsfe(): array
    {
        $tsfe = $GLOBALS['TSFE'] ?? null;
        if (!$tsfe instanceof TypoScriptFrontendController) {
            return [];
        }
        $setup = $tsfe->tmpl?->setup ?? null;
        if (!is_array($setup)) {
            return [];
        }
        /** @var TypoScriptService $tsService */
        $tsService = GeneralUtility::makeInstance(TypoScriptService::class);
        $plain = $tsService->convertTypoScriptArrayToPlainArray($setup);
        $settings = $plain['plugin']['tx_aiassistant_chat']['settings'] ?? [];
        return is_array($settings) ? $settings : [];
    }

    /**
     * get reads config by dot path, with Registry overriding TypoScript.
     *
     * @param string $path Dot-notated setting path.
     * @param mixed $default Default value when missing.
     * @return mixed The resolved configuration value.
     * @throws AppException When the setting is missing and no default is given.
     */
    public static function get(string $path, mixed $default = null): mixed
    {
        self::init();

        [$registryFound, $registryValue] = self::getRegistryValue($path);
        if ($registryFound) {
            return self::resolveValue($registryValue);
        }

        [$settingsFound, $settingsValue] = self::getSettingValue($path);
        if ($settingsFound) {
            return self::resolveValue($settingsValue);
        }

        if (func_num_args() > 1) {
            return $default;
        }

        throw new AppException(
            "Missing configuration for '{$path}'. Set TypoScript setting " .
            "'plugin.tx_aiassistant_chat.settings." . $path .
            "' or Registry key '" . self::REGISTRY_NAMESPACE . ":" . $path . "'."
        );
    }

    /**
     * getRegistryValue resolves a value from the Registry.
     *
     * @param string $path Registry key path.
     * @return array{0: bool, 1: mixed} Tuple of found flag and value.
     */
    private static function getRegistryValue(string $path): array
    {
        try {
            /** @var Registry $registry */
            $registry = GeneralUtility::makeInstance(Registry::class);
            $value = $registry->get(self::REGISTRY_NAMESPACE, $path, null);
        } catch (\Throwable) {
            return [false, null];
        }

        if ($value === null || $value === '') {
            return [false, null];
        }

        return [true, $value];
    }


    /**
     * getSettingValue resolves a value from TypoScript settings.
     *
     * @param string $path Dot-notated setting path.
     * @return array{0: bool, 1: mixed} Tuple of found flag and value.
     */
    private static function getSettingValue(string $path): array
    {
        $parts = array_filter(explode('.', $path), static fn (string $part) => $part !== '');
        $cursor = self::$settings ?? [];

        foreach ($parts as $part) {
            if (!is_array($cursor) || !array_key_exists($part, $cursor)) {
                return [false, null];
            }
            $cursor = $cursor[$part];
        }

        if ($cursor === null || $cursor === '') {
            return [false, null];
        }

        if (is_string($cursor) && self::isUnresolvedTypoScriptPlaceholder($cursor)) {
            return [false, null];
        }

        return [true, $cursor];
    }

    /**
     * resolveValue normalizes special value forms (EXT: paths, placeholders).
     *
     * @param mixed $value Raw value.
     * @return mixed Resolved value.
     */
    private static function resolveValue(mixed $value): mixed
    {
        if (is_array($value)) {
            $resolved = [];
            foreach ($value as $key => $item) {
                $resolvedItem = self::resolveValue($item);
                if ($resolvedItem === null || $resolvedItem === '') {
                    continue;
                }
                $resolved[$key] = $resolvedItem;
            }

            return $resolved;
        }

        if (is_string($value) && self::isUnresolvedTypoScriptPlaceholder($value)) {
            return null;
        }

        if (is_string($value) && str_starts_with($value, 'EXT:')) {
            return GeneralUtility::getFileAbsFileName($value);
        }

        return $value;
    }

    /**
     * isUnresolvedTypoScriptPlaceholder checks for unresolved TS placeholders.
     *
     * @param string $value Raw value.
     * @return bool True when the value is a placeholder.
     */
    private static function isUnresolvedTypoScriptPlaceholder(string $value): bool
    {
        return (bool)preg_match('/^\{\$[^}]+\}$/', trim($value));
    }
}
