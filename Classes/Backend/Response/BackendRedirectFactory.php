<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 3
 * of the License, or any later version.
 */

namespace Madj2k\AiAssistant\Backend\Response;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\RedirectResponse;

/**
 * Class BackendRedirectFactory
 *
 * Creates backend redirects and keeps response construction out of the controller.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class BackendRedirectFactory
{
    /**
     * Creates a redirect response.
     *
     * @param string $uri Target URI.
     * @return \Psr\Http\Message\ResponseInterface Redirect response.
     */
    public function create(string $uri): ResponseInterface
    {
        return new RedirectResponse($uri);
    }
}
