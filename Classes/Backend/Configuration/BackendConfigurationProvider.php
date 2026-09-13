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

namespace Madj2k\AiAssistant\Backend\Configuration;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class BackendConfigurationProvider
 *
 * Builds backend view data for the remaining central configuration values.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final class BackendConfigurationProvider
{
    /**
     * @param \Madj2k\AiAssistant\Backend\Configuration\BackendRegistryService $registryService Registry service.
     * @param \Madj2k\AiAssistant\Backend\Configuration\BackendRegistryFieldProvider $registryFieldProvider Registry field provider.
     */
    public function __construct(
        private readonly BackendRegistryService $registryService,
        private readonly BackendRegistryFieldProvider $registryFieldProvider
    ) {
    }


    /**
     * Builds configuration view data.
     *
     * @param object $extbaseRequest Extbase request.
     * @param \Psr\Http\Message\ServerRequestInterface $backendRequest Backend request.
     * @param array<string, mixed> $state Module state.
     * @return array<string, mixed> View data.
     */
    public function getViewData(object $extbaseRequest, ServerRequestInterface $backendRequest, array $state): array
    {
        /** @var array<string, string> $submittedValues */
        $submittedValues = (array)($state['submittedValues'] ?? []);

        /** @var array<string, string> $fieldErrors */
        $fieldErrors = (array)($state['fieldErrors'] ?? []);

        /** @var array<int, array<string, mixed>> $fields */
        $fields = [];

        foreach ($this->registryFieldProvider->getFields() as $field) {
            /** @var string $key */
            $key = (string)$field['key'];

            /** @var string $default */
            $default = (string)($field['default'] ?? '');

            /** @var string $value */
            $value = array_key_exists($key, $submittedValues)
                ? (string)$submittedValues[$key]
                : (string)$this->registryService->get($key, $default);

            $field['value'] = $value;
            $field['isSet'] = trim((string)$this->registryService->get($key, '')) !== '';
            $field['hasError'] = isset($fieldErrors[$key]);
            $field['statusClass'] = $field['hasError'] ? 'error' : ($field['isSet'] ? 'ok' : 'empty');
            $field['statusLabel'] = $field['hasError'] ? 'Invalid' : ($field['isSet'] ? 'Configured' : 'Default');

            $fields[] = $field;
        }

        return [
            'registryFields' => $fields,
            'fieldErrors' => $fieldErrors,
            'submittedValues' => $submittedValues,
        ];
    }
}
