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
 * Verifies the UI component tag-name migration in TCA.
 */
final class TcaUiComponentTest extends TestCase
{
    /**
     * Ensures the TCA exposes tag_name rather than element_name.
     *
     * @return void
     */
    public function testUsesTagNameInsteadOfElementName(): void
    {
        /** @var array<string, mixed> $tca */
        $tca = require dirname(__DIR__, 3) . '/Configuration/TCA/tx_aiassistant_ui_component.php';
        $showItem = (string)($tca['types']['1']['showitem'] ?? '');

        self::assertArrayHasKey('tag_name', $tca['columns']);
        self::assertArrayNotHasKey('element_name', $tca['columns']);
        self::assertStringContainsString('tag_name', $showItem);
        self::assertStringNotContainsString('element_name', $showItem);
    }
}
