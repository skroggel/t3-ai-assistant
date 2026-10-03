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

namespace Madj2k\AiAssistant\Controller;

use Madj2k\AiAssistant\Assistant\Domain\Repository\AssistantProfileRepository;
use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use Madj2k\AiAssistant\Assistant\Service\FrontendRequestTokenService;

/**
 * Class AbstractController
 *
 * Renders the frontend chat plugin and exposes only the assistant selected in
 * the plugin configuration to the JavaScript client.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
abstract class AbstractController extends ActionController
{

    /**
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer|null $currentContentObject
     */
    protected ?ContentObjectRenderer $currentContentObject = null;


    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Assistant\Domain\Repository\AssistantProfileRepository $assistantProfileRepository Assistant repository.
     */
    public function __construct(
        protected readonly AssistantProfileRepository  $assistantProfileRepository,
        protected readonly FrontendRequestTokenService $requestTokenService,
    ) {
    }

    /**
     * Returns the shared Assistant site settings.
     *
     * @return array<string,mixed>
     */
    protected function getAssistantSiteSettings(): array
    {
        $site = $this->resolveServerRequest()?->getAttribute('site');
        $settings = $site?->getSettings()->get('aiAssistant', []);

        return is_array($settings) ? $settings : [];
    }


    /**
     * Checks if the given assistantProfile is allowed globally
     *
     * @param int $assistantProfile
     * @return bool
     */
    protected function isAllowedAssistantProfile(int $assistantProfile): bool
    {
        $allowed = $this->getAssistantSiteSettings()['allowedAssistantProfiles'] ?? [];
        if (is_string($allowed)) {
            $allowed = preg_split('/[,\s]+/', trim($allowed), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        if (!is_array($allowed) || $allowed === []) {
            return $assistantProfile > 0;
        }

        return in_array($assistantProfile, array_map('intval', $allowed), true);
    }


    /**
     * Creates a request token
     *
     * @param int $assistantProfile
     * @param string $chatIdentifier
     * @return string
     */
    protected function createRequestToken(int $assistantProfile, string $chatIdentifier): string
    {
        if (!$this->isAllowedAssistantProfile($assistantProfile)) {
            return '';
        }

        return $this->requestTokenService->create([
            'pageUid' => $this->getCurrentPageUid(),
            'assistantProfile' => $assistantProfile,
            'chatIdentifier' => $chatIdentifier,
        ], (int)($this->getAssistantSiteSettings()['requestTokenTtl'] ?? 7200));
    }


    /**
     * Validates a request token
     *
     * @param string $token
     * @param int $assistantProfile
     * @param string $chatIdentifier
     * @return bool
     */
    protected function validateRequestToken(
        string $token,
        int $assistantProfile,
        string $chatIdentifier,
    ): bool {
        return $this->isAllowedAssistantProfile($assistantProfile)
            && $this->requestTokenService->isValid($token, [
                'pageUid' => $this->getCurrentPageUid(),
                'assistantProfile' => $assistantProfile,
                'chatIdentifier' => $chatIdentifier,
            ]);
    }

    /**
     * @param string $requestToken
     * @param int $assistantProfile
     * @param string $chatIdentifier
     * @param string $settingsJson
     * @return bool
     */
    protected function isValidFrontendRequest(
        string $requestToken,
        int $assistantProfile,
        string $chatIdentifier,
        string $settingsJson,
    ): bool {
        return $this->validateRequestToken($requestToken, $assistantProfile, $chatIdentifier)
            && strlen($settingsJson) <= $this->getMaxPayloadBytes();
    }


    /**
     * @return int
     */
    protected function getMaxPayloadBytes(): int
    {
        return max(1024, (int)($this->getAssistantSiteSettings()['maxPayloadBytes'] ?? 524288));
    }

    /**
     * Decodes frontend runtime settings consistently for JSON and SSE requests.
     *
     * @return array<string,mixed>
     */
    protected function resolveRuntimeSettings(string $settingsJson): array
    {
        $settings = json_decode($settingsJson, true);
        return is_array($settings) ? $settings : [];
    }

    /**
     * @param int $assistantProfile
     * @return \Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile|null
     */
    protected function resolveAssistantProfile(int $assistantProfile): ?AssistantProfile
    {
        $profile = $this->assistantProfileRepository->findByUid($assistantProfile);
        return $profile instanceof AssistantProfile ? $profile : null;
    }


    /**
     * Returns the current pid
     *
     * @return int
     */
    protected function getCurrentPageUid(): int
    {
        $request = $this->resolveServerRequest();
        $queryParameters = $request?->getQueryParams() ?? [];
        $queryPageUid = (int)($queryParameters['id'] ?? 0);
        if ($queryPageUid === 0) {
            $queryPageUid = (int)(
                $queryParameters['tx_aiassistantpremium_search']['id']
                ?? $queryParameters['tx_aiassistant_chat']['id']
                ?? 0
            );
        }
        if ($queryPageUid > 0) {
            return $queryPageUid;
        }

        $contentPageUid = (int)($this->currentContentObject?->data['pid'] ?? 0);
        if ($contentPageUid > 0) {
            return $contentPageUid;
        }

        return (int)($GLOBALS['TSFE']->id ?? 0);
    }


    /**
     * Set globally used objects
     */
    protected function initializeAction(): void
    {
        $this->currentContentObject = $this->request->getAttribute('currentContentObject');
    }


    /**
     * Encodes settings for the frontend client.
     *
     * @param array<string,mixed> $runtimeSettings Runtime settings.
     * @return string Encoded runtime settings.
     */
    protected function jsonEncodeSettings(array $runtimeSettings): string
    {
        try {
            return json_encode($runtimeSettings, JSON_THROW_ON_ERROR | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG);
        } catch (\JsonException) {
            return '{}';
        }
    }


    /**
     * Creates a new chat identifier.
     *
     * @return string New chat identifier.
     */
    protected function createChatIdentifier(): string
    {
        try {
            return bin2hex(random_bytes(16));
        } catch (\Throwable) {
            return str_replace('.', '', uniqid('trace', true));
        }
    }


    /**
     * Resolves the current PSR-7 request.
     *
     * @return \Psr\Http\Message\ServerRequestInterface|null
     */
    protected function resolveServerRequest(): ?ServerRequestInterface
    {
        if (method_exists($this->request, 'getHttpRequest')) {
            $httpRequest = $this->request->getHttpRequest();
            if ($httpRequest instanceof ServerRequestInterface) {
                return $httpRequest;
            }
        }

        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;

        return $request instanceof ServerRequestInterface ? $request : null;
    }


    /**
     * Resolves the current TYPO3 site language as a locale identifier.
     *
     * @return string Current locale or an empty string when unavailable.
     */
    protected function resolveSiteLanguage(): string
    {
        $siteLanguage = $this->resolveServerRequest()?->getAttribute('language');

        return $siteLanguage instanceof SiteLanguage
            ? str_replace('_', '-', (string)$siteLanguage->getLocale())
            : '';
    }
}
