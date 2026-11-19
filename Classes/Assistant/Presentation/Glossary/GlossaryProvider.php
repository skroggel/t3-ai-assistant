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

namespace Madj2k\AiAssistant\Assistant\Presentation\Glossary;

use Doctrine\DBAL\ParameterType;
use Madj2k\AiAssistant\Assistant\Domain\Repository\AssistantProfileRepository;
use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\Glossary\Glossary;
use Madj2k\AiCore\Assistant\Glossary\GlossaryProviderInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Site\SiteFinder;

/**
 * Class GlossaryProvider
 *
 * Resolves the localized glossary for the effective response language.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final class GlossaryProvider implements GlossaryProviderInterface
{
    /**
     * Constructor.
     *
     * @param AssistantProfileRepository $profileRepository Assistant profile repository.
     * @param ConnectionPool $connectionPool TYPO3 database connection pool.
     * @param SiteFinder $siteFinder TYPO3 site/language resolver.
     */
    public function __construct(
        private AssistantProfileRepository $profileRepository,
        private ConnectionPool $connectionPool,
        private SiteFinder $siteFinder,
    ) {
    }

    /**
     * Resolves the localized glossary for the effective response language.
     *
     * @param Context $context Current assistant context.
     * @return Glossary|null Localized glossary or the default-language fallback.
     */
    public function resolve(Context $context): ?Glossary
    {
        $profile = $this->profileRepository->findByUid($context->getAssistant()->getUid());
        $glossaryUid = $profile?->getGlossary()?->getUid() ?? 0;
        if ($glossaryUid <= 0) {
            return null;
        }

        $rows = $this->getGlossaryRows($glossaryUid);
        if ($rows === []) {
            return null;
        }

        $languageId = $this->resolveLanguageId($context, $profile->getUid());
        $selected = null;
        $fallback = null;
        foreach ($rows as $row) {
            if ((int)$row['sys_language_uid'] === 0) {
                $fallback = $row;
            }
            if ((int)$row['sys_language_uid'] === $languageId) {
                $selected = $row;
                break;
            }
        }

        $row = $selected ?? $fallback;
        if (!is_array($row) || trim((string)($row['content'] ?? '')) === '') {
            return null;
        }

        return new Glossary(
            identifier: trim((string)($row['identifier'] ?? '')),
            title: trim((string)($row['title'] ?? '')),
            content: trim((string)$row['content']),
            languageCode: $context->getRequest()->getChatOptions()->languageCode,
        );
    }

    /**
     * Loads the base glossary and its TYPO3 translations.
     *
     * @param int $glossaryUid Base glossary record UID.
     * @return array<int,array<string,mixed>> Glossary database rows.
     */
    private function getGlossaryRows(int $glossaryUid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_aiassistant_glossary');

        return $queryBuilder
            ->select('uid', 'identifier', 'title', 'content', 'sys_language_uid', 'l10n_parent')
            ->from('tx_aiassistant_glossary')
            ->where(
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq(
                        'uid',
                        $queryBuilder->createNamedParameter($glossaryUid, ParameterType::INTEGER),
                    ),
                    $queryBuilder->expr()->eq(
                        'l10n_parent',
                        $queryBuilder->createNamedParameter($glossaryUid, ParameterType::INTEGER),
                    ),
                ),
            )
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * Maps the effective chat language to the TYPO3 site language UID.
     *
     * @param Context $context Current assistant context.
     * @param int $profileUid Assistant profile UID.
     * @return int TYPO3 language UID, or zero for the default language.
     */
    private function resolveLanguageId(Context $context, int $profileUid): int
    {
        $languageCode = strtolower((string)$context->getRequest()->getChatOptions()->languageCode);
        $languageCode = (string)strtok($languageCode, '-');
        if ($languageCode === '') {
            return 0;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_aiassistant_assistant_profile');
        $pid = (int)$queryBuilder
            ->select('pid')
            ->from('tx_aiassistant_assistant_profile')
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($profileUid, ParameterType::INTEGER)))
            ->executeQuery()
            ->fetchOne();

        try {
            $site = $this->siteFinder->getSiteByPageId($pid);
        } catch (\Throwable) {
            return 0;
        }

        foreach ($site->getLanguages() as $language) {
            if (strtolower($language->getLocale()->getLanguageCode()) === $languageCode) {
                return $language->getLanguageId();
            }
        }

        return 0;
    }
}
