<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension ai_assistant.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiAssistant\Assistant\TCA;

use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Class PipelineCollectionItems
 *
 * Provides collections from the effective vector store connection as TCA items.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final readonly class PipelineCollectionItems
{
    /**
     * Constructor.
     *
     * @param ConnectionPool $connectionPool TYPO3 database connection pool.
     */
    public function __construct(
        private ConnectionPool $connectionPool,
    ) {
    }


    /**
     * Adds available vector collections to the TCA item list.
     *
     * @param array<string, mixed> $parameters TCA parameters.
     * @return void
     */
    public function items(array &$parameters): void
    {
        $parameters['items'] = [[
            'label' => 'LLL:EXT:ai_assistant/Resources/Private/Language/locallang_tx_aiassistant_assistant_pipeline_step.xlf:tx_aiassistant_assistant_pipeline_step.retrieval_collection.default',
            'value' => '',
        ]];

        $row = is_array($parameters['row'] ?? null) ? $parameters['row'] : [];
        $connectionUid = $this->normalizeUid($row['retrieval_vector_store_connection'] ?? 0);
        if ($connectionUid <= 0) {
            $profileUid = $this->normalizeUid($row['assistant_profile'] ?? 0);
            if ($profileUid <= 0) {
                return;
            }

            $profileQuery = $this->connectionPool->getQueryBuilderForTable('tx_aiassistant_assistant_profile');
            $connectionUid = (int)$profileQuery
                ->select('vector_store_connection')
                ->from('tx_aiassistant_assistant_profile')
                ->where($profileQuery->expr()->eq('uid', $profileQuery->createNamedParameter($profileUid, \Doctrine\DBAL\ParameterType::INTEGER)))
                ->executeQuery()
                ->fetchOne();
        }
        if ($connectionUid <= 0) {
            return;
        }

        $connectionQuery = $this->connectionPool->getQueryBuilderForTable('tx_aiassistant_connection_vector_database');
        $connection = $connectionQuery
            ->select('default_collection', 'collections')
            ->from('tx_aiassistant_connection_vector_database')
            ->where($connectionQuery->expr()->eq('uid', $connectionQuery->createNamedParameter($connectionUid, \Doctrine\DBAL\ParameterType::INTEGER)))
            ->executeQuery()
            ->fetchAssociative();
        if (!is_array($connection)) {
            return;
        }

        $collections = preg_split('/[\r\n,]+/', (string)($connection['collections'] ?? '')) ?: [];
        $collections[] = (string)($connection['default_collection'] ?? '');
        $collections = array_values(array_unique(array_filter(array_map('trim', $collections))));

        foreach ($collections as $collection) {
            $parameters['items'][] = ['label' => $collection, 'value' => $collection];
        }
    }

    /**
     * Normalizes a TCA relation value to its numeric UID.
     *
     * @param mixed $value TCA relation value.
     * @return int Relation UID.
     */
    private function normalizeUid(mixed $value): int
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        return is_scalar($value) ? (int)$value : 0;
    }
}
