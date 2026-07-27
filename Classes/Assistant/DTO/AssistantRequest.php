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


namespace Madj2k\AiAssistant\Assistant\DTO;

use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class ChatTurnRequest
 *
 * Immutable input for one frontend chat turn.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final readonly class AssistantRequest
{
    /**
     * Constructor.
     *
     * @param string $query User query.
     * @param int $startTimestamp Frontend page timestamp.
     * @param \Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile $assistantProfile Active assistant profile selected in the plugin.
     * @param string $chatIdentifier Frontend conversation identifier.
     * @param \Psr\Http\Message\ServerRequestInterface|null $serverRequest Current server request.
     * @param array<string,mixed> $runtimeSettings Runtime settings provided by the frontend plugin.
     */
    public function __construct(
        public string $query,
        public int $startTimestamp,
        public AssistantProfile $assistantProfile,
        public string $chatIdentifier,
        public ?ServerRequestInterface $serverRequest,
        public array $runtimeSettings = [],
    ) {
    }
}
