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
 */

namespace Madj2k\AiAssistant\Assistant\Presentation\UIComponents;

use Madj2k\AiAssistant\Assistant\Domain\Model\UIComponent;
use Madj2k\AiAssistant\Assistant\Domain\Model\UIComponentButton;
use Madj2k\AiAssistant\Assistant\Domain\Repository\AssistantProfileRepository;
use Madj2k\AiAssistant\Assistant\Domain\Repository\UIComponentRepository;
use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\Presentation\UIComponents\Action;
use Madj2k\AiCore\Assistant\Presentation\UIComponents\Definition;
use Madj2k\AiCore\Assistant\Presentation\UIComponents\Defaults\Provider as DefaultProvider;
use Madj2k\AiCore\Assistant\Presentation\UIComponents\UIComponentProviderInterface;

/**
 * Class UIComponentProvider
 *
 * Resolves TYPO3 UI component records through Extbase repositories and merges
 * presets, overrides and button child records into Core definitions.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final readonly class UIComponentProvider implements UIComponentProviderInterface
{
    /**
     * Constructor.
     *
     * @param \Madj2k\AiAssistant\Assistant\Domain\Repository\AssistantProfileRepository $profileRepository Assistant profile repository.
     * @param \Madj2k\AiAssistant\Assistant\Domain\Repository\UIComponentRepository $componentRepository UI component repository.
     * @param \Madj2k\AiCore\Assistant\Presentation\UIComponents\Defaults\Provider $defaultProvider Core default provider.
     */
    public function __construct(
        private AssistantProfileRepository $profileRepository,
        private UIComponentRepository $componentRepository,
        private DefaultProvider $defaultProvider,
    ) {
    }

    /** @inheritDoc */
    public function getDefinitions(Context $context, PipelineStepConfigurationInterface $step): array
    {
        $profile = $this->profileRepository->findByUid($context->getAssistant()->getUid());
        $profileUids = $profile?->getUiComponentUids() ?? [];
        $stepUids = method_exists($step, 'getUiComponentUids') ? $step->getUiComponentUids() : [];
        $mode = method_exists($step, 'getUiComponentsMode') ? $step->getUiComponentsMode() : 'inherit';
        $uids = match ($mode) {
            'replace' => $stepUids,
            'extend' => array_values(array_unique([...$profileUids, ...$stepUids])),
            default => $profileUids,
        };

        return $this->loadDefinitions($uids);
    }

    /**
     * Returns all components that can occur in a profile pipeline response.
     *
     * @param int $profileUid Assistant profile UID.
     * @return array<int,Definition> Component definitions.
     */
    public function getDefinitionsForProfile(int $profileUid): array
    {
        $profile = $this->profileRepository->findByUid($profileUid);
        $uids = $profile?->getUiComponentUids() ?? [];
        foreach ($profile?->getChatPipelineSteps() ?? [] as $step) {
            if (method_exists($step, 'getUiComponentUids')) {
                $uids = array_merge($uids, $step->getUiComponentUids());
            }
        }

        return $this->loadDefinitions(array_values(array_unique(array_filter($uids))));
    }

    /**
     * @param array<int,int> $uids Component record UIDs.
     * @return array<int,Definition> Definitions.
     */
    private function loadDefinitions(array $uids): array
    {
        $definitions = [];
        foreach ($uids as $uid) {
            $record = $this->componentRepository->findByUid($uid);
            if (!$record instanceof UIComponent) {
                continue;
            }
            $definitions[] = $this->createDefinition($record);
        }
        return $definitions;
    }

    /**
     * Creates a Core definition from one TYPO3 record.
     *
     * @param UIComponent $record Component record.
     * @return Definition Definition.
     */
    private function createDefinition(UIComponent $record): Definition
    {
        $preset = trim($record->getPreset());
        $definition = $preset !== 'custom' ? $this->defaultProvider->get($preset) : null;
        if (!$definition instanceof Definition) {
            $definition = new Definition(
                $record->getIdentifier(),
                $record->getDescription(),
                $record->getTemplate(),
                $this->parseActions($record->getActions()),
                $this->parseJson($record->getDataSchema()),
                $this->splitList($record->getPlaceholders()),
                $record->getCssClass(),
                $record->getTitle(),
            );
        } else {
            $definition = $preset === 'buttons'
                ? $definition->withActions($this->createButtonActions($record))
                : $definition;
            $definition = $definition
                ->withOverrides([
                    'description' => $record->getOverrideDescription(),
                    'template' => $record->getOverrideTemplate(),
                    'actions' => $this->parseActions($record->getOverrideActions()),
                    'dataSchema' => $this->parseJson($record->getOverrideDataSchema()),
                    'placeholders' => $this->splitList($record->getOverridePlaceholders()),
                    'cssClass' => $record->getCssClass(),
                ])
                ->withType($preset)
                ->withIdentifier($record->getIdentifier())
                ->withCssClass($record->getCssClass())
                ->withTitle($record->getTitle());
        }

        return $definition;
    }

    /** @return array<int,Action> */
    private function createButtonActions(UIComponent $record): array
    {
        $actions = [];
        foreach ($record->getButtonActions() as $button) {
            if ($button instanceof UIComponentButton) {
                $actions[] = new Action(
                    'button-' . $button->getUid(),
                    $button->getPromptTemplate(),
                    $button->getPlaceholderList(),
                    $button->getLabel(),
                    $button->getType(),
                    $button->getUrl(),
                );
            }
        }
        return $actions;
    }

    /** @return array<int,Action> */
    private function parseActions(string $json): array
    {
        $raw = json_decode($json, true);
        if (!is_array($raw)) return [];
        $actions = [];
        foreach ($raw as $action) {
            if (!is_array($action) || trim((string)($action['identifier'] ?? '')) === '') continue;
            $actions[] = new Action(
                trim((string)$action['identifier']),
                (string)($action['promptTemplate'] ?? $action['prompt_template'] ?? ''),
                $this->splitList((string)($action['placeholders'] ?? '')),
                trim((string)($action['label'] ?? $action['identifier'])),
                trim((string)($action['type'] ?? 'prompt')),
                trim((string)($action['url'] ?? '')),
            );
        }
        return $actions;
    }

    /** @return array<string,mixed> */
    private function parseJson(string $json): array
    {
        $value = json_decode($json, true);
        return is_array($value) ? $value : [];
    }

    /** @return array<int,string> */
    private function splitList(string $value): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/[,\r\n]+/', $value) ?: [])));
    }
}
