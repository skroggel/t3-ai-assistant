<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension ai_assistant.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiAssistant\Indexing\Indexer;

use Madj2k\AiAssistant\Indexing\DTO\IndexingRequest;
use Madj2k\AiAssistant\Indexing\DTO\IndexingResult;

/**
 * Interface IndexerInterface
 *
 * Defines the contract for executable indexers.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
interface IndexerInterface
{
    /**
     * Returns the unique indexer identifier used by configuration records and commands.
     *
     * @return string Indexer identifier.
     */
    public function getIdentifier(): string;


    /**
     * Returns the human readable indexer label.
     *
     * @return string Indexer label.
     */
    public function getLabel(): string;


    /**
     * Returns the supported source type.
     *
     * @return string Source type.
     */
    public function getSourceType(): string;


    /**
     * Executes the indexer.
     *
     * @param \Madj2k\AiAssistant\Indexing\DTO\IndexingRequest $request Indexing request.
     * @return \Madj2k\AiAssistant\Indexing\DTO\IndexingResult Indexing result.
     */
    public function index(IndexingRequest $request): IndexingResult;
}
