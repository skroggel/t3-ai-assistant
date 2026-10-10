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

namespace Madj2k\AiAssistant\Assistant\UIComponents;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Madj2k\AiCore\Assistant\Configuration\PipelineStepConfigurationInterface;
use Madj2k\AiCore\Assistant\Context\Context;
use Madj2k\AiCore\Assistant\UIComponents\Action;
use Madj2k\AiCore\Assistant\UIComponents\Definition;
use Madj2k\AiCore\Assistant\UIComponents\Defaults\Provider as DefaultProvider;
use Madj2k\AiCore\Assistant\UIComponents\ProviderInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Class Provider
 *
 * Resolves TYPO3 UI component records for the effective pipeline step.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final readonly class Provider implements ProviderInterface
{
    /**
     * Constructor.
     *
     * @param \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool TYPO3 database connection pool.
     */
    public function __construct(
        private ConnectionPool $connectionPool,
        private DefaultProvider $defaultProvider,
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function getDefinitions(Context $context, PipelineStepConfigurationInterface $step): array
    {
        $profileUid = $context->getAssistant()->getUid();
        $profileUids = $this->readUidList('tx_aiassistant_assistant_profile', 'ui_components', $profileUid);
        $stepUids = method_exists($step, 'getUiComponentUids') ? $step->getUiComponentUids() : [];
        $mode = method_exists($step, 'getUiComponentsMode') ? $step->getUiComponentsMode() : 'inherit';

        $componentUids = match ($mode) {
            'replace' => $stepUids,
            'extend' => array_values(array_unique([...$profileUids, ...$stepUids])),
            default => $profileUids,
        };

        return $this->loadDefinitions($componentUids);
    }

    /**
     * Returns all components that can occur in a profile pipeline response.
     *
     * @param int $profileUid Assistant profile UID.
     * @return array<int,Definition> Frontend component definitions.
     * @throws \Doctrine\DBAL\Exception
     */
    public function getDefinitionsForProfile(int $profileUid): array
    {
        $componentUids = $this->readUidList('tx_aiassistant_assistant_profile', 'ui_components', $profileUid);
        if ($profileUid > 0) {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_aiassistant_assistant_pipeline_step');
            $stepRows = $queryBuilder
                ->select('ui_components')
                ->from('tx_aiassistant_assistant_pipeline_step')
                ->where($queryBuilder->expr()->eq('assistant_profile', $queryBuilder->createNamedParameter($profileUid, ParameterType::INTEGER)))
                ->executeQuery()
                ->fetchAllAssociative();
            foreach ($stepRows as $stepRow) {
                $componentUids = array_merge(
                    $componentUids,
                    array_map('intval', array_filter(array_map('trim', explode(',', (string)($stepRow['ui_components'] ?? ''))))),
                );
            }
        }

        return $this->loadDefinitions(array_values(array_unique(array_filter($componentUids))));
    }

    /**
     * Loads component definitions by record UIDs.
     *
     * @param array<int,int> $uids Component record UIDs.
     * @return array<int,Definition> Component definitions.
     * @throws \Doctrine\DBAL\Exception
     */
    private function loadDefinitions(array $uids): array
    {
        if ($uids === []) {
            return [];
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_aiassistant_ui_component');
        $rows = $queryBuilder
            ->select('uid', 'title', 'preset', 'css_class', 'identifier', 'description', 'template', 'actions', 'data_schema', 'placeholders', 'override_description', 'override_template', 'override_actions', 'override_data_schema', 'override_placeholders')
            ->from('tx_aiassistant_ui_component')
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($uids, ArrayParameterType::INTEGER),
                ),
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $definitions = [];
        foreach ($rows as $row) {
            $preset = trim((string)($row['preset'] ?? 'custom'));
            if ($preset !== '' && $preset !== 'custom') {
                $definition = $this->defaultProvider->get($preset);
                if ($definition instanceof Definition) {
                    $definition = $preset === 'buttons'
                        ? $definition->withActions($this->loadButtonActions((int)($row['uid'] ?? 0)))
                        : $definition;
                    $definition = $definition->withOverrides($this->createOverrides($row));
                    $definitions[] = $definition
                        ->withIdentifier((string)($row['identifier'] ?? $definition->identifier))
                        ->withCssClass((string)($row['css_class'] ?? ''))
                        ->withTitle((string)($row['title'] ?? ''));
                }
                continue;
            }

            $identifier = trim((string)($row['identifier'] ?? ''));
            if ($identifier === '') {
                continue;
            }

            $actions = json_decode((string)($row['actions'] ?? ''), true);
            $actionObjects = [];
            foreach (is_array($actions) ? $actions : [] as $action) {
                if (!is_array($action) || trim((string)($action['identifier'] ?? '')) === '') {
                    continue;
                }
                $actionObjects[] = new Action(
                    trim((string)$action['identifier']),
                    (string)($action['promptTemplate'] ?? $action['prompt_template'] ?? ''),
                    $this->splitList((string)($action['placeholders'] ?? '')),
                    trim((string)($action['label'] ?? $action['identifier'] ?? '')),
                    trim((string)($action['type'] ?? 'prompt')),
                    trim((string)($action['url'] ?? '')),
                );
            }

            $schema = json_decode((string)($row['data_schema'] ?? ''), true);
            $definitions[] = new Definition(
                $identifier,
                trim((string)($row['description'] ?? '')),
                (string)($row['template'] ?? ''),
                $actionObjects,
                is_array($schema) ? $schema : [],
                $this->splitList((string)($row['placeholders'] ?? '')),
                trim((string)($row['css_class'] ?? '')),
                trim((string)($row['title'] ?? '')),
            );
        }

        return $definitions;
    }

    /**
     * Creates non-empty definition overrides from a TYPO3 record.
     *
     * @param array<string,mixed> $row Component record.
     * @return array<string,mixed> Definition overrides.
     */
    private function createOverrides(array $row): array
    {
        $actions = json_decode((string)($row['override_actions'] ?? ''), true);
        $schema = json_decode((string)($row['override_data_schema'] ?? ''), true);

        return [
            'description' => (string)($row['override_description'] ?? ''),
            'template' => (string)($row['override_template'] ?? ''),
            'actions' => $this->createActions(is_array($actions) ? $actions : []),
            'dataSchema' => is_array($schema) ? $schema : [],
            'placeholders' => $this->splitList((string)($row['override_placeholders'] ?? '')),
            'cssClass' => (string)($row['css_class'] ?? ''),
        ];
    }

    /**
     * Converts JSON action definitions to core actions.
     *
     * @param array<int,mixed> $actions Raw actions.
     * @return array<int,Action> Actions.
     */
    private function createActions(array $actions): array
    {
        $actionObjects = [];
        foreach ($actions as $action) {
            if (!is_array($action) || trim((string)($action['identifier'] ?? '')) === '') {
                continue;
            }
            $actionObjects[] = new Action(
                trim((string)$action['identifier']),
                (string)($action['promptTemplate'] ?? $action['prompt_template'] ?? ''),
                $this->splitList((string)($action['placeholders'] ?? '')),
                trim((string)($action['label'] ?? $action['identifier'] ?? '')),
            );
        }

        return $actionObjects;
    }

    /**
     * Loads the configured button actions of one component record.
     *
     * @param int $componentUid Component record UID.
     * @return array<int,Action> Button actions.
     * @throws \Doctrine\DBAL\Exception
     */
    private function loadButtonActions(int $componentUid): array
    {
        if ($componentUid <= 0) {
            return [];
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_aiassistant_ui_component_button');
        $rows = $queryBuilder
            ->select('uid', 'label', 'type', 'url', 'prompt_template', 'placeholders')
            ->from('tx_aiassistant_ui_component_button')
            ->where($queryBuilder->expr()->eq('ui_component', $queryBuilder->createNamedParameter($componentUid, ParameterType::INTEGER)))
            ->orderBy('sorting')
            ->executeQuery()
            ->fetchAllAssociative();

        return array_map(
            function (array $row): Action {
                return new Action(
                    'button-' . (int)($row['uid'] ?? 0),
                    (string)($row['prompt_template'] ?? ''),
                    $this->splitList((string)($row['placeholders'] ?? '')),
                    trim((string)($row['label'] ?? '')),
                    trim((string)($row['type'] ?? 'prompt')),
                    trim((string)($row['url'] ?? '')),
                );
            },
            $rows,
        );
    }

    /**
     * Reads a comma-separated UID field from one record.
     *
     * @param string $table Table name.
     * @param string $field Field name.
     * @param int $uid Record UID.
     * @return array<int,int> Related UIDs.
     * @throws \Doctrine\DBAL\Exception
     */
    private function readUidList(string $table, string $field, int $uid): array
    {
        if ($uid <= 0) {
            return [];
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $value = $queryBuilder
            ->select($field)
            ->from($table)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, ParameterType::INTEGER)))
            ->executeQuery()
            ->fetchOne();

        return array_values(array_filter(
            array_map(static fn (string $item): int => (int)trim($item), explode(',', (string)$value)),
            static fn (int $item): bool => $item > 0,
        ));
    }

    /**
     * Splits a comma-separated list.
     *
     * @param string $value List value.
     * @return array<int,string> Normalized values.
     */
    private function splitList(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}
