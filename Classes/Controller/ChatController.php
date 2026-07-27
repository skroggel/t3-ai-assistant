<?php
declare(strict_types=1);

namespace Madj2k\AiAssistant\Controller;

use Madj2k\AiAssistant\Assistant\Application\Orchestrator;
use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile;
use Madj2k\AiAssistant\Assistant\Domain\Repository\AssistantProfileRepository;
use Madj2k\AiAssistant\Assistant\DTO\AssistantRequest;
use Madj2k\AiAssistant\Assistant\Http\SseResponseFactory;
use Madj2k\AiAssistant\Exception\AppException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ChatController
 *
 * Frontend controller for the chat stream. It only translates Extbase input
 * into a chat turn and delegates all business logic to the assistant domain.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ChatController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Assistant\Application\Orchestrator $orchestrator Chat turn orchestrator.
     * @param \Madj2k\AiAssistant\Assistant\Http\SseResponseFactory $sseResponseFactory SSE response factory.
     * @param \Madj2k\AiAssistant\Assistant\Domain\Repository\AssistantProfileRepository $assistantProfileRepository Assistant profile repository.
     */
    public function __construct(
        protected readonly Orchestrator               $orchestrator,
        protected readonly SseResponseFactory         $sseResponseFactory,
        protected readonly AssistantProfileRepository $assistantProfileRepository,
    ) {
    }


    /**
     * Streams one chat response over SSE.
     *
     * @param string $query User query.
     * @param int $startTimestamp Frontend page timestamp for session reset.
     * @param int $assistantProfile Assistant profile selected in the plugin.
     * @param string $chatIdentifier Stable frontend conversation scope.
     * @param string $settingsJson Runtime settings provided by the frontend plugin.
     * @return \Psr\Http\Message\ResponseInterface SSE response.
     */
    public function streamAction(
        string $query = '',
        int $startTimestamp = 0,
        int $assistantProfile = 0,
        string $chatIdentifier = '',
        string $settingsJson = '',
    ): ResponseInterface {
        /** @var array<string,mixed> $runtimeSettings */
        $runtimeSettings = [];
        $decodedRuntimeSettings = json_decode($settingsJson, true);
        if (is_array($decodedRuntimeSettings)) {
            $runtimeSettings = $decodedRuntimeSettings;
        }

        /** @var \Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile|null $profile */
        $profile = $this->assistantProfileRepository->findByUid($assistantProfile);
        $serverRequest = $this->resolveServerRequest();

        try {
            if ($chatIdentifier === '' || $startTimestamp === 0) {
                throw new AppException('No chat identifier specified');
            }

            if (!$profile instanceof AssistantProfile) {
                throw new AppException('Assistant profile not found');
            }

            $assistantRequest = new AssistantRequest(
                query: $query,
                startTimestamp: $startTimestamp,
                assistantProfile: $profile,
                chatIdentifier: $chatIdentifier,
                serverRequest: $serverRequest,
                runtimeSettings: $runtimeSettings,
            );

            $streamProducer = $this->orchestrator->createStreamProducer(
                $assistantRequest,
                function (string $chunk): void {
                    if ($chunk !== '') {
                        $this->sseResponseFactory->sendData($chunk);
                    }
                }
            );
        } catch (\Throwable $exception) {
            return $this->sseResponseFactory->createStreamingResponse(function () use ($exception): void {
                $this->sseResponseFactory->sendPrelude();
                $this->sseResponseFactory->sendEvent('error', $exception->getMessage());
                $this->sseResponseFactory->sendEvent('done', 'end');
            });
        }

        return $this->sseResponseFactory->createStreamingResponse(function () use ($streamProducer): void {
            $this->sseResponseFactory->sendPrelude();

            try {
                $streamProducer();
                $this->sseResponseFactory->sendEvent('done', 'end');
            } catch (\Throwable $exception) {
                $this->sseResponseFactory->sendEvent('error', $exception->getMessage());
                $this->sseResponseFactory->sendEvent('done', 'end');
            }
        });
    }
}
