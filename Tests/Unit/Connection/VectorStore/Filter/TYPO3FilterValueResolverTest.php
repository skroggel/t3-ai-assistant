<?php
declare(strict_types=1);

namespace Madj2k\AiAssistant\Tests\Unit\Connection\VectorStore\Filter;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Uri;
use Madj2k\AiAssistant\Connection\VectorStore\Filter\TYPO3FilterValueResolver;
use Madj2k\AiCore\Assistant\Context\Answer\AnswerState;
use Madj2k\AiCore\Assistant\Context\Assistant\AssistantContext;
use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\Context\Request\History;
use Madj2k\AiCore\Assistant\Context\Request\Request;
use Madj2k\AiCore\Assistant\Context\Retrieval\RetrievalResult;
use Madj2k\AiCore\Assistant\Context\Trace\ProcessingTrace;
use Madj2k\AiCore\Connection\VectorStore\Filter\DTO\FilterCondition;
use Madj2k\AiCore\Connection\VectorStore\Filter\DTO\FilterGroup;
use Madj2k\AiCore\Connection\VectorStore\Filter\DTO\FilterOperator;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * Class TYPO3FilterValueResolverTest
 *
 * Verifies TYPO3 frontend language placeholder resolution in vector filters.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final class TYPO3FilterValueResolverTest extends TestCase
{
    /**
     * Verifies replacement of the ISO language code placeholder.
     *
     * @return void
     */
    public function testReplacesIsoLanguageCode(): void
    {
        $filter = new FilterGroup(conditions: [
            new FilterCondition('meta.language', FilterOperator::Equals, '###FE_LANG###'),
        ]);

        $resolved = (new TYPO3FilterValueResolver())->resolve($filter, $this->contextWithLanguage(3, 'de-DE'));

        self::assertNotNull($resolved);
        self::assertSame('de', $resolved->conditions[0]->value);
    }

    /**
     * Verifies replacement of the numeric TYPO3 language uid placeholder.
     *
     * @return void
     */
    public function testReplacesLanguageUid(): void
    {
        $filter = new FilterGroup(conditions: [
            new FilterCondition('meta.language_id', FilterOperator::Equals, '###FE_LANG_UID###'),
        ]);

        $resolved = (new TYPO3FilterValueResolver())->resolve($filter, $this->contextWithLanguage(3, 'de-DE'));

        self::assertNotNull($resolved);
        self::assertSame('3', $resolved->conditions[0]->value);
    }

    /**
     * Verifies replacement in nested filter values.
     *
     * @return void
     */
    public function testReplacesLanguagePlaceholdersInArrayValues(): void
    {
        $filter = new FilterGroup(conditions: [
            new FilterCondition(
                'meta.language',
                FilterOperator::In,
                ['###FE_LANG###', '###FE_LANG_UID###'],
            ),
        ]);

        $resolved = (new TYPO3FilterValueResolver())->resolve($filter, $this->contextWithLanguage(3, 'de-DE'));

        self::assertNotNull($resolved);
        self::assertSame(['de', '3'], $resolved->conditions[0]->value);
    }

    /**
     * Verifies deterministic fallback values when no frontend language exists.
     *
     * @return void
     */
    public function testUsesEmptyLanguageCodeAndZeroUidWithoutSiteLanguage(): void
    {
        $filter = new FilterGroup(conditions: [
            new FilterCondition('meta.language', FilterOperator::Equals, '###FE_LANG###'),
            new FilterCondition('meta.language_id', FilterOperator::Equals, '###FE_LANG_UID###'),
        ]);

        $resolved = (new TYPO3FilterValueResolver())->resolve($filter, $this->contextWithoutLanguage());

        self::assertNotNull($resolved);
        self::assertSame('', $resolved->conditions[0]->value);
        self::assertSame('0', $resolved->conditions[1]->value);
    }

    /**
     * Creates a pipeline context with a TYPO3 frontend language.
     *
     * @param int $languageId TYPO3 language uid.
     * @param string $locale Locale identifier.
     * @return Context
     */
    private function contextWithLanguage(int $languageId, string $locale): Context
    {
        $language = new SiteLanguage($languageId, $locale, new Uri('https://example.test/'), []);
        $request = new Request(
            serverRequest: (new ServerRequest('GET', 'https://example.test/'))
                ->withAttribute('language', $language),
        );

        return new Context(
            new AssistantContext(),
            $request,
            new History([]),
            new RetrievalResult(),
            new AnswerState(),
            new ProcessingTrace(),
        );
    }

    /**
     * Creates a pipeline context without a TYPO3 frontend language.
     *
     * @return Context
     */
    private function contextWithoutLanguage(): Context
    {
        return new Context(
            new AssistantContext(),
            new Request(),
            new History([]),
            new RetrievalResult(),
            new AnswerState(),
            new ProcessingTrace(),
        );
    }
}
