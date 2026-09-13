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
use Madj2k\AiCore\Exception\JsonRecordIdentityException;
use Madj2k\AiCore\Indexing\Resolver\AdapterResolver as AdapterRegistry;
use Madj2k\AiCore\Indexing\Adapter\MultiDocumentAdapterInterface;
use Madj2k\AiCore\Indexing\DTO\IndexableDocument;
use Madj2k\AiCore\DTO\DocumentMetadata;
use Madj2k\AiCore\Indexing\DTO\IndexingRequest;
use Madj2k\AiCore\Indexing\DTO\IndexingResult;
use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig;
use Madj2k\AiAssistant\Indexing\Service\CategoryMetadataService;
use Madj2k\AiAssistant\Indexing\Service\SourceStateService;
use Madj2k\AiAssistant\Indexing\Domain\Repository\IndexerConfigRepository;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * Class FileIndexer
 *
 * Indexes files from configured import paths in cursor-based batches.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final class FileIndexer extends AbstractIndexer
{
    /**
     * Text content adapter registry.
     *
     * @var \Madj2k\AiChat\Indexing\Registry\AdapterRegistry
     */
    private readonly AdapterRegistry $adapterRegistry;


    /**
     * TYPO3 resource factory.
     *
     * @var \TYPO3\CMS\Core\Resource\ResourceFactory
     */
    private readonly ResourceFactory $resourceFactory;


    /**
     * Constructor.
     *
     * @inheritDoc
     * @param \Madj2k\AiCore\Indexing\Resolver\AdapterResolver $adapterRegistry Text content adapter registry.
     * @param \Madj2k\AiAssistant\Indexing\Service\CategoryMetadataService $categoryMetadataService Category metadata service.
     * @param \TYPO3\CMS\Core\Resource\ResourceFactory|null $resourceFactory TYPO3 resource factory.
     */
    public function __construct(
        IndexerConfigRepository          $indexerConfigRepository,
        SourceStateService               $sourceStateService,
        VectorDocumentIndexer            $vectorDocumentIndexer,
        AdapterRegistry                  $adapterRegistry,
        private readonly CategoryMetadataService $categoryMetadataService,
        ?ResourceFactory                 $resourceFactory = null
    ) {
        parent::__construct(
            $indexerConfigRepository,
            $sourceStateService,
            $vectorDocumentIndexer,
        );

        $this->adapterRegistry = $adapterRegistry;
        $this->resourceFactory = $resourceFactory ?? GeneralUtility::makeInstance(ResourceFactory::class);
    }


    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return 'aiassistant.indexer.file';
    }


    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'File indexer';
    }


    /**
     * @inheritDoc
     */
    public function getSourceType(): string
    {
        return 'file';
    }


    /**
     * @inheritDoc
     */
    public function index(IndexingRequest $request): IndexingResult
    {
        $result = new IndexingResult();

        foreach ($this->resolveConfigurations($request->getIndexerUid()) as $configuration) {
            $collection = $this->resolveCollection($configuration, $request->getCollection());
            $importPath = trim($configuration->getImportPath());

            if ($collection === '' || $importPath === '') {
                $result->increaseSkipped();
                continue;
            }

            try {
                $files = $this->resolveFiles($importPath, $configuration->isIncludeSubfolders());
            } catch (\Throwable $exception) {
                $result->increaseSkipped();
                $result->addDetail('file_import_path_error_' . (int)$configuration->getUid(), [
                    'path' => $importPath,
                    'message' => $exception->getMessage(),
                ]);
                continue;
            }

            if ($files === []) {
                $result->increaseSkipped();
                continue;
            }

            foreach ($files as $file) {
                $sourceIdentifier = $file['sourceIdentifier'];
                if ($request->getCursor() !== '' && strcmp($sourceIdentifier, $request->getCursor()) <= 0) {
                    continue;
                }

                if ($this->isLimitReached($request, $result)) {
                    $result->setHasMore(true);
                    break 2;
                }

                $result->setNextCursor($sourceIdentifier);
                $result->increaseProcessed();

                try {
                    $adapter = $this->adapterRegistry->getForPath($file['localPath']);
                    $metadata = $this->buildMetadata(
                        $file,
                        $configuration,
                        $adapter->getIdentifier()
                    );

                    if ($adapter instanceof MultiDocumentAdapterInterface) {
                        foreach ($adapter->extractDocuments($file['localPath'], $metadata) as $document) {
                            $this->indexDocument(
                                $configuration,
                                $document,
                                $request,
                                $result
                            );
                        }
                    } else {
                        $text = $adapter->extract($file['localPath'], $metadata);
                        $this->indexDocument(
                            $configuration,
                            new IndexableDocument($text, $metadata),
                            $request,
                            $result
                        );
                    }
                } catch (JsonRecordIdentityException $exception) {
                    $result->increaseFailed();
                    $result->addDetail('json_record_identity_error_' . $result->getProcessed(), array_merge(
                        [
                            'path' => $file['sourceIdentifier'],
                            'local_path' => $file['localPath'],
                            'filename' => $file['filename'],
                            'message' => $exception->getMessage(),
                            'action' => 'file_failed',
                        ],
                        $exception->getDetails()
                    ));
                } catch (\Throwable $exception) {
                    $result->increaseFailed();
                    $result->addDetail('file_error_' . $result->getProcessed(), [
                        'path' => $file['sourceIdentifier'],
                        'local_path' => $file['localPath'],
                        'message' => $exception->getMessage(),
                    ]);
                }
            }
        }

        return $result;
    }


    /**
     * Builds metadata for an indexed file document.
     *
     * @param array{sourceIdentifier:string,localPath:string,title:string,path:string,filename:string,mtime:int,extension:string,size:int,url:string,storageUid:int,fileUid:int} $file File information.
     * @param \Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig $configuration Indexer configuration.
     * @param string $adapterIdentifier Adapter identifier.
     * @return \Madj2k\AiCore\DTO\DocumentMetadata Metadata.
     */
    private function buildMetadata(
        array $file,
        IndexerConfig $configuration,
        string $adapterIdentifier
    ): DocumentMetadata {
        $metadata = new DocumentMetadata('file', $file['sourceIdentifier']);
        $metadata->setTitle($file['title']);
        $metadata->setPath($file['path']);
        $metadata->setFilename($file['filename']);
        $metadata->setChangedAt($file['mtime']);
        $metadata->addAdditional('adapter', $adapterIdentifier);
        $metadata->addAdditional('extension', $file['extension']);
        $metadata->addAdditional('size', $file['size']);

        if ($file['storageUid'] > 0) {
            $metadata->addAdditional('storage_uid', $file['storageUid']);
        }

        if ($file['fileUid'] > 0) {
            $fileMetadataUid = $this->categoryMetadataService->resolveFileMetadataUid($file['fileUid']);
            $this->categoryMetadataService->addCategoryMetadata($metadata, 'sys_file_metadata', $fileMetadataUid);
        }

        if ($file['url'] !== '') {
            $metadata->setUrl($file['url']);
        }

        foreach ($configuration->getAdditionalMetadataArray() as $key => $value) {
            if (is_string($key) && trim($key) !== '') {
                $metadata->addAdditional($key, $value);
            }
        }

        return $metadata;
    }


    /**
     * Resolves configured import path to local files.
     *
     * Supports classic absolute/local paths and TYPO3 FAL combined identifiers like "1:/user_upload/".
     *
     * @param string $importPath Import path.
     * @param bool $includeSubfolders Whether subfolders are included.
     * @return array<int, array{sourceIdentifier:string,localPath:string,title:string,path:string,filename:string,mtime:int,extension:string,size:int,url:string,storageUid:int,fileUid:int}> Files.
     */
    private function resolveFiles(string $importPath, bool $includeSubfolders): array
    {
        if ($this->isFalCombinedIdentifier($importPath)) {
            return $this->getFalFiles($importPath, $includeSubfolders);
        }

        if (!is_dir($importPath)) {
            return [];
        }

        return $this->getLocalFiles($importPath, $includeSubfolders);
    }


    /**
     * Returns whether a path is a TYPO3 FAL combined identifier.
     *
     * @param string $path Path.
     * @return bool FAL combined identifier flag.
     */
    private function isFalCombinedIdentifier(string $path): bool
    {
        return preg_match('/^\d+:.+/', $path) === 1;
    }


    /**
     * Returns FAL files sorted by stable combined identifier.
     *
     * @param string $combinedIdentifier Folder combined identifier.
     * @param bool $includeSubfolders Whether subfolders are included.
     * @return array<int, array{sourceIdentifier:string,localPath:string,title:string,path:string,filename:string,mtime:int,extension:string,size:int,url:string,storageUid:int,fileUid:int}> Files.
     */
    private function getFalFiles(string $combinedIdentifier, bool $includeSubfolders): array
    {
        $folder = $this->resourceFactory->getFolderObjectFromCombinedIdentifier($combinedIdentifier);
        $storageUid = (int)$folder->getStorage()->getUid();
        $recursive = $includeSubfolders ? true : false;

        /** @var array<int, array{sourceIdentifier:string,localPath:string,title:string,path:string,filename:string,mtime:int,extension:string,size:int,url:string,storageUid:int,fileUid:int}> $files */
        $files = [];

        foreach ($folder->getFiles(0, 0, Folder::FILTER_MODE_USE_OWN_AND_STORAGE_FILTERS, $recursive) as $file) {
            if (!$file instanceof FileInterface) {
                continue;
            }

            $localPath = $file->getForLocalProcessing(false);
            if ($localPath === '' || !is_file($localPath)) {
                continue;
            }

            $fileUid = method_exists($file, 'getUid') ? (int)$file->getUid() : 0;

            $files[] = [
                'sourceIdentifier' => $file->getCombinedIdentifier(),
                'localPath' => $localPath,
                'title' => $file->getName(),
                'path' => $file->getCombinedIdentifier(),
                'filename' => $file->getName(),
                'mtime' => (int)$file->getModificationTime(),
                'extension' => $file->getExtension(),
                'size' => (int)$file->getSize(),
                'url' => (string)($file->getPublicUrl() ?? ''),
                'storageUid' => $storageUid,
                'fileUid' => $fileUid,
            ];
        }

        usort(
            $files,
            static fn (array $left, array $right): int => strcmp($left['sourceIdentifier'], $right['sourceIdentifier'])
        );

        return $files;
    }


    /**
     * Returns local files sorted by stable path.
     *
     * @param string $path Root path.
     * @param bool $includeSubfolders Whether subfolders are included.
     * @return array<int, array{sourceIdentifier:string,localPath:string,title:string,path:string,filename:string,mtime:int,extension:string,size:int,url:string,storageUid:int,fileUid:int}> Files.
     */
    private function getLocalFiles(string $path, bool $includeSubfolders): array
    {
        /** @var array<int, array{sourceIdentifier:string,localPath:string,title:string,path:string,filename:string,mtime:int,extension:string,size:int,url:string,storageUid:int,fileUid:int}> $files */
        $files = [];

        /** @var \RecursiveIteratorIterator<\RecursiveDirectoryIterator> $iterator */
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo instanceof \SplFileInfo || !$fileInfo->isFile()) {
                continue;
            }

            if (!$includeSubfolders && dirname($fileInfo->getPathname()) !== rtrim($path, DIRECTORY_SEPARATOR)) {
                continue;
            }

            $pathname = $fileInfo->getRealPath() ?: $fileInfo->getPathname();
            $files[] = [
                'sourceIdentifier' => $pathname,
                'localPath' => $pathname,
                'title' => $fileInfo->getBasename(),
                'path' => $pathname,
                'filename' => $fileInfo->getBasename(),
                'mtime' => (int)$fileInfo->getMTime(),
                'extension' => $fileInfo->getExtension(),
                'size' => (int)$fileInfo->getSize(),
                'url' => $this->buildPublicUrlForLocalPath($pathname),
                'storageUid' => 0,
                'fileUid' => 0,
            ];
        }

        usort(
            $files,
            static fn (array $left, array $right): int => strcmp($left['sourceIdentifier'], $right['sourceIdentifier'])
        );

        return $files;
    }


    /**
     * Builds a public URL for files below the TYPO3 public path.
     *
     * @param string $filePath File path.
     * @return string Public URL.
     */
    private function buildPublicUrlForLocalPath(string $filePath): string
    {
        /** @var string $publicPath */
        $publicPath = rtrim(Environment::getPublicPath(), DIRECTORY_SEPARATOR);

        if ($publicPath === '' || !str_starts_with($filePath, $publicPath . DIRECTORY_SEPARATOR)) {
            return '';
        }

        /** @var string $relativePath */
        $relativePath = substr($filePath, strlen($publicPath));

        return '/' . ltrim(str_replace(DIRECTORY_SEPARATOR, '/', $relativePath), '/');
    }
}
