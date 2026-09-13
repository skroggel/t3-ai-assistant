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

namespace Madj2k\AiAssistant\Backend\Configuration;

/**
 * Contributes editable registry fields to the backend configuration module.
 */
interface BackendRegistryFieldContributorInterface
{
    /**
     * @return array<int, array<string, mixed>> Field definitions.
     */
    public function getFields(): array;
}
