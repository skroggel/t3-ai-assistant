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

namespace Madj2k\AiAssistant\Assistant\Frontend;

use Madj2k\AiCore\Assistant\DTO\ChatOptions;

/**
 * Class ChatOptionsResolver
 *
 * Resolves nested TYPO3 plugin settings, the active site language and optional
 * user input into the normalized runtime options expected by AI Core.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final class ChatOptionsResolver
{
    /**
     * Resolves normalized Core chat options.
     *
     * @param array<string,mixed> $settings Plugin settings.
     * @param string $siteLanguage Current TYPO3 site language.
     * @param string $userLanguage Optional language selected by the frontend user.
     * @return \Madj2k\AiCore\Assistant\DTO\ChatOptions Normalized chat options.
     */
    public function resolve(
        array $settings,
        string $siteLanguage,
        string $userLanguage = '',
    ): ChatOptions {
        $languageSettings = is_array($settings['language'] ?? null) ? $settings['language'] : [];
        $accessibilitySettings = is_array($settings['accessibility'] ?? null) ? $settings['accessibility'] : [];
        $configuredLanguage = $this->sanitizeLanguage((string)($languageSettings['responseLanguage'] ?? ''));
        $userSelectionAllowed = (bool)($languageSettings['allowUserSelection'] ?? false);
        $resolvedLanguage = $configuredLanguage;

        if ($resolvedLanguage === '' && $userSelectionAllowed) {
            $resolvedLanguage = $this->sanitizeLanguage($userLanguage);
        }
        if ($resolvedLanguage === '') {
            $resolvedLanguage = $this->sanitizeLanguage($siteLanguage);
        }

        return new ChatOptions(
            responseLanguage: $resolvedLanguage,
            languageCode: $this->extractLanguageCode($resolvedLanguage),
            plainLanguage: (bool)($accessibilitySettings['plainLanguage'] ?? false),
        );
    }


    /**
     * Returns whether the user-facing free language selector should be shown.
     *
     * @param array<string,mixed> $settings Plugin settings.
     * @return bool Whether language selection is available to frontend users.
     */
    public function showLanguageSelector(array $settings): bool
    {
        $languageSettings = is_array($settings['language'] ?? null) ? $settings['language'] : [];

        return (bool)($languageSettings['allowUserSelection'] ?? false)
            && $this->sanitizeLanguage((string)($languageSettings['responseLanguage'] ?? '')) === '';
    }


    /**
     * Converts core options into the nested frontend constructor structure.
     *
     * @param \Madj2k\AiCore\Assistant\DTO\ChatOptions $chatOptions Normalized Core chat options.
     * @return array<string,mixed>
     */
    public function toFrontendOptions(ChatOptions $chatOptions): array
    {
        return [
            'language' => [
                'responseLanguage' => $chatOptions->responseLanguage,
                'languageCode' => $chatOptions->languageCode,
            ],
            'accessibility' => [
                'plainLanguage' => $chatOptions->plainLanguage,
            ],
        ];
    }


    /**
     * Sanitizes a human-readable language value received from configuration or user input.
     *
     * @param string $language Language name or language tag.
     * @return string Sanitized language or an empty string for invalid input.
     */
    private function sanitizeLanguage(string $language): string
    {
        $language = trim((string)preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $language));
        $language = (string)preg_replace('/\s+/u', ' ', $language);

        if ($language === '' || mb_strlen($language) > 80) {
            return '';
        }

        if (preg_match('/^[\p{L}\p{M}\p{N}\s._()\'’\-]+$/u', $language) !== 1) {
            return '';
        }

        return $language;
    }


    /**
     * Extracts a BCP 47 language tag from a plain code or a labelled selection.
     *
     * @param string $language Language name or language tag.
     * @return string Normalized language tag or an empty string when unavailable.
     */
    private function extractLanguageCode(string $language): string
    {
        $candidate = str_replace('_', '-', trim($language));

        if (preg_match('/\(([a-zA-Z]{2,3}(?:-[a-zA-Z0-9]{2,8})*)\)$/', $candidate, $matches) === 1) {
            $candidate = $matches[1];
        }

        if (preg_match('/^[a-zA-Z]{2,3}(?:-[a-zA-Z0-9]{2,8})*$/', $candidate) !== 1) {
            return '';
        }

        $segments = explode('-', $candidate);
        $segments[0] = strtolower($segments[0]);

        return implode('-', $segments);
    }
}
