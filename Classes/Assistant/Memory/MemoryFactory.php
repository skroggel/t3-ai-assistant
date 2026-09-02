<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace Madj2k\AiAssistant\Assistant\Memory;

use Madj2k\AiAssistant\Config\Config;
use Madj2k\AiCore\Assistant\Memory\SessionMemory;
use Madj2k\AiCore\Assistant\Memory\SessionStoreInterface;

/** Creates the configured conversation memory service. */
final readonly class MemoryFactory
{
    public function __construct(private SessionStoreInterface $sessionStore)
    {
    }

    public function create(): SessionMemory
    {
        return new SessionMemory(
            $this->sessionStore,
            max(2, (int)Config::get('chat.memory.maxPrompts', 20)),
        );
    }
}
