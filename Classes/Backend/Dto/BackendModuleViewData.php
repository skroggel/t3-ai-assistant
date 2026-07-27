<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension ai_assistant.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiAssistant\Backend\Dto;

/**
 * Class BackendModuleViewData
 *
 * Aggregates all backend module view data sections.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class BackendModuleViewData
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Backend\Dto\BackendConfigurationViewData $configurationViewData Configuration view data.
     * @param \Madj2k\AiAssistant\Backend\Dto\BackendIndexerViewData $indexerViewData Indexer view data.
     * @param \Madj2k\AiAssistant\Backend\Dto\BackendPreviewViewData $previewViewData Preview view data.
     * @param \Madj2k\AiAssistant\Backend\Dto\BackendPurgeViewData $purgeViewData Purge view data.
     * @param \Madj2k\AiAssistant\Backend\Dto\BackendDiagnosticsViewData $diagnosticsViewData Diagnostics view data.
     */
    public function __construct(
        protected readonly BackendConfigurationViewData $configurationViewData,
        protected readonly BackendIndexerViewData $indexerViewData,
        protected readonly BackendPreviewViewData $previewViewData,
        protected readonly BackendPurgeViewData $purgeViewData,
        protected readonly BackendDiagnosticsViewData $diagnosticsViewData
    ) {
    }


    /**
     * Returns aggregated view data.
     *
     * @return array<string, mixed> View data.
     */
    public function toArray(): array
    {
        return array_merge(
            $this->configurationViewData->toArray(),
            $this->indexerViewData->toArray(),
            $this->previewViewData->toArray(),
            $this->purgeViewData->toArray(),
            $this->diagnosticsViewData->toArray()
        );
    }
}
