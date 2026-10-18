<?php
declare(strict_types=1);

namespace Madj2k\AiAssistant\Tests\Unit\Assistant\Presentation\Rules;

use Madj2k\AiAssistant\Assistant\Domain\Model\PresentationRule as PresentationRuleRecord;
use Madj2k\AiAssistant\Assistant\Domain\Repository\PresentationRuleRepository;
use Madj2k\AiAssistant\Assistant\Presentation\Rules\PresentationRuleProvider;
use PHPUnit\Framework\TestCase;

final class PresentationRuleProviderTest extends TestCase
{
    public function testResolvesRecordByStableIdentifier(): void
    {
        $record = new PresentationRuleRecord();
        $record->setIdentifier('shared.rules');
        $record->setTitle('Shared rules');
        $record->setContent('Use the shared rules.');
        $repository = $this->createMock(PresentationRuleRepository::class);
        $repository->expects(self::once())->method('findOneBy')->with(['identifier' => 'shared.rules'])->willReturn($record);

        $rule = (new PresentationRuleProvider($repository))->resolve(' shared.rules ');

        self::assertNotNull($rule);
        self::assertSame('shared.rules', $rule->identifier);
        self::assertSame('Use the shared rules.', $rule->content);
    }
}
