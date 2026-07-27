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
use TYPO3\CMS\Core\Http\JsonResponse;

/**
 * Class BackendJsonResponseFactory
 *
 * Builds JSON responses for small backend module endpoints.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class BackendJsonResponseFactory
{
    /**
     * Creates a JSON response.
     *
     * @param array<string,mixed> $data Response payload.
     * @param int $status HTTP status code.
     * @return \Psr\Http\Message\ResponseInterface JSON response.
     */
    public function create(array $data, int $status = 200): ResponseInterface
    {
        return new JsonResponse($data, $status);
    }
}
