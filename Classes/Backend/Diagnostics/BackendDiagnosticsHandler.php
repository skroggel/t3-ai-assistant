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

namespace Madj2k\AiAssistant\Backend\Diagnostics;

use Madj2k\AiAssistant\Backend\Response\BackendFlashMessageService;
use Madj2k\AiAssistant\Assistant\Domain\Repository\PipelineTraceRepository;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

/**
 * Class BackendDiagnosticsHandler
 *
 * Handles write actions for pipeline logs.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final readonly class BackendDiagnosticsHandler
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Assistant\Domain\Repository\PipelineTraceRepository $pipelineTraceRepository Pipeline trace repository.
     * @param \Madj2k\AiAssistant\Backend\Response\BackendFlashMessageService $flashMessageService Flash message service.
     */
    public function __construct(
        private PipelineTraceRepository    $pipelineTraceRepository,
        private BackendFlashMessageService $flashMessageService
    ) {
    }


    /**
     * Checks whether the request contains a diagnostic write action.
     *
     * @param object $request PSR-7 backend request or Extbase request.
     * @return bool True when supported.
     */
    public function supports(object $request): bool
    {
        return $this->getRequestArgument($request, 'purgeChatLog') !== ''
            || $this->getRequestArgument($request, 'aiassistantBackendAction') === 'purgePipelineLogs';
    }


    /**
     * Handles diagnostic write actions.
     *
     * @param object $request PSR-7 backend request or Extbase request.
     * @param array<string, mixed> $state Mutable module state.
     * @return void
     */
    public function handle(object $request, array &$state): void
    {
        if (!$this->supports($request)) {
            return;
        }

        /** @var int $deleted */
        $deleted = $this->pipelineTraceRepository->deleteAll();

        if ($deleted > 0) {
            $this->flashMessageService->add(
                sprintf('Pipeline logs wurden gelöscht: %d Datensatz/Datensätze.', $deleted),
                'Logs gelöscht',
                ContextualFeedbackSeverity::OK
            );
            return;
        }

        $this->flashMessageService->add(
            'Es wurden keine Pipeline logs gefunden, die gelöscht werden konnten.',
            'Keine Logs gelöscht',
            ContextualFeedbackSeverity::WARNING
        );
    }


    /**
     * Resolves a request argument from PSR-7, Extbase or raw POST data.
     *
     * @param object $request PSR-7 backend request or Extbase request.
     * @param string $name Argument name.
     * @return string Argument value.
     */
    private function getRequestArgument(object $request, string $name): string
    {
        if ($request instanceof ServerRequestInterface) {
            $parsedBody = $request->getParsedBody();
            if (is_array($parsedBody) && array_key_exists($name, $parsedBody)) {
                return trim((string)$parsedBody[$name]);
            }
        }

        if (method_exists($request, 'hasArgument') && method_exists($request, 'getArgument') && $request->hasArgument($name)) {
            return trim((string)$request->getArgument($name));
        }

        if (array_key_exists($name, $_POST)) {
            return trim((string)$_POST[$name]);
        }

        return '';
    }
}
