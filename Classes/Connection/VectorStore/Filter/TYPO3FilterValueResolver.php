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

namespace Madj2k\AiAssistant\Connection\VectorStore\Filter;

use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Connection\VectorStore\Filter\DTO\FilterCondition;
use Madj2k\AiCore\Connection\VectorStore\Filter\DTO\FilterGroup;
use Madj2k\AiCore\Connection\VectorStore\Filter\FilterValueResolverInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * Class TYPO3FilterValueResolver
 *
 * Resolves TYPO3 frontend filter placeholders.
 *
 * Supported placeholders include `###FE_LANG###` for the current ISO language code.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final class TYPO3FilterValueResolver implements FilterValueResolverInterface
{
    /**
     * @param \Madj2k\AiCore\Connection\VectorStore\Filter\DTO\FilterGroup|null $filter
     * @param \Madj2k\AiCore\Assistant\Context\Context $context
     * @return \Madj2k\AiCore\Connection\VectorStore\Filter\DTO\FilterGroup|null
     */
    public function resolve(?FilterGroup $filter, Context $context): ?FilterGroup
    {
        if ($filter === null) {
            return null;
        }

        $languageCode = $this->resolveLanguageCode($context);
        $conditions = array_map(
            static fn (FilterCondition $condition): FilterCondition => new FilterCondition(
                $condition->field,
                $condition->operator,
                $this->replaceValue($condition->value, $languageCode),
            ),
            $filter->conditions,
        );

        return new FilterGroup($filter->conjunction, $conditions);
    }

    private function replaceValue(mixed $value, string $languageCode): mixed
    {
        if (is_array($value)) {
            return array_map(fn (mixed $item): mixed => $this->replaceValue($item, $languageCode), $value);
        }

        return is_string($value)
            ? str_replace('###FE_LANG###', $languageCode, $value)
            : $value;
    }

    /**
     * @param \Madj2k\AiCore\Assistant\Context\Context $context
     * @return string
     */
    private function resolveLanguageCode(Context $context): string
    {
        $language = $context->getRequest()->getServerRequest()?->getAttribute('language');
        if (!$language instanceof SiteLanguage) {
            return '';
        }

        return $language->getLocale()->getLanguageCode();
    }
}
