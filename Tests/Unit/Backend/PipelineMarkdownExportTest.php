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

namespace Madj2k\AiAssistant\Tests\Unit\Backend;

use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile;
use Madj2k\AiCore\Assistant\Export\PipelineMarkdownExporter;
use Madj2k\AiCore\Assistant\UIComponents\Definition;
use PHPUnit\Framework\TestCase;

/**
 * Class PipelineMarkdownExportTest
 *
 * Tests that the export contains the assistant configuration and UI definitions.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant\Tests\Unit\Backend
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final class PipelineMarkdownExportTest extends TestCase
{
    public function testExportsUiComponentConfiguration(): void
    {
        $profile = new AssistantProfile();
        $profile->setTitle('Test assistant');
        $profile->setAssistantLabel('Assistant');
        $profile->setIdentityPrompt('Be helpful.');

        $markdown = (new PipelineMarkdownExporter())->export($profile, [
            new Definition(
                'link',
                'Inline link',
                '<a href="{{url}}">{{label}}</a>',
                [],
                ['label' => ['type' => 'string']],
                ['label', 'url'],
                'custom-link',
                'Helpful link',
                true,
            ),
        ]);

        self::assertStringContainsString('## UI Components', $markdown);
        self::assertStringContainsString('Identifier: `link`', $markdown);
        self::assertStringContainsString('Helpful link', $markdown);
        self::assertStringContainsString('custom-link', $markdown);
        self::assertStringContainsString('<a href="{{url}}">{{label}}</a>', $markdown);
    }
}
