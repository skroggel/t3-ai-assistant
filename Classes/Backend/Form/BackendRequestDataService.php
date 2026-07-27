<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 3
 * of the License, or any later version.
 */

namespace Madj2k\AiAssistant\Backend\Form;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class BackendRequestDataService
 *
 * Normalizes frequently used request and state access for the backend module.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class BackendRequestDataService
{
    /**
     * Returns the PSR-7 backend request when available.
     *
     * @return \Psr\Http\Message\ServerRequestInterface|null Backend request.
     */
    public function getBackendRequest(): ?ServerRequestInterface
    {
        /** @var mixed $request */
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;

        return $request instanceof ServerRequestInterface ? $request : null;
    }


    /**
     * Checks whether the Extbase request uses POST.
     *
     * @param object $request Extbase request.
     * @return bool True for POST.
     */
    public function isPostRequest(object $request): bool
    {
        return strtoupper((string)$request->getMethod()) === 'POST';
    }


    /**
     * Checks whether the Extbase request uses GET.
     *
     * @param object $request Extbase request.
     * @return bool True for GET.
     */
    public function isGetRequest(object $request): bool
    {
        return strtoupper((string)$request->getMethod()) === 'GET';
    }


    /**
     * Creates the mutable module state that is enriched by action handlers.
     *
     * @return array<string,mixed>
     */
    public function createInitialState(): array
    {
        return [
            'previewResult' => null,
            'searchResult' => [],
            'healthChecked' => false,
            'health' => [
                'openai' => ['status' => 'unknown', 'message' => 'Not checked'],
                'qdrant' => ['status' => 'unknown', 'message' => 'Not checked'],
            ],
            'submittedValues' => [],
            'fieldErrors' => [],
            'indexerNotice' => null,
            'connectorNotice' => null,
            'connectionTestResult' => null,
            'previewValues' => [
                'filePath' => '',
                'pageId' => '',
                'language' => '0',
            ],
        ];
    }
}
