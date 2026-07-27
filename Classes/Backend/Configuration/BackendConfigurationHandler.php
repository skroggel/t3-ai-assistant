<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension ai_assistant.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiAssistant\Backend\Configuration;


use Madj2k\AiAssistant\Backend\Response\BackendFlashMessageService;
use Madj2k\AiAssistant\Backend\Utility\SanitizerUtility;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

/**
 * Class BackendConfigurationHandler
 *
 * Handles writes for the remaining central backend configuration values.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class BackendConfigurationHandler
{
    /**
     * @param \Madj2k\AiAssistant\Backend\Configuration\BackendRegistryService $registryService Registry service.
     * @param \Madj2k\AiAssistant\Backend\Configuration\BackendRegistryFieldProvider $registryFieldProvider Registry field provider.
     * @param \Madj2k\AiAssistant\Backend\Response\BackendFlashMessageService $flashMessageService Flash message service.
     */
    public function __construct(
        private readonly BackendRegistryService $registryService,
        private readonly BackendRegistryFieldProvider $registryFieldProvider,
        private readonly BackendFlashMessageService $flashMessageService
    ) {
    }


    /**
     * Checks whether this handler should process the current request.
     *
     * @param object $request Extbase request.
     * @return bool True when supported.
     */
    public function supports(object $request): bool
    {
        return $this->hasRequestArgument($request, 'registry') || $this->hasRequestArgument($request, 'delete');
    }


    /**
     * Handles supported configuration actions.
     *
     * @param object $request Extbase request.
     * @param array<string, mixed> $state Mutable module state.
     * @return void
     */
    public function handle(object $request, array &$state): void
    {
        /** @var array<string, mixed> $submitted */
        $submitted = (array)$this->getRequestArgument($request, 'registry', []);

        /** @var array<string, mixed> $delete */
        $delete = (array)$this->getRequestArgument($request, 'delete', []);

        /** @var array<string, string> $fieldErrors */
        $fieldErrors = [];

        /** @var array<string, string> $submittedValues */
        $submittedValues = [];

        /** @var int $updated */
        $updated = 0;

        /** @var int $deleted */
        $deleted = 0;

        foreach ($this->registryFieldProvider->getFields() as $field) {
            /** @var string $key */
            $key = (string)$field['key'];

            if (isset($delete[$key]) && (bool)($field['allowDelete'] ?? true)) {
                $this->registryService->remove($key);
                $deleted++;
                continue;
            }

            if (!array_key_exists($key, $submitted)) {
                continue;
            }

            /** @var string $value */
            $value = SanitizerUtility::cleanString((string)$submitted[$key], PHP_INT_MAX);
            $submittedValues[$key] = $value;

            if (($field['type'] ?? 'text') === 'select') {
                /** @var array<int, string> $allowedValues */
                $allowedValues = array_map(
                    static fn (array $option): string => (string)($option['value'] ?? ''),
                    (array)($field['options'] ?? [])
                );

                if (!in_array($value, $allowedValues, true)) {
                    $fieldErrors[$key] = 'invalidOption';
                    continue;
                }
            }

            if (($field['type'] ?? 'text') === 'number' && $value !== '' && !ctype_digit($value)) {
                $fieldErrors[$key] = 'invalidNumber';
                continue;
            }

            if (strlen($value) > (int)($field['maxLength'] ?? 255)) {
                $fieldErrors[$key] = 'maxLength';
                continue;
            }

            if ($value === '') {
                $value = (string)($field['default'] ?? '');
            }

            $this->registryService->set($key, $value);
            $updated++;
        }

        $state['fieldErrors'] = $fieldErrors;
        $state['submittedValues'] = $submittedValues;

        if ($fieldErrors !== []) {
            $this->flashMessageService->add(
                'Some settings could not be saved. Please check the highlighted fields.',
                '',
                ContextualFeedbackSeverity::ERROR
            );
            return;
        }

        $this->flashMessageService->add(
            sprintf('Saved %d setting(s), removed %d setting(s).', $updated, $deleted)
        );
    }


    /**
     * Returns whether a request argument exists.
     *
     * @param object $request PSR-7 backend request or Extbase request.
     * @param string $name Argument name.
     * @return bool True when the argument exists.
     */
    private function hasRequestArgument(object $request, string $name): bool
    {
        return $this->getRequestArgument($request, $name, null) !== null;
    }


    /**
     * Resolves a request argument from PSR-7, Extbase or raw POST data.
     *
     * @param object $request PSR-7 backend request or Extbase request.
     * @param string $name Argument name.
     * @param mixed $default Default value.
     * @return mixed Argument value.
     */
    private function getRequestArgument(object $request, string $name, mixed $default = null): mixed
    {
        if ($request instanceof ServerRequestInterface) {
            $parsedBody = $request->getParsedBody();
            if (is_array($parsedBody) && array_key_exists($name, $parsedBody)) {
                return $parsedBody[$name];
            }
        }

        if (method_exists($request, 'hasArgument') && method_exists($request, 'getArgument') && $request->hasArgument($name)) {
            return $request->getArgument($name);
        }

        if (array_key_exists($name, $_POST)) {
            return $_POST[$name];
        }

        return $default;
    }
}
