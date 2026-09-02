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

/**
 * Creates the configured conversation memory service.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final readonly class MemoryFactory
{
    /**
     * @param \Madj2k\AiCore\Assistant\Memory\SessionStoreInterface $sessionStore
     */
    public function __construct(private SessionStoreInterface $sessionStore)
    {
    }


    /**
     * @return \Madj2k\AiCore\Assistant\Memory\SessionMemory
     * @throws \Madj2k\AiAssistant\Exception\AppException
     * @throws \Madj2k\AiCore\Exception\AppException
     */
    public function create(): SessionMemory
    {
        return new SessionMemory(
            $this->sessionStore,
            max(2, (int)Config::get('chat.memory.maxPrompts', 20)),
        );
    }
}
