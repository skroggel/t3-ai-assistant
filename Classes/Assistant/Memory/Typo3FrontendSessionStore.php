<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace Madj2k\AiAssistant\Assistant\Memory;

use Madj2k\AiCore\Assistant\Memory\SessionStoreInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Persists assistant state in the current TYPO3 frontend user session.
 *
 * This adapter is intentionally not the default. Projects that need TYPO3
 * session persistence can alias SessionStoreInterface to this service.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2 or later
 */
final class Typo3FrontendSessionStore implements SessionStoreInterface
{
    /**
     * Returns a value from the current TYPO3 frontend user session.
     *
     * @param string $key Session key.
     * @return mixed Stored value or null when the key does not exist.
     * @throws \RuntimeException When no TYPO3 frontend request or frontend
     *     user session is available.
     */
    public function read(string $key): mixed
    {
        return $this->getFrontendUser()->getKey('ses', $key);
    }


    /**
     * Stores a value in the current TYPO3 frontend user session.
     *
     * The changed session data is persisted immediately.
     *
     * @param string $key Session key.
     * @param mixed $value Value to store.
     * @return void
     * @throws \RuntimeException When no TYPO3 frontend request or frontend
     *     user session is available.
     */
    public function write(string $key, mixed $value): void
    {
        $frontendUser = $this->getFrontendUser();
        $frontendUser->setKey('ses', $key, $value);
        $frontendUser->storeSessionData();
    }


    /**
     * Returns the frontend user authentication of the current TYPO3 request.
     *
     * @return FrontendUserAuthentication Current frontend user authentication.
     * @throws \RuntimeException When the current TYPO3 request or its frontend
     *     user attribute is unavailable.
     */
    private function getFrontendUser(): FrontendUserAuthentication
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (!$request instanceof ServerRequestInterface) {
            throw new \RuntimeException('The current TYPO3 frontend request is unavailable.', 1788342101);
        }

        $frontendUser = $request->getAttribute('frontend.user');
        if (!$frontendUser instanceof FrontendUserAuthentication) {
            throw new \RuntimeException('The TYPO3 frontend user session is unavailable.', 1788342102);
        }

        return $frontendUser;
    }
}
