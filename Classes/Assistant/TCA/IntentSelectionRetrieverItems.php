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

namespace Madj2k\AiAssistant\Assistant\TCA;

use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Provides retriever identifiers from the current assistant pipeline.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final readonly class IntentSelectionRetrieverItems
{
    /**
     * Constructor.
     *
     * @param \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool TYPO3 database connection pool.
     */
    public function __construct(
        private ConnectionPool $connectionPool,
    ) {
    }

    /**
     * Adds retriever identifiers to the TCA item list.
     *
     * @param array<string,mixed> $parameters TCA parameters.
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function items(array &$parameters): void
    {
        $assistantProfileUid = $this->resolveAssistantProfileUid($parameters);
        $items = [];
        $identifiers = [];

        if ($assistantProfileUid > 0) {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_aiassistant_assistant_pipeline_step');
            $rows = $queryBuilder
                ->select('title', 'retrieval_identifier')
                ->from('tx_aiassistant_assistant_pipeline_step')
                ->where(
                    $queryBuilder->expr()->eq(
                        'assistant_profile',
                        $queryBuilder->createNamedParameter($assistantProfileUid, ParameterType::INTEGER),
                    ),
                    $queryBuilder->expr()->eq(
                        'type',
                        $queryBuilder->createNamedParameter('retriever'),
                    ),
                    $queryBuilder->expr()->eq(
                        'deleted',
                        $queryBuilder->createNamedParameter(0, ParameterType::INTEGER),
                    ),
                    $queryBuilder->expr()->eq(
                        'hidden',
                        $queryBuilder->createNamedParameter(0, ParameterType::INTEGER),
                    ),
                )
                ->orderBy('sorting')
                ->executeQuery()
                ->fetchAllAssociative();

            foreach ($rows as $row) {
                $identifier = trim((string)($row['retrieval_identifier'] ?? ''));
                if ($identifier === '' || isset($identifiers[$identifier])) {
                    continue;
                }

                $identifiers[$identifier] = true;
                $items[] = [
                    'label' => trim((string)($row['title'] ?? '')) !== ''
                        ? trim((string)$row['title']) . ' (' . $identifier . ')'
                        : $identifier,
                    'value' => $identifier,
                ];
            }
        }

        $parameters['items'] = $items;
    }

    /**
     * Resolves the assistant profile UID from the current TCA row.
     *
     * @param array<string,mixed> $parameters TCA parameters.
     * @return int Assistant profile UID.
     */
    private function resolveAssistantProfileUid(array $parameters): int
    {
        $row = $parameters['row'] ?? $parameters['databaseRow'] ?? [];
        $value = is_array($row) ? ($row['assistant_profile'] ?? 0) : 0;
        if (is_array($value)) {
            $value = reset($value);
        }

        $assistantProfileUid = is_scalar($value) ? (int)$value : 0;
        if ($assistantProfileUid > 0) {
            return $assistantProfileUid;
        }

        $stepUid = is_array($row) ? (int)($row['uid'] ?? 0) : 0;
        if ($stepUid <= 0) {
            return 0;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_aiassistant_assistant_pipeline_step');
        $profileUid = $queryBuilder
            ->select('assistant_profile')
            ->from('tx_aiassistant_assistant_pipeline_step')
            ->where($queryBuilder->expr()->eq(
                'uid',
                $queryBuilder->createNamedParameter($stepUid, ParameterType::INTEGER),
            ))
            ->executeQuery()
            ->fetchOne();

        return is_numeric($profileUid) ? (int)$profileUid : 0;
    }
}
