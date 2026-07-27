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

namespace Madj2k\AiAssistant\Assistant\Context\Request;

/**
 * Class History
 *
 * Provides controlled access to the already visible messages.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final readonly class History
{
    /**
     * @var array<int,array{role:string,content:string}>
     */
    private array $messages;


    /**
     * Constructor.
     *
     * @param array<int,array<string,string>> $messages Visible history messages.
     */
    public function __construct(array $messages)
    {
        /** @var array<int,array{role:string,content:string}> $normalizedMessages */
        $normalizedMessages = [];
        foreach ($messages as $message) {
            $role = trim((string)($message['role'] ?? ''));
            $content = trim((string)($message['content'] ?? ''));
            if ($role !== '' && $content !== '') {
                $normalizedMessages[] = [
                    'role' => $role,
                    'content' => $content,
                ];
            }
        }

        $this->messages = $normalizedMessages;
    }


    /**
     * Returns all visible history messages.
     *
     * @return array<int,array{role:string,content:string}>
     */
    public function all(): array
    {
        return $this->messages;
    }


    /**
     * Returns the last history messages.
     *
     * @param int $limit Maximum number of messages.
     * @return array<int,array{role:string,content:string}>
     */
    public function last(int $limit): array
    {
        if ($limit <= 0) {
            return [];
        }

        return array_slice($this->messages, -$limit);
    }
}
