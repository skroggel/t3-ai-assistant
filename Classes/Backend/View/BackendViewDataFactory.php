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

namespace Madj2k\AiAssistant\Backend\View;

use Madj2k\AiAssistant\Backend\Configuration\BackendConfigurationProvider;
use Madj2k\AiAssistant\Backend\Diagnostics\BackendConnectionProvider;
use Madj2k\AiAssistant\Backend\Diagnostics\BackendDiagnosticsProvider;
use Madj2k\AiAssistant\Backend\Dto\BackendConfigurationViewData;
use Madj2k\AiAssistant\Backend\Dto\BackendDiagnosticsViewData;
use Madj2k\AiAssistant\Backend\Dto\BackendIndexerViewData;
use Madj2k\AiAssistant\Backend\Dto\BackendModuleViewData;
use Madj2k\AiAssistant\Backend\Dto\BackendPreviewViewData;
use Madj2k\AiAssistant\Backend\Dto\BackendPurgeViewData;
use Madj2k\AiAssistant\Backend\Indexing\BackendIndexerProvider;
use Madj2k\AiAssistant\Backend\Purge\BackendPurgeProvider;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class BackendViewDataFactory
 *
 * Aggregates the reduced backend module view data.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final readonly class BackendViewDataFactory
{
    /**
     * @param \Madj2k\AiAssistant\Backend\Configuration\BackendConfigurationProvider $configurationProvider Configuration provider.
     * @param \Madj2k\AiAssistant\Backend\Indexing\BackendIndexerProvider $indexerProvider Indexer provider.
     * @param \Madj2k\AiAssistant\Backend\Purge\BackendPurgeProvider $purgeProvider Purge provider.
     * @param \Madj2k\AiAssistant\Backend\Diagnostics\BackendConnectionProvider $connectionProvider Connection provider.
     * @param \Madj2k\AiAssistant\Backend\Diagnostics\BackendDiagnosticsProvider $diagnosticsProvider Diagnostics provider.
     */
    public function __construct(
        private BackendConfigurationProvider $configurationProvider,
        private BackendIndexerProvider       $indexerProvider,
        private BackendPurgeProvider         $purgeProvider,
        private BackendConnectionProvider    $connectionProvider,
        private BackendDiagnosticsProvider   $diagnosticsProvider
    ) {
    }


    /**
     * Creates the complete backend module view data.
     *
     * @param object $extbaseRequest Extbase request.
     * @param \Psr\Http\Message\ServerRequestInterface $backendRequest Backend request.
     * @param array<string, mixed> $state Module state.
     * @return \Madj2k\AiAssistant\Backend\Dto\BackendModuleViewData Module view data.
     */
    public function create(
        object $extbaseRequest,
        ServerRequestInterface $backendRequest,
        array $state
    ): BackendModuleViewData {
        return new BackendModuleViewData(
            new BackendConfigurationViewData(
                $this->configurationProvider->getViewData($extbaseRequest, $backendRequest, $state)
            ),
            new BackendIndexerViewData(
                $this->indexerProvider->getViewData($extbaseRequest, $backendRequest, $state)
            ),
            new BackendPreviewViewData([]),
            new BackendPurgeViewData(
                $this->purgeProvider->getViewData($extbaseRequest, $backendRequest, $state)
            ),
            new BackendDiagnosticsViewData(array_merge(
                $this->connectionProvider->getViewData($backendRequest, $state),
                $this->diagnosticsProvider->getViewData($extbaseRequest, $backendRequest)
            ))
        );
    }
}
