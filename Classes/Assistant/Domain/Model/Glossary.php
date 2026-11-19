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

namespace Madj2k\AiAssistant\Assistant\Domain\Model;

/**
 * Class Glossary
 *
 * One complete, language-specific glossary mapping source terms to target terms.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final class Glossary extends AbstractEntity
{
    /** @var string Stable glossary identifier. */
    protected string $identifier = '';

    /** @var string Human-readable glossary title. */
    protected string $title = '';

    /** @var string Complete source-to-target mapping list. */
    protected string $content = '';

    /**
     * Returns the stable glossary identifier.
     *
     * @return string Glossary identifier.
     */
    public function getIdentifier(): string
    {
        return trim($this->identifier);
    }

    /**
     * Sets the stable glossary identifier.
     *
     * @param string $identifier Glossary identifier.
     * @return void
     */
    public function setIdentifier(string $identifier): void
    {
        $this->identifier = trim($identifier);
    }

    /**
     * Returns the glossary title.
     *
     * @return string Glossary title.
     */
    public function getTitle(): string
    {
        return trim($this->title);
    }

    /**
     * Sets the glossary title.
     *
     * @param string $title Glossary title.
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = trim($title);
    }

    /**
     * Returns the complete source-to-target mapping list.
     *
     * @return string Glossary content.
     */
    public function getContent(): string
    {
        return trim($this->content);
    }

    /**
     * Sets the complete source-to-target mapping list.
     *
     * @param string $content Glossary content.
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}
