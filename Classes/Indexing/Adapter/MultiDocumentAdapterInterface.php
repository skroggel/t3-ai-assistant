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

namespace Madj2k\AiAssistant\Indexing\Adapter;

use Madj2k\AiAssistant\Indexing\DTO\IndexableDocument;
use Madj2k\AiAssistant\Indexing\DTO\IndexableMetadata;

/**
 * Interface MultiDocumentAdapterInterface
 *
 * Allows adapters to split one source file into multiple indexable documents.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
interface MultiDocumentAdapterInterface
{
    /**
     * Extracts one or more documents from a source file.
     *
     * @param string $path File path.
     * @param \Madj2k\AiAssistant\Indexing\DTO\IndexableMetadata $metadata Base metadata to copy and enrich.
     * @return array<int, \Madj2k\AiAssistant\Indexing\DTO\IndexableDocument> Extracted documents.
     */
    public function extractDocuments(string $path, IndexableMetadata $metadata): array;
}
