<?php
declare(strict_types=1);

namespace Madj2k\AiAssistant\Controller;

use Madj2k\AiCore\Assistant\Application\Orchestrator;
use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile;
use Madj2k\AiAssistant\Assistant\Domain\Repository\AssistantProfileRepository;
use Madj2k\AiCore\Assistant\DTO\AssistantRequest;
use Madj2k\AiCore\Assistant\DTO\DirectInteraction;
use Madj2k\AiAssistant\Assistant\Http\SseResponseFactory;
use Madj2k\AiAssistant\Assistant\Frontend\ChatOptionsResolver;
use Madj2k\AiCore\Exception\AppException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
    protected readonly LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param \Madj2k\AiCore\Assistant\Application\Orchestrator $orchestrator Chat turn orchestrator.
     * @param \Madj2k\AiAssistant\Assistant\Http\SseResponseFactory $sseResponseFactory SSE response factory.
     * @param \Madj2k\AiAssistant\Assistant\Domain\Repository\AssistantProfileRepository $assistantProfileRepository Assistant profile repository.
     * @param \Madj2k\AiAssistant\Assistant\Frontend\ChatOptionsResolver $chatOptionsResolver Chat options resolver.
     * @param \Psr\Log\LoggerInterface|null $logger Frontend stream logger.
     */
    public function __construct(
        protected readonly Orchestrator               $orchestrator,
        protected readonly SseResponseFactory         $sseResponseFactory,
        protected readonly AssistantProfileRepository $assistantProfileRepository,
        protected readonly ChatOptionsResolver         $chatOptionsResolver,
        ?LoggerInterface                                $logger = null,
    ) {
        $this->logger = $logger ?? GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }


    /**
     * Streams one chat response over SSE.
     *
     * @param string $query User query.
     * @param int $startTimestamp Frontend page timestamp for session reset.
     * @param int $assistantProfile Assistant profile selected in the plugin.
     * @param string $chatIdentifier Stable frontend conversation scope.
     * @param string $settingsJson Runtime settings provided by the frontend plugin.
     * @param string $userLanguage Optional response language selected by the user.
     * @param string $directInteraction Optional explicit direct interaction identifier.
     * @return \Psr\Http\Message\ResponseInterface SSE response.
     */
    public function streamAction(
        string $query = '',
        int $startTimestamp = 0,
        int $assistantProfile = 0,
        string $chatIdentifier = '',
        string $settingsJson = '',
        string $userLanguage = '',
        string $directInteraction = '',
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

            $chatOptions = $this->chatOptionsResolver->resolve(
                $runtimeSettings,
                $this->resolveSiteLanguage(),
                $userLanguage,
            );
            $isLanguageConfirmation = $directInteraction === 'language_confirmation';
            if ($directInteraction !== '' && !$isLanguageConfirmation) {
                throw new AppException('Unsupported direct interaction');
            }
            if (
                $isLanguageConfirmation
                && !$this->chatOptionsResolver->showLanguageSelector($runtimeSettings)
            ) {
                throw new AppException('Language confirmation is not available');
            }

            $assistantRequest = new AssistantRequest(
                query: $isLanguageConfirmation ? $chatOptions->responseLanguage : $query,
                startTimestamp: $startTimestamp,
                assistantProfile: $profile,
                chatIdentifier: $chatIdentifier,
                serverRequest: $serverRequest,
                runtimeSettings: $runtimeSettings,
                chatOptions: $chatOptions,
            );

            if ($isLanguageConfirmation) {
                $streamProducer = function () use ($assistantRequest): void {
                    $response = $this->orchestrator->handleDirect(
                        $assistantRequest,
                        new DirectInteraction(
                            instruction: 'Write exactly one short sentence confirming that all following answers will use the language named by the user. Write the sentence in that language and mention only its natural language name. Interpret language, locale and regional codes when provided, but never reproduce or mention those codes in the answer.',
                            maxTokens: 60,
                            remember: false,
                        ),
                    );
                    if ($response->answer !== '') {
                        $this->sseResponseFactory->sendData($response->answer);
                    }
                };
            } else {
                $streamProducer = $this->orchestrator->createStreamProducer(
                    $assistantRequest,
                    function (string $chunk): void {
                        if ($chunk !== '') {
                            $this->sseResponseFactory->sendData($chunk);
                        }
                    }
                );
            }
        } catch (\Throwable $exception) {
            $this->logStreamException($exception);
            return $this->sseResponseFactory->createStreamingResponse(function (): void {
                $this->sseResponseFactory->sendPrelude();
                $this->sseResponseFactory->sendEvent('error', SseResponseFactory::ERROR_MESSAGE);
                $this->sseResponseFactory->sendEvent('done', 'end');
            });
        }

        return $this->sseResponseFactory->createStreamingResponse(function () use ($streamProducer): void {
            $this->sseResponseFactory->sendPrelude();

            try {
                $streamProducer();
                $this->sseResponseFactory->sendEvent('done', 'end');
            } catch (\Throwable $exception) {
                $this->logStreamException($exception);
                $this->sseResponseFactory->sendEvent('error', SseResponseFactory::ERROR_MESSAGE);
                $this->sseResponseFactory->sendEvent('done', 'end');
            }
        });
    }


    private function logStreamException(\Throwable $exception): void
    {
        $this->logger->error('Frontend chat stream failed.', [
            'exception' => $exception,
            'exception_class' => $exception::class,
            'exception_message' => $exception->getMessage(),
        ]);
    }
}
