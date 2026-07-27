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

namespace Madj2k\AiAssistant\Assistant\Context\Answer;

/**
 * Class AnswerState
 *
 * Holds the answer candidate and the final answer.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class AnswerState
{
    /**
     * Answer candidate.
     *
     * @var string
     */
    protected string $candidate = '';


    /**
     * Final answer.
     *
     * @var string
     */
    protected string $final = '';


    /**
     * Returns the answer candidate.
     *
     * @return string Answer candidate.
     */
    public function getCandidate(): string
    {
        return $this->candidate;
    }


    /**
     * Sets the answer candidate.
     *
     * @param string $candidate Answer candidate.
     * @return void
     */
    public function setCandidate(string $candidate): void
    {
        $this->candidate = trim($candidate);
    }


    /**
     * Returns the final answer.
     *
     * @return string Final answer.
     */
    public function getFinal(): string
    {
        return $this->final;
    }


    /**
     * Sets the final answer.
     *
     * @param string $final Final answer.
     * @return void
     */
    public function setFinal(string $final): void
    {
        $this->final = trim($final);
    }
}
