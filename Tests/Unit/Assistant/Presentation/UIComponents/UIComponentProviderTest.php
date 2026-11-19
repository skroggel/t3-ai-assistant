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

namespace Madj2k\AiAssistant\Tests\Unit\Assistant\Presentation\UIComponents;

use Madj2k\AiAssistant\Assistant\Domain\Model\UIComponent;
use Madj2k\AiAssistant\Assistant\Domain\Repository\AssistantProfileRepository;
use Madj2k\AiAssistant\Assistant\Domain\Repository\UIComponentRepository;
use Madj2k\AiAssistant\Assistant\Presentation\UIComponents\UIComponentProvider;
use Madj2k\AiCore\Assistant\Presentation\UIComponents\Defaults\Provider as DefaultProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Verifies TYPO3 UI component tag-name mapping into Core definitions.
 */
final class UIComponentProviderTest extends TestCase
{
    /**
     * Ensures preset definitions retain the configured outer tag name.
     *
     * @return void
     */
    public function testPresetDefinitionUsesConfiguredTagName(): void
    {
        $record = new UIComponent();
        $record->setIdentifier('custom-container');
        $record->setPreset('container');
        $record->setRenderMode('container');
        $record->setTagName('custom-container');

        $provider = new UIComponentProvider(
            $this->createStub(AssistantProfileRepository::class),
            $this->createStub(UIComponentRepository::class),
            new DefaultProvider(),
        );
        $method = new ReflectionMethod($provider, 'createDefinition');

        $definition = $method->invoke($provider, $record);

        self::assertSame('custom-container', $definition->tagName);
        self::assertSame('container', $definition->renderMode);
        self::assertSame('custom-container', $definition->identifier);
    }

    /**
     * Ensures the domain model trims the configured tag name.
     *
     * @return void
     */
    public function testModelNormalizesTagName(): void
    {
        $record = new UIComponent();
        $record->setTagName(' custom-slider ');

        self::assertSame('custom-slider', $record->getTagName());
    }
}
