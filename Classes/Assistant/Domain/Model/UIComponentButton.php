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
 * Class UIComponentButton
 *
 * Domain model for one configurable UI component button.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final class UIComponentButton extends AbstractEntity
{
    /**
     * Visible button label.
     *
     * @var string
     */
    protected string $label = '';


    /**
     * Button type.
     *
     * @var string
     */
    protected string $type = 'prompt';


    /**
     * Link URL.
     *
     * @var string
     */
    protected string $url = '';


    /**
     * Prompt template.
     *
     * @var string
     */
    protected string $promptTemplate = '';


    /**
     * Comma- or line-separated placeholder names.
     *
     * @var string
     */
    protected string $placeholders = '';


    /**
     * Returns the visible button label.
     *
     * @return string Button label.
     */
    public function getLabel(): string
    {
        return trim($this->label);
    }


    /**
     * Sets the visible button label.
     *
     * @param string $label Button label.
     * @return void
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }


    /**
     * Returns the button type.
     *
     * @return string Button type.
     */
    public function getType(): string
    {
        return $this->type;
    }


    /**
     * Sets the button type.
     *
     * @param string $type Button type.
     * @return void
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }


    /**
     * Returns the link URL.
     *
     * @return string URL.
     */
    public function getUrl(): string
    {
        return trim($this->url);
    }


    /**
     * Sets the link URL.
     *
     * @param string $url URL.
     * @return void
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }


    /**
     * Returns the prompt template.
     *
     * @return string Prompt template.
     */
    public function getPromptTemplate(): string
    {
        return $this->promptTemplate;
    }


    /**
     * Sets the prompt template.
     *
     * @param string $promptTemplate Prompt template.
     * @return void
     */
    public function setPromptTemplate(string $promptTemplate): void
    {
        $this->promptTemplate = $promptTemplate;
    }


    /**
     * Returns the configured placeholder names.
     *
     * @return array<int,string> Placeholder names.
     */
    public function getPlaceholderList(): array
    {
        return array_values(array_filter(array_map(
            'trim',
            preg_split('/[,\r\n]+/', $this->placeholders) ?: []
        )));
    }


    /**
     * Sets the configured placeholder names.
     *
     * @param string $placeholders Placeholder names.
     * @return void
     */
    public function setPlaceholders(string $placeholders): void
    {
        $this->placeholders = $placeholders;
    }
}
