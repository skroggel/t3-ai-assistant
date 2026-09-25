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

namespace Madj2k\AiAssistant\Tests\Unit\Configuration;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * TcaRootLevelTest
 *
 * Verifies that configuration records can be stored globally or on pages.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final class TcaRootLevelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $packageManager = $this->createStub(PackageManager::class);
        $packageManager->method('isPackageActive')->willReturn(false);
        ExtensionManagementUtility::setPackageManager($packageManager);
    }


    /**
     * @return array<string, array{string}>
     */
    public static function configurableTableProvider(): array
    {
        return [
            'assistant pipeline step' => ['tx_aiassistant_assistant_pipeline_step'],
            'assistant profile' => ['tx_aiassistant_assistant_profile'],
            'AI connection' => ['tx_aiassistant_connection_ai'],
            'vector database connection' => ['tx_aiassistant_connection_vector_database'],
            'indexer' => ['tx_aiassistant_indexer'],
            'indexer connector' => ['tx_aiassistant_indexer_connector'],
        ];
    }


    /**
     * @param string $table Table name.
     */
    #[DataProvider('configurableTableProvider')]
    public function testConfigurationRecordsCanBeStoredGloballyOrOnPages(string $table): void
    {
        /** @var array<string, mixed> $tca */
        $tca = require dirname(__DIR__, 3) . '/Configuration/TCA/' . $table . '.php';

        self::assertSame(-1, $tca['ctrl']['rootLevel'] ?? null);
    }
}
