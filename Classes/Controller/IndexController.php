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
use Madj2k\AiAssistant\Assistant\Frontend\PluginConfigurationResolver;
use Madj2k\AiAssistant\Assistant\Frontend\ChatOptionsResolver;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class IndexController
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
class IndexController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Assistant\Domain\Repository\AssistantProfileRepository $assistantProfileRepository Assistant repository.
     * @param \Madj2k\AiAssistant\Assistant\Frontend\ChatOptionsResolver $chatOptionsResolver Chat options resolver.
     */
    public function __construct(
        AssistantProfileRepository $assistantProfileRepository,
        private readonly ChatOptionsResolver $chatOptionsResolver,
    ) {
        parent::__construct($assistantProfileRepository);
    }

    /**
     * Renders the index view.
     *
     * @return \Psr\Http\Message\ResponseInterface HTML response.
     */
    public function indexAction(): ResponseInterface
    {
        $assistantProfile = (int)$this->settings['assistantProfile'] > 0
            ? $this->assistantProfileRepository->findByUid((int)$this->settings['assistantProfile'])
            : null;

        $chatOptions = $this->chatOptionsResolver->resolve($this->settings, $this->resolveSiteLanguage());
        $frontendOptions = $this->chatOptionsResolver->toFrontendOptions($chatOptions, $this->settings);

        $this->view->assignMultiple([
            'pageUid' => (int)($this->currentContentObject->data['pid'] ?? 0),
            'contentElementUid' => (int)($this->currentContentObject->data['uid'] ?? 0),
            'chatIdentifier' => $this->createChatIdentifier(),
            'assistantProfile' => $assistantProfile,
            'startTimestamp' => time(),
            'settingsJson' => $this->jsonEncodeSettings($this->settings),
            'chatOptionsJson' => $this->jsonEncodeSettings($frontendOptions),
            'labelsJson' => $this->jsonEncodeSettings($this->getFrontendLabels()),
            'showLanguageSelector' => $this->chatOptionsResolver->showLanguageSelector($this->settings),
        ]);

        return $this->htmlResponse();
    }

    /**
     * Returns translated labels for the Vue chat in one serializable payload.
     *
     * @return array<string, string>
     */
    private function getFrontendLabels(): array
    {
        $keys = [
            'errorMessage' => 'templates_index_index.error_message',
            'chatLabel' => 'templates_index_index.chat_history',
            'userLabel' => 'templates_index_index.user_label',
            'assistantLabel' => 'templates_index_index.assistant_label',
            'consentMessage' => 'templates_index_index.consent_message',
            'consentLabel' => 'templates_index_index.consent_button',
            'inputPlaceholder' => 'templates_index_index.input_placeholder',
            'submitLabel' => 'templates_index_index.submit',
            'languageLabel' => 'templates_index_index.response_language',
            'siteLanguageLabel' => 'templates_index_index.use_site_language',
            'browserLanguageLabel' => 'templates_index_index.use_browser_language',
            'languageApplyLabel' => 'templates_index_index.apply_language',
            'languagePlaceholder' => 'templates_index_index.response_language_placeholder',
            'languageConfirmationFallback' => 'templates_index_index.language_confirmation_fallback',
        ];

        $labels = [];
        foreach ($keys as $name => $key) {
            $labels[$name] = (string)(LocalizationUtility::translate($key, 'ai_assistant') ?? '');
        }

        return $labels;
    }
}
