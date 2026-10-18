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

namespace Madj2k\AiAssistant\Assistant\Domain\Model;

/**
 * Class PresentationRule
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final class PresentationRule extends AbstractEntity
{
    /**
     * Human-readable title.
     *
     * @var string
     */
    protected string $title = '';

    /**
     * Stable rule identifier.
     *
     * @var string
     */
    protected string $identifier = '';

    /**
     * Prompt instructions.
     *
     * @var string
     */
    protected string $content = '';

    /**
     * @return string Human-readable title.
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title Human-readable title.
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string Stable rule identifier.
     */
    public function getIdentifier(): string
    {
        return trim($this->identifier);
    }

    /**
     * @param string $identifier Stable rule identifier.
     * @return void
     */
    public function setIdentifier(string $identifier): void
    {
        $this->identifier = trim($identifier);
    }

    /**
     * @return string Prompt instructions.
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content Prompt instructions.
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}
