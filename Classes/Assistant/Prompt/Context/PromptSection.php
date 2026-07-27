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

/**
 * Class PromptSection
 *
 * Represents one structured section of a prompt context before final formatting.
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final readonly class PromptSection
{
    /**
     * Constructor.
     *
     * @param string $title Section title.
     * @param string $content Section content.
     * @param int $priority Sorting priority.
     */
    public function __construct(
        public string $title,
        public string $content,
        public int $priority = 100
    ) {
    }


    /**
     * Returns whether the section has no usable title or content.
     *
     * @return bool Empty section flag.
     */
    public function isEmpty(): bool
    {
        return trim($this->title) === '' || trim($this->content) === '';
    }


    /**
     * Converts the section to the prompt text format.
     *
     * @return string Formatted section.
     */
    public function toText(): string
    {
        return '[' . trim($this->title) . ']' . "\n" . trim($this->content);
    }
}
