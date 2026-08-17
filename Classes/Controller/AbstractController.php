<?php
declare(strict_types=1);

namespace Madj2k\AiAssistant\Controller;

use Madj2k\AiAssistant\Assistant\Domain\Repository\AssistantProfileRepository;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * Class AbstractController
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
    ) {
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
