<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Madj2k\AiAssistant\Indexing\Service;

use Doctrine\DBAL\ParameterType;
use Madj2k\AiCore\DTO\DocumentMetadata;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Class CategoryMetadataService
 *
 * Adds TYPO3 category metadata for indexable records.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class CategoryMetadataService
{
    /**
     * Constructor.
     *
     * @param \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool Connection pool.
     */
    public function __construct(
        private readonly ConnectionPool $connectionPool
    ) {
    }


    /**
     * Adds category ids and titles to document metadata.
     *
     * @param \Madj2k\AiCore\DTO\DocumentMetadata $metadata Document metadata.
     * @param string $tableName Related record table name.
     * @param int $recordUid Related record uid.
     * @param string $fieldName Category field name.
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function addCategoryMetadata(
        DocumentMetadata $metadata,
        string $tableName,
        int $recordUid,
        string $fieldName = 'categories'
    ): void {
        $categories = $this->fetchCategories($tableName, $recordUid, $fieldName);
        if ($categories === []) {
            return;
        }

        $metadata->addAdditional(
            'category_ids',
            array_values(array_map(
                static fn (array $category): int => (int)$category['uid'],
                $categories
            ))
        );

        $metadata->addAdditional(
            'categories',
            array_values(array_map(
                static fn (array $category): string => (string)$category['title'],
                $categories
            ))
        );
    }


    /**
     * Resolves the sys_file_metadata uid for a FAL file uid.
     *
     * @param int $fileUid FAL file uid.
     * @return int Metadata record uid.
     * @throws \Doctrine\DBAL\Exception
     */
    public function resolveFileMetadataUid(int $fileUid): int
    {
        if ($fileUid <= 0) {
            return 0;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_file_metadata');
        $row = $queryBuilder
            ->select('uid')
            ->from('sys_file_metadata')
            ->where(
                $queryBuilder->expr()->eq(
                    'file',
                    $queryBuilder->createNamedParameter($fileUid, ParameterType::INTEGER)
                )
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();

        if (!is_array($row)) {
            return 0;
        }

        return (int)($row['uid'] ?? 0);
    }


    /**
     * Fetches TYPO3 categories assigned to a record.
     *
     * @param string $tableName Related record table name.
     * @param int $recordUid Related record uid.
     * @param string $fieldName Category field name.
     * @return array<int, array{uid:int,title:string}> Categories.
     * @throws \Doctrine\DBAL\Exception
     */
    private function fetchCategories(string $tableName, int $recordUid, string $fieldName): array
    {
        $tableName = trim($tableName);
        $fieldName = trim($fieldName);
        if ($tableName === '' || $fieldName === '' || $recordUid <= 0) {
            return [];
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_category_record_mm');
        $rows = $queryBuilder
            ->select('category.uid', 'category.title')
            ->from('sys_category_record_mm', 'mm')
            ->join(
                'mm',
                'sys_category',
                'category',
                $queryBuilder->expr()->eq('category.uid', $queryBuilder->quoteIdentifier('mm.uid_local'))
            )
            ->where(
                $queryBuilder->expr()->eq(
                    'mm.uid_foreign',
                    $queryBuilder->createNamedParameter($recordUid, ParameterType::INTEGER)
                ),
                $queryBuilder->expr()->eq(
                    'mm.tablenames',
                    $queryBuilder->createNamedParameter($tableName)
                ),
                $queryBuilder->expr()->eq(
                    'mm.fieldname',
                    $queryBuilder->createNamedParameter($fieldName)
                )
            )
            ->orderBy('mm.sorting_foreign', 'ASC')
            ->addOrderBy('mm.sorting', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        return array_values(array_map(
            static fn (array $row): array => [
                'uid' => (int)($row['uid'] ?? 0),
                'title' => (string)($row['title'] ?? ''),
            ],
            array_filter(
                $rows,
                static fn (array $row): bool => (int)($row['uid'] ?? 0) > 0
                    && trim((string)($row['title'] ?? '')) !== ''
            )
        ));
    }
}
