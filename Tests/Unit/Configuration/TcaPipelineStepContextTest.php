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
 * Verifies the retrieval-context switch visibility in pipeline-step TCA.
 */
final class TcaPipelineStepContextTest extends TestCase
{
    /**
     * Ensures the switch is offered only on quality-gate records.
     *
     * @return void
     */
    public function testRetrievalContextSwitchIsShownOnlyForQualityGate(): void
    {
        /** @var array<string, mixed> $tca */
        $tca = require dirname(__DIR__, 3) . '/Configuration/TCA/tx_aiassistant_assistant_pipeline_step.php';
        $field = $tca['columns']['include_retrieval_context'] ?? [];

        self::assertSame('FIELD:type:=:quality_gate', $field['displayCond']);
        self::assertStringContainsString('include_retrieval_context', $tca['palettes']['context']['showitem']);
    }
}
