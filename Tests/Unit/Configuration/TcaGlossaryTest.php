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
 */

namespace Madj2k\AiAssistant\Tests\Unit\Configuration;

use PHPUnit\Framework\TestCase;

/**
 * Verifies the localized glossary TCA configuration.
 */
final class TcaGlossaryTest extends TestCase
{
    /**
     * Ensures TYPO3 localization is used with one complete content field.
     *
     * @return void
     */
    public function testGlossaryUsesTypo3LocalizationAndOneContentField(): void
    {
        /** @var array<string, mixed> $tca */
        $tca = require dirname(__DIR__, 3) . '/Configuration/TCA/tx_aiassistant_glossary.php';

        self::assertSame('sys_language_uid', $tca['ctrl']['languageField']);
        self::assertSame('l10n_parent', $tca['ctrl']['transOrigPointerField']);
        self::assertArrayHasKey('content', $tca['columns']);
        self::assertSame('text', $tca['columns']['content']['config']['type']);
    }
}
