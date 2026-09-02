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
 */
final class Typo3FrontendSessionStore implements SessionStoreInterface
{
    public function read(string $key): mixed
    {
        return $this->getFrontendUser()->getKey('ses', $key);
    }

    public function write(string $key, mixed $value): void
    {
        $frontendUser = $this->getFrontendUser();
        $frontendUser->setKey('ses', $key, $value);
        $frontendUser->storeSessionData();
    }

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
