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

namespace Madj2k\AiAssistant\Indexing\Registry;

use Madj2k\AiAssistant\Exception\IndexingException;
use Madj2k\AiAssistant\Indexing\Adapter\AdapterInterface;

/**
 * Class AdapterRegistry
 *
 * Resolves tagged text content adapters.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class AdapterRegistry
{
    /**
     * Adapters.
     *
     * @var iterable<\Madj2k\AiAssistant\Indexing\Adapter\AdapterInterface>
     */
    protected iterable $adapters;


    /**
     * Constructor. Set via Service.yaml with tags
     *
     * @param iterable<\Madj2k\AiAssistant\Indexing\Adapter\AdapterInterface> $adapters Adapters.
     */
    public function __construct(iterable $adapters)
    {
        $this->adapters = $adapters;
    }


    /**
     * Returns an adapter for a path.
     *
     * @param string $path File path.
     * @return \Madj2k\AiAssistant\Indexing\Adapter\AdapterInterface Adapter
     * @throws \Madj2k\AiAssistant\Exception\IndexingException
     */
    public function getForPath(string $path): AdapterInterface
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->supports($path)) {
                return $adapter;
            }
        }

        throw new IndexingException(sprintf('No text content adapter registered for "%s".', $path), 1760001002);
    }


    /**
     * Returns all registered adapters.
     *
     * @return array<string, \Madj2k\AiAssistant\Indexing\Adapter\AdapterInterface> Adapters.
     */
    public function all(): array
    {
        return is_array($this->adapters)
            ? $this->adapters
            : iterator_to_array($this->adapters, false);
    }
}
