<?php
declare(strict_types=1);

namespace Madj2k\AiAssistant\Controller;

use Madj2k\AiAssistant\Assistant\Domain\Repository\AssistantProfileRepository;
use Madj2k\AiAssistant\Assistant\Frontend\PluginConfigurationResolver;
use Madj2k\AiAssistant\Assistant\Frontend\ChatOptionsResolver;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class IndexController
 *
 * Renders the frontend chat plugin and exposes only the assistant selected in
 * the plugin configuration to the JavaScript client.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
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

        $this->view->assignMultiple([
            'pageUid' => (int)($this->currentContentObject->data['pid'] ?? 0),
            'contentElementUid' => (int)($this->currentContentObject->data['uid'] ?? 0),
            'chatIdentifier' => $this->createChatIdentifier(),
            'assistantProfile' => $assistantProfile,
            'startTimestamp' => time(),
            'settingsJson' => $this->jsonEncodeSettings($this->settings),
            'chatOptionsJson' => $this->jsonEncodeSettings($this->chatOptionsResolver->toFrontendOptions($chatOptions)),
            'showLanguageSelector' => $this->chatOptionsResolver->showLanguageSelector($this->settings),
        ]);

        return $this->htmlResponse();
    }
}
