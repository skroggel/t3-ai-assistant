<?php
declare(strict_types=1);

namespace Madj2k\AiAssistant\Tests\Unit\Assistant\Presentation\Rules;

use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep;
use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile;
use Madj2k\AiCore\Assistant\Export\PipelineMarkdownExporter;
use Madj2k\AiCore\Assistant\Presentation\Rules\PresentationRule;
use PHPUnit\Framework\TestCase;

final class PresentationRuleUsageTest extends TestCase
{
    public function testOneStableRuleCanBeReferencedByMultipleStepsAndExportedOnce(): void
    {
        $profile = new AssistantProfile();
        $profile->setTitle('Shared prompts');

        $answer = new AssistantPipelineStep();
        $answer->setTitle('Answer');
        $answer->setPresentationRules('shared.rules');
        $quality = new AssistantPipelineStep();
        $quality->setTitle('Quality');
        $quality->setPresentationRules('shared.rules');
        $profile->addChatPipelineStep($answer);
        $profile->addChatPipelineStep($quality);

        $markdown = (new PipelineMarkdownExporter())->export($profile, [], [
            new PresentationRule('shared.rules', 'Use the shared rules.'),
        ]);

        self::assertSame(['shared.rules'], $answer->getPresentationRuleIdentifiers());
        self::assertSame(['shared.rules'], $quality->getPresentationRuleIdentifiers());
        self::assertSame(1, substr_count($markdown, 'Use the shared rules.'));
        self::assertStringContainsString('Presentation rule references: `shared.rules`', $markdown);
        self::assertStringNotContainsString('uid', strtolower($markdown));
        self::assertStringNotContainsString('tstamp', strtolower($markdown));
    }

    public function testPresentationRuleSelectionModesAreNormalized(): void
    {
        $step = new AssistantPipelineStep();

        self::assertSame('inherit', $step->getPresentationRulesMode());

        $step->setPresentationRulesMode('replace');
        self::assertSame('replace', $step->getPresentationRulesMode());

        $step->setPresentationRulesMode('extend');
        self::assertSame('extend', $step->getPresentationRulesMode());

        $step->setPresentationRulesMode('invalid');
        self::assertSame('inherit', $step->getPresentationRulesMode());
    }

    public function testProfileProvidesPresentationRuleIdentifiers(): void
    {
        $profile = new AssistantProfile();
        $profile->setPresentationRules(' shared.rules, answer.format, shared.rules ');

        self::assertSame(['shared.rules', 'answer.format', 'shared.rules'], $profile->getPresentationRuleIdentifiers());
    }
}
