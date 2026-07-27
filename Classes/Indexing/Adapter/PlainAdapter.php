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

use Madj2k\AiAssistant\Indexing\DTO\IndexableMetadata;

/**
 * Class PlainTextContentAdapter
 *
 * Extracts text from plain text files.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class PlainAdapter implements AdapterInterface
{
    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return 'aiassistant.text.plain';
    }


    /**
     * @inheritDoc
     */
    public function getSupportedExtensions(): array
    {
        return ['txt', 'text', 'md', 'markdown', 'csv'];
    }


    /**
     * @inheritDoc
     */
    public function supports(string $path): bool
    {
        return in_array(strtolower((string)pathinfo($path, PATHINFO_EXTENSION)), $this->getSupportedExtensions(), true);
    }


    /**
     * @inheritDoc
     */
    public function extract(string $path, IndexableMetadata $metadata): string
    {
        return trim((string)file_get_contents($path));
    }
}
