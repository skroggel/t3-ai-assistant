<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, version 3.
 */

namespace Madj2k\AiAssistant\Assistant\Memory;

use Madj2k\AiAssistant\Config\Config;
use Madj2k\AiCore\Assistant\Memory\SessionMemory as CoreSessionMemory;

/**
 * TYPO3-session-backed memory kept as an explicit, backwards-compatible service.
 *
 * The default MemoryInterface service uses the native PHP session instead.
 */
final class SessionMemory extends CoreSessionMemory
{
    public function __construct(?Typo3FrontendSessionStore $sessionStore = null)
    {
        parent::__construct(
            $sessionStore ?? new Typo3FrontendSessionStore(),
            max(2, (int)Config::get('chat.memory.maxPrompts', 20)),
        );
    }
}
