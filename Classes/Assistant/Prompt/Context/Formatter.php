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

namespace Madj2k\AiAssistant\Assistant\Prompt\Context;

use Madj2k\AiAssistant\Assistant\Prompt\Context\PromptSection;

/**
 * Class Formatter
 *
 * Formats prompt sections into the plain-text prompt context.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class Formatter
{
    /**
     * Formats prompt sections into a plain-text prompt context.
     *
     * @param array<int, \Madj2k\AiAssistant\Assistant\Prompt\Context\PromptSection> $sections Prompt sections.
     * @return string Formatted prompt context.
     */
    public function format(array $sections): string
    {
        $parts = [];

        foreach ($sections as $section) {
            if (!$section instanceof PromptSection || $section->isEmpty()) {
                continue;
            }

            $parts[] = $section->toText();
        }

        return implode("\n\n", $parts);
    }
}
