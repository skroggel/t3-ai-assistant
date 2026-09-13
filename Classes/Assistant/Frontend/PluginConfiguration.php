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

namespace Madj2k\AiAssistant\Assistant\Frontend;

/**
 * Class PluginConfiguration
 *
 * Contains the assistant selection and runtime settings of one content element.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final readonly class PluginConfiguration
{
    /**
     * Constructor.
     *
     * @param int $contentElementUid Content element uid.
     * @param int $assistantProfileUid Selected assistant profile uid.
     * @param array<string,mixed> $runtimeSettings Runtime settings exposed to the assistant request.
     */
    public function __construct(
        public int $contentElementUid,
        public int $assistantProfileUid,
        public array $runtimeSettings = [],
    ) {
    }
}
