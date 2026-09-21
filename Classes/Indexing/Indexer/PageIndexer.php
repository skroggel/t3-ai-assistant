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


namespace Madj2k\AiAssistant\Indexing\Indexer;

use Madj2k\AiCore\Indexing\VectorDocumentIndexer;
use Madj2k\AiCore\DTO\DocumentMetadata;
use Madj2k\AiCore\Indexing\DTO\IndexableDocument;
use Madj2k\AiCore\Indexing\DTO\IndexingRequest;
use Madj2k\AiCore\Indexing\DTO\IndexingResult;
use Doctrine\DBAL\ArrayParameterType;
use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig;
use Madj2k\AiAssistant\Indexing\Service\CategoryMetadataService;
use Madj2k\AiAssistant\Indexing\Service\SourceStateService;
use Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerConfigRepository;
use Madj2k\AiAssistant\Indexing\Utility\AdditionalFieldParserUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PageIndexer
 *
 * Indexes TYPO3 pages, content elements and configured page-related table fields in cursor-based batches.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final class PageIndexer extends AbstractIndexer
{
    /**
     * Database columns cached per table for CLI-safe field validation.
     *
     * @var array<string, array<string, true>>
     */
    private array $tableColumns = [];


    /**
     * Constructor.
     *
     * @param \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool Connection pool.
     * @param \Madj2k\AiAssistant\Indexing\Utility\AdditionalFieldParserUtility $additionalFieldParserUtility Additional content field parser.
     * @param \Madj2k\AiAssistant\Indexing\Service\CategoryMetadataService $categoryMetadataService Category metadata service.
     */
    public function __construct(
        IndexerConfigRepository $indexerConfigRepository,
        SourceStateService $indexerSourceStateService,
        VectorDocumentIndexer $vectorDocumentIndexer,
        private readonly ConnectionPool $connectionPool,
        private readonly AdditionalFieldParserUtility $additionalFieldParserUtility,
        private readonly CategoryMetadataService $categoryMetadataService
    ) {
        parent::__construct(
            $indexerConfigRepository,
            $indexerSourceStateService,
            $vectorDocumentIndexer,
        );
    }


    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return 'aiassistant.indexer.page';
    }


    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'Page indexer';
    }


    /**
     * @inheritDoc
     */
    public function getSourceType(): string
    {
        return 'page';
    }


    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\Exception
     */
    public function index(IndexingRequest $request): IndexingResult
    {
        $result = new IndexingResult();
        foreach ($this->resolveConfigurations($request->getIndexerUid()) as $configuration) {
            $collection = $this->resolveCollection($configuration, $request->getCollection());
            if ($collection === '') {
                $result->increaseSkipped();
                continue;
            }

            /** @var array<string, array<int, string>> $additionalFieldsByTable */
            $additionalFieldsByTable = $this->additionalFieldParserUtility->parse(
                $configuration->getAdditionalContentFields()
            );

            $pageFields = $this->getConfiguredFieldsForTable(
                'pages',
                array_merge(
                    $this->splitList($configuration->getPageFields(), ['title', 'description', 'keywords']),
                    $additionalFieldsByTable['pages'] ?? []
                )
            );

            $contentFields = $this->getConfiguredFieldsForTable(
                'tt_content',
                array_merge(
                    $this->splitList($configuration->getContentFields(), ['header', 'bodytext']),
                    $additionalFieldsByTable['tt_content'] ?? []
                )
            );

            $contentTypes = $this->splitList($configuration->getContentTypes(), []);
            $relatedFieldsByTable = $this->getRelatedFieldsByTable($additionalFieldsByTable);

            /** @var array<int, int>|null $allowedPageUids */
            $allowedPageUids = null;

            /** @var array<int, int> $rootPageUids */
            $rootPageUids = $this->resolveRootPageUids($configuration);
            if ($rootPageUids !== []) {
                $allowedPageUids = $this->fetchVisiblePageTreeUids($rootPageUids);

                if ($allowedPageUids === []) {
                    $result->increaseSkipped();
                    continue;
                }
            }

            $limit = ($request->getLimit() ?? 100) + 1;

            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
            $queryBuilder
                ->select(...$this->getPageSelectFields($pageFields))
                ->from('pages')
                ->where(
                    $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0)),
                    $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0))
                )
                ->orderBy('uid', 'ASC')
                ->setMaxResults($limit);

            if ($request->getCursor() !== '') {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->gt('uid', $queryBuilder->createNamedParameter((int)$request->getCursor()))
                );
            }

            if (is_array($allowedPageUids)) {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->in(
                        'uid',
                        $queryBuilder->createNamedParameter($allowedPageUids, ArrayParameterType::INTEGER)
                    )
                );
            }

            /** @var array<int, array<string, mixed>> $pages */
            $pages = $queryBuilder->executeQuery()->fetchAllAssociative();

            foreach ($pages as $page) {
                if ($this->isLimitReached($request, $result)) {
                    $result->setHasMore(true);
                    break 2;
                }

                $sourceIdentifier = (string)(int)$page['uid'];
                $result->setNextCursor($sourceIdentifier);
                $result->increaseProcessed();

                /** @var array<int, string> $content */
                $content = [];

                /** @var array<int, array<string, mixed>> $relatedSources */
                $relatedSources = [];

                foreach ($pageFields as $field) {
                    if (isset($page[$field]) && trim((string)$page[$field]) !== '') {
                        $content[] = trim(strip_tags((string)$page[$field]));
                    }
                }

                foreach ($this->fetchTableContent('tt_content', (int)$page['uid'], $contentFields, $contentTypes) as $contentRow) {
                    if (trim($contentRow['text']) !== '') {
                        $content[] = $contentRow['text'];
                    }

                    $relatedSources[] = $contentRow['source'];
                }

                foreach ($relatedFieldsByTable as $table => $fields) {
                    foreach ($this->fetchTableContent($table, (int)$page['uid'], $fields, []) as $contentRow) {
                        if (trim($contentRow['text']) !== '') {
                            $content[] = $contentRow['text'];
                        }

                        $relatedSources[] = $contentRow['source'];
                    }
                }

                $text = trim(implode("\n\n", $content));
                if ($text === '') {
                    $result->increaseSkipped();
                    continue;
                }

                $metadata = $this->buildMetadata(
                    $page,
                    $configuration,
                    $pageFields,
                    $contentFields,
                    $relatedFieldsByTable,
                    $relatedSources
                );

                $this->indexDocument(
                    $configuration,
                    new IndexableDocument($text, $metadata),
                    $request,
                    $result
                );
            }
        }

        return $result;
    }


    /**
     * Resolves configured root page uids.
     *
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration Indexer configuration.
     * @return array<int, int> Root page uids.
     */
    private function resolveRootPageUids(IndexerConfig $configuration): array
    {
        /** @var array<int, int> $rootPageUids */
        $rootPageUids = [];

        foreach ($this->splitList($configuration->getRootPages(), []) as $rootPageUid) {
            $rootPageUid = (int)$rootPageUid;

            if ($rootPageUid > 0) {
                $rootPageUids[] = $rootPageUid;
            }
        }

        return array_values(array_unique($rootPageUids));
    }


    /**
     * Fetches visible root pages and all visible subpages.
     *
     * A configured root page includes the page itself and every visible child page below it.
     *
     * @param array<int, int> $rootPageUids Root page uids.
     * @return array<int, int> Page uids.
     * @throws \Doctrine\DBAL\Exception
     */
    private function fetchVisiblePageTreeUids(array $rootPageUids): array
    {
        /** @var array<int, int> $pageUids */
        $pageUids = [];

        /** @var array<int, int> $pendingPageUids */
        $pendingPageUids = array_values(array_unique(array_filter($rootPageUids)));

        while ($pendingPageUids !== []) {
            /** @var array<int, int> $visiblePageUids */
            $visiblePageUids = $this->fetchVisiblePagesByUid($pendingPageUids);

            /** @var array<int, int> $newPageUids */
            $newPageUids = [];

            foreach ($visiblePageUids as $visiblePageUid) {
                if (!in_array($visiblePageUid, $pageUids, true)) {
                    $pageUids[] = $visiblePageUid;
                    $newPageUids[] = $visiblePageUid;
                }
            }

            if ($newPageUids === []) {
                break;
            }

            $pendingPageUids = $this->fetchVisibleChildPageUids($newPageUids);
        }

        sort($pageUids);

        return $pageUids;
    }


    /**
     * Fetches visible pages by uid.
     *
     * @param array<int, int> $pageUids Page uids.
     * @return array<int, int> Visible page uids.
     * @throws \Doctrine\DBAL\Exception
     */
    private function fetchVisiblePagesByUid(array $pageUids): array
    {
        $pageUids = array_values(array_unique(array_filter(array_map('intval', $pageUids))));

        if ($pageUids === []) {
            return [];
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder
            ->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($pageUids, ArrayParameterType::INTEGER)
                ),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0))
            )
            ->orderBy('uid', 'ASC');

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $queryBuilder->executeQuery()->fetchAllAssociative();

        return array_values(array_map(
            static fn (array $row): int => (int)($row['uid'] ?? 0),
            $rows
        ));
    }


    /**
     * Fetches visible direct child pages for the given parent pages.
     *
     * @param array<int, int> $parentPageUids Parent page uids.
     * @return array<int, int> Child page uids.
     * @throws \Doctrine\DBAL\Exception
     */
    private function fetchVisibleChildPageUids(array $parentPageUids): array
    {
        $parentPageUids = array_values(array_unique(array_filter(array_map('intval', $parentPageUids))));

        if ($parentPageUids === []) {
            return [];
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder
            ->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->in(
                    'pid',
                    $queryBuilder->createNamedParameter($parentPageUids, ArrayParameterType::INTEGER)
                ),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0))
            )
            ->orderBy('uid', 'ASC');

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $queryBuilder->executeQuery()->fetchAllAssociative();

        return array_values(array_map(
            static fn (array $row): int => (int)($row['uid'] ?? 0),
            $rows
        ));
    }


    /**
     * Builds metadata for an indexed page document.
     *
     * @param array<string, mixed> $page Page row.
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration Indexer configuration.
     * @param array<int, string> $pageFields Page fields.
     * @param array<int, string> $contentFields Content fields.
     * @param array<string, array<int, string>> $relatedFieldsByTable Related fields by table.
     * @param array<int, array<string, mixed>> $relatedSources Related sources.
     * @return \Madj2k\AiCore\DTO\DocumentMetadata Metadata.
     */
    private function buildMetadata(
        array $page,
        IndexerConfig $configuration,
        array $pageFields,
        array $contentFields,
        array $relatedFieldsByTable,
        array $relatedSources
    ): DocumentMetadata {

        $metadata = new DocumentMetadata('page', 'pages:' . (int)$page['uid']);
        $metadata->setTitle((string)($page['title'] ?? ''));
        $metadata->setPageId((int)$page['uid']);
        $languageId = (int)($page['sys_language_uid'] ?? 0);
        $metadata->setLanguageId($languageId);
        $metadata->setChangedAt((int)($page['SYS_LASTCHANGED'] ?: $page['tstamp'] ?: 0));
        $this->categoryMetadataService->addCategoryMetadata($metadata, 'pages', (int)$page['uid']);
        $keywords = $this->normalizeKeywords((string)($page['keywords'] ?? ''));
        if ($keywords !== []) {
            $metadata->addAdditional('keywords', $keywords);
        }

        $metadata->addAdditional('doktype', (int)($page['doktype'] ?? 0));
        $metadata->addAdditional('slug', (string)($page['slug'] ?? ''));
        $metadata->addAdditional('indexed_tables', $this->getIndexedTables($relatedFieldsByTable));
        $metadata->addAdditional('indexed_fields', array_merge(
            [
                'pages' => $pageFields,
                'tt_content' => $contentFields,
            ],
            $relatedFieldsByTable
        ));
        $metadata->addAdditional('related_sources', $relatedSources);

        // generate link to page
        try {
            $siteFinder  = GeneralUtility::makeInstance(SiteFinder::class);
            $site = $siteFinder->getSiteByPageId((int)$page['uid']);
            $siteLanguage = $site->getLanguageById($languageId);
            $metadata->setLanguage($siteLanguage->getLocale()->getLanguageCode());
            $router = $site->getRouter();
            $metadata->setUrl((string) $router->generateUri((int)$page['uid']));
        } catch (\Exception $e) {
            // nothing
        }

        foreach ($configuration->getAdditionalMetadataArray() as $key => $value) {
            if (is_string($key) && trim($key) !== '') {
                $metadata->addAdditional($key, $value);
            }
        }

        return $metadata;
    }


    /**
     * Fetches content text for records related to one page via pid.
     *
     * @param string $table Table name.
     * @param int $pageUid Page uid.
     * @param array<int, string> $fields Content fields.
     * @param array<int, string> $contentTypes Content types.
     * @return array<int, array{text: string, source: array<string, mixed>}> Content rows.
     * @throws \Doctrine\DBAL\Exception
     */
    private function fetchTableContent(string $table, int $pageUid, array $fields, array $contentTypes): array
    {
        $fields = $this->getConfiguredFieldsForTable($table, $fields);
        if ($fields === [] || !$this->isTableNameValid($table) || !$this->fieldExists($table, 'pid')) {
            return [];
        }

        /** @var array<int, string> $selectFields */
        $selectFields = array_unique(array_merge(['uid'], $fields));

        if ($table === 'tt_content' && $this->fieldExists($table, 'CType')) {
            $selectFields[] = 'CType';
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $queryBuilder
            ->select(...array_unique($selectFields))
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageUid))
            );

        if ($this->fieldExists($table, 'deleted')) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0))
            );
        }

        if ($this->fieldExists($table, 'hidden')) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0))
            );
        }

        if ($table === 'tt_content' && $contentTypes !== [] && $this->fieldExists($table, 'CType')) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->in('CType', $queryBuilder->createNamedParameter($contentTypes, ArrayParameterType::STRING))
            );
        }

        if ($this->fieldExists($table, 'sorting')) {
            $queryBuilder->orderBy('sorting', 'ASC');
        } else {
            $queryBuilder->orderBy('uid', 'ASC');
        }

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $queryBuilder->executeQuery()->fetchAllAssociative();

        /** @var array<int, array{text: string, source: array<string, mixed>}> $contentRows */
        $contentRows = [];
        foreach ($rows as $row) {
            /** @var array<int, string> $texts */
            $texts = [];

            foreach ($fields as $field) {
                if (isset($row[$field]) && trim((string)$row[$field]) !== '') {
                    $texts[] = trim(strip_tags((string)$row[$field]));
                }
            }

            $contentRows[] = [
                'text' => trim(implode("\n", $texts)),
                'source' => [
                    'table' => $table,
                    'uid' => (int)($row['uid'] ?? 0),
                    'fields' => $fields,
                    'type' => (string)($row['CType'] ?? ''),
                ],
            ];
        }

        return $contentRows;
    }


    /**
     * Returns the page select fields.
     *
     * @param array<int, string> $pageFields Page fields.
     * @return array<int, string> Select fields.
     */
    private function getPageSelectFields(array $pageFields): array
    {
        return array_values(array_unique(array_merge(
            ['uid', 'pid', 'title', 'SYS_LASTCHANGED', 'tstamp', 'doktype', 'slug', 'sys_language_uid', 'keywords'],
            $pageFields
        )));
    }


    /**
     * Normalizes TYPO3 page keywords for metadata output.
     *
     * @param string $keywords Raw page keywords.
     * @return array<int, string> Normalized keywords.
     */
    private function normalizeKeywords(string $keywords): array
    {
        $keywords = trim(strip_tags($keywords));
        if ($keywords === '') {
            return [];
        }

        return array_values(array_unique(array_filter(
            array_map(
                'trim',
                preg_split('/[,;\r\n]+/', $keywords) ?: []
            ),
            static fn (string $keyword): bool => $keyword !== ''
        )));
    }


    /**
     * Returns configured fields grouped by related table.
     *
     * @param array<string, array<int, string>> $additionalFieldsByTable Additional fields by table.
     * @return array<string, array<int, string>> Related fields by table.
     */
    private function getRelatedFieldsByTable(array $additionalFieldsByTable): array
    {
        unset($additionalFieldsByTable['pages'], $additionalFieldsByTable['tt_content']);

        /** @var array<string, array<int, string>> $relatedFieldsByTable */
        $relatedFieldsByTable = [];

        foreach ($additionalFieldsByTable as $table => $fields) {
            $fields = $this->getConfiguredFieldsForTable($table, $fields);
            if ($fields !== [] && $this->fieldExists($table, 'pid')) {
                $relatedFieldsByTable[$table] = $fields;
            }
        }

        return $relatedFieldsByTable;
    }


    /**
     * Returns valid configured fields for a table.
     *
     * @param string $table Table name.
     * @param array<int, string> $fields Fields.
     * @return array<int, string> Valid fields.
     */
    private function getConfiguredFieldsForTable(string $table, array $fields): array
    {
        if (!$this->isTableNameValid($table)) {
            return [];
        }

        /** @var array<int, string> $validFields */
        $validFields = [];

        foreach ($fields as $field) {
            $field = trim($field);
            if ($field !== '' && $this->isFieldNameValid($field) && $this->fieldExists($table, $field)) {
                $validFields[] = $field;
            }
        }

        return array_values(array_unique($validFields));
    }


    /**
     * Returns indexed tables.
     *
     * @param array<string, array<int, string>> $relatedFieldsByTable Related fields by table.
     * @return array<int, string> Indexed tables.
     */
    private function getIndexedTables(array $relatedFieldsByTable): array
    {
        return array_values(array_unique(array_merge(
            ['pages', 'tt_content'],
            array_keys($relatedFieldsByTable)
        )));
    }


    /**
     * Checks if a table field exists.
     *
     * @param string $table Table name.
     * @param string $field Field name.
     * @return bool Field exists.
     */
    private function fieldExists(string $table, string $field): bool
    {
        if (!$this->isTableNameValid($table) || !$this->isFieldNameValid($field)) {
            return false;
        }

        if (!array_key_exists($table, $this->tableColumns)) {
            try {
                $columns = $this->connectionPool
                    ->getConnectionForTable($table)
                    ->createSchemaManager()
                    ->listTableColumns($table);

                $this->tableColumns[$table] = array_fill_keys(
                    array_map(
                        static fn ($column): string => $column->getName(),
                        $columns
                    ),
                    true
                );
            } catch (\Throwable) {
                $this->tableColumns[$table] = [];
            }
        }

        return isset($this->tableColumns[$table][$field]);
    }


    /**
     * Checks if a table name is safe for direct query builder usage.
     *
     * @param string $table Table name.
     * @return bool Table name is valid.
     */
    private function isTableNameValid(string $table): bool
    {
        return preg_match('/^[a-zA-Z0-9_]+$/', $table) === 1;
    }


    /**
     * Checks if a field name is safe for direct query builder usage.
     *
     * @param string $field Field name.
     * @return bool Field name is valid.
     */
    private function isFieldNameValid(string $field): bool
    {
        return preg_match('/^[a-zA-Z0-9_]+$/', $field) === 1;
    }


    /**
     * Splits a comma separated list.
     *
     * @param string $value Value.
     * @param array<int, string> $default Default.
     * @return array<int, string> Values.
     */
    private function splitList(string $value, array $default): array
    {
        $items = array_values(array_filter(array_map('trim', explode(',', $value))));

        return $items !== [] ? $items : $default;
    }
}
