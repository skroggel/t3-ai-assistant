<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension ai_assistant.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiAssistant\Controller;

use Madj2k\AiAssistant\Backend\Configuration\BackendConfigurationHandler;
use Madj2k\AiAssistant\Backend\Diagnostics\BackendConnectionTestHandler;
use Madj2k\AiAssistant\Backend\Diagnostics\BackendDiagnosticsHandler;
use Madj2k\AiAssistant\Backend\Form\BackendFormTokenService;
use Madj2k\AiAssistant\Backend\Form\BackendRequestDataService;
use Madj2k\AiAssistant\Backend\Purge\BackendPurgeHandler;
use Madj2k\AiAssistant\Backend\View\BackendViewDataFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\FormProtection\BackendFormProtection;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class BackendController
 *
 * Backend module controller with one action per remaining backend area.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class BackendController extends ActionController
{
    /**
     * @param \TYPO3\CMS\Backend\Template\ModuleTemplateFactory $moduleTemplateFactory Module template factory.
     * @param \Madj2k\AiAssistant\Backend\Form\BackendRequestDataService $requestDataService Request data service.
     * @param \Madj2k\AiAssistant\Backend\Form\BackendFormTokenService $formTokenService Form token service.
     * @param \Madj2k\AiAssistant\Backend\View\BackendViewDataFactory $viewDataFactory View data factory.
     * @param \Madj2k\AiAssistant\Backend\Configuration\BackendConfigurationHandler $configurationHandler Configuration handler.
     * @param \Madj2k\AiAssistant\Backend\Diagnostics\BackendConnectionTestHandler $connectionTestHandler Connection test handler.
     * @param \Madj2k\AiAssistant\Backend\Diagnostics\BackendDiagnosticsHandler $diagnosticsHandler Diagnostics handler.
     * @param \Madj2k\AiAssistant\Backend\Purge\BackendPurgeHandler $purgeHandler Purge handler.
     */
    public function __construct(
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly BackendRequestDataService $requestDataService,
        private readonly BackendFormTokenService $formTokenService,
        private readonly BackendViewDataFactory $viewDataFactory,
        private readonly BackendConfigurationHandler $configurationHandler,
        private readonly BackendConnectionTestHandler $connectionTestHandler,
        private readonly BackendDiagnosticsHandler $diagnosticsHandler,
        private readonly BackendPurgeHandler $purgeHandler
    ) {
    }


    /**
     * Shows and processes the central configuration area.
     *
     * @return \Psr\Http\Message\ResponseInterface HTML response.
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    public function configurationAction(): ResponseInterface
    {
        return $this->renderModuleArea(
            'configuration',
            'Backend/Configuration',
            [$this->configurationHandler]
        );
    }


    /**
     * Shows and processes connection diagnostics.
     *
     * @return \Psr\Http\Message\ResponseInterface HTML response.
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    public function diagnosticsAction(): ResponseInterface
    {
        return $this->renderModuleArea(
            'diagnostics',
            'Backend/Diagnostics',
            [$this->connectionTestHandler]
        );
    }


    /**
     * Shows the indexer status area.
     *
     * @return \Psr\Http\Message\ResponseInterface HTML response.
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    public function indexerStatusAction(): ResponseInterface
    {
        return $this->renderModuleArea(
            'indexerStatus',
            'Backend/IndexerStatus',
            []
        );
    }


    /**
     * Shows and processes pipeline logs.
     *
     * @return \Psr\Http\Message\ResponseInterface HTML response.
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    public function logsAction(): ResponseInterface
    {
        return $this->renderModuleArea(
            'logs',
            'Backend/Logs',
            [$this->diagnosticsHandler]
        );
    }


    /**
     * Shows and processes vector collection purge actions.
     *
     * @return \Psr\Http\Message\ResponseInterface HTML response.
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    public function purgeAction(): ResponseInterface
    {
        return $this->renderModuleArea(
            'purge',
            'Backend/Purge',
            [$this->purgeHandler]
        );
    }


    /**
     * Renders a backend module area and delegates explicit write actions to area handlers.
     *
     * @param string $activeDomain Active module domain.
     * @param string $templateName Fluid template name.
     * @param array<int, object> $handlers Action handlers.
     * @return \Psr\Http\Message\ResponseInterface HTML response.
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    private function renderModuleArea(
        string $activeDomain,
        string $templateName,
        array $handlers
    ): ResponseInterface {
        /** @var \Psr\Http\Message\ServerRequestInterface|null $backendRequest */
        $backendRequest = $this->requestDataService->getBackendRequest();

        if (!$backendRequest instanceof ServerRequestInterface) {
            return $this->htmlResponse();
        }

        /** @var \TYPO3\CMS\Core\FormProtection\BackendFormProtection $formProtection */
        $formProtection = $this->formTokenService->createFormProtection($backendRequest);

        /** @var array<string, mixed> $state */
        $state = $this->requestDataService->createInitialState();
        $this->processAreaAction($handlers, $state, $formProtection, $backendRequest);

        /** @var array<string, mixed> $viewData */
        $viewData = $this->viewDataFactory
            ->create($this->request, $backendRequest, $state)
            ->toArray();

        $viewData = array_merge(
            $viewData,
            $this->buildSharedViewData($activeDomain, $backendRequest, $formProtection)
        );

        /** @var \TYPO3\CMS\Backend\Template\ModuleTemplate $moduleTemplate */
        $moduleTemplate = $this->moduleTemplateFactory->create($backendRequest);
        $moduleTemplate->assignMultiple($viewData);

        GeneralUtility::makeInstance(AssetCollector::class)
            ->addStyleSheet('aiassistant-backend', 'EXT:ai_assistant/Resources/Public/Styles/Backend.css');

        return $moduleTemplate->renderResponse($templateName);
    }


    /**
     * Delegates POST actions to the active area handlers.
     *
     * @param array<int, object> $handlers Action handlers.
     * @param array<string, mixed> $state Mutable module state.
     * @param \TYPO3\CMS\Core\FormProtection\BackendFormProtection $formProtection Form protection.
     * @param \Psr\Http\Message\ServerRequestInterface $backendRequest PSR-7 backend request.
     * @return void
     */
    private function processAreaAction(
        array $handlers,
        array &$state,
        BackendFormProtection $formProtection,
        ServerRequestInterface $backendRequest
    ): void {

        if (
            strtoupper($backendRequest->getMethod()) !== 'POST'
            && !$this->requestDataService->isPostRequest($this->request)
            && strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? '')) !== 'POST'
        ) {
            return;
        }

        if (
            !$this->formTokenService->validate($backendRequest, $formProtection)
            && !$this->formTokenService->validate($this->request, $formProtection)
        ) {
            $this->addFlashMessage(
                'Invalid form token. Please reload the module and try again.',
                '',
                ContextualFeedbackSeverity::ERROR
            );
            return;
        }

        foreach ($handlers as $handler) {
            if (!method_exists($handler, 'supports') || !method_exists($handler, 'handle')) {
                continue;
            }

            if ($handler->supports($backendRequest)) {
                $handler->handle($backendRequest, $state);
                return;
            }

            if ($handler->supports($this->request)) {
                $handler->handle($this->request, $state);
                return;
            }
        }
    }


    /**
     * Builds shared values used by every backend template.
     *
     * @param string $activeDomain Active module domain.
     * @param \Psr\Http\Message\ServerRequestInterface $backendRequest Backend request.
     * @param \TYPO3\CMS\Core\FormProtection\BackendFormProtection $formProtection Form protection.
     * @return array<string, mixed> Shared view data.
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    private function buildSharedViewData(
        string $activeDomain,
        ServerRequestInterface $backendRequest,
        BackendFormProtection $formProtection
    ): array {
        /** @var \TYPO3\CMS\Backend\Routing\UriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        /** @var array<string, string> $actions */
        $actions = [
            'configuration' => (string)$uriBuilder->buildUriFromRoute('web_aiassistant', ['action' => 'configuration']),
            'diagnostics' => (string)$uriBuilder->buildUriFromRoute('web_aiassistant', ['action' => 'diagnostics']),
            'indexerStatus' => (string)$uriBuilder->buildUriFromRoute('web_aiassistant', ['action' => 'indexerStatus']),
            'purge' => (string)$uriBuilder->buildUriFromRoute('web_aiassistant', ['action' => 'purge']),
            'logs' => (string)$uriBuilder->buildUriFromRoute('web_aiassistant', ['action' => 'logs']),
        ];

        return [
            'activeDomain' => $activeDomain,
            'formAction' => $actions[$activeDomain] ?? (string)$backendRequest->getUri(),
            'showAction' => $actions[$activeDomain] ?? (string)$backendRequest->getUri(),
            'formToken' => $this->formTokenService->generate($formProtection),
            'actions' => $actions,
        ];
    }
}
