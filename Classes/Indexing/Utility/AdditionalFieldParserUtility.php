<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */


namespace Madj2k\AiAssistant\Indexing\Utility;

/**
 * Class AdditionalContentFieldParser
 *
 * Parses comma-separated table.field definitions for page indexing.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class AdditionalFieldParserUtility
{
    /**
     * Parses a comma-separated table.field list.
     *
     * @param string $fieldList Comma-separated field list.
     * @return array<string, array<int, string>> Fields grouped by table name.
     */
    public function parse(string $fieldList): array
    {
        /** @var array<string, array<int, string>> $fieldsByTable */
        $fieldsByTable = [];

        /** @var array<int, string> $items */
        $items = array_filter(array_map('trim', explode(',', $fieldList)));

        foreach ($items as $item) {
            if (!preg_match('/^(?<table>[a-zA-Z0-9_]+)\.(?<field>[a-zA-Z0-9_]+)$/', $item, $matches)) {
                continue;
            }

            /** @var string $table */
            $table = $matches['table'];

            /** @var string $field */
            $field = $matches['field'];

            if (!isset($fieldsByTable[$table])) {
                $fieldsByTable[$table] = [];
            }

            if (!in_array($field, $fieldsByTable[$table], true)) {
                $fieldsByTable[$table][] = $field;
            }
        }

        return $fieldsByTable;
    }
}
