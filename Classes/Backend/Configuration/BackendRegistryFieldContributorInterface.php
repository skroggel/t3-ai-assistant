<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension ai_assistant.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
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
