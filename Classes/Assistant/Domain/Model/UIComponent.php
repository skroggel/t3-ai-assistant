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

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class UIComponent
 *
 * Domain model for a reusable TYPO3 UI component definition.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final class UIComponent extends AbstractEntity
{
    /**
     * Human-readable title.
     *
     * @var string
     */
    protected string $title = '';


    /**
     * Selected default preset or custom.
     *
     * @var string
     */
    protected string $preset = 'custom';


    /**
     * Additional wrapper CSS class.
     *
     * @var string
     */
    protected string $cssClass = '';


    /**
     * Stable technical identifier.
     *
     * @var string
     */
    protected string $identifier = '';


    /**
     * Description for the AI.
     *
     * @var string
     */
    protected string $description = '';


    /**
     * Custom HTML template.
     *
     * @var string
     */
    protected string $template = '';


    /**
     * Custom action JSON.
     *
     * @var string
     */
    protected string $actions = '';


    /**
     * Custom data schema JSON.
     *
     * @var string
     */
    protected string $dataSchema = '';


    /**
     * Custom placeholder list.
     *
     * @var string
     */
    protected string $placeholders = '';


    /**
     * Preset description override.
     *
     * @var string
     */
    protected string $overrideDescription = '';


    /**
     * Preset template override.
     *
     * @var string
     */
    protected string $overrideTemplate = '';


    /**
     * Preset action override JSON.
     *
     * @var string
     */
    protected string $overrideActions = '';


    /**
     * Preset data schema override.
     *
     * @var string
     */
    protected string $overrideDataSchema = '';


    /**
     * Preset placeholder override.
     *
     * @var string
     */
    protected string $overridePlaceholders = '';


    /**
     * Configured button child records.
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Madj2k\AiAssistant\Assistant\Domain\Model\UIComponentButton>
     */
    protected ObjectStorage $buttonActions;


    /**
     * Initializes the button child collection.
     */
    public function __construct()
    {
        $this->buttonActions = new ObjectStorage();
    }


    /**
     * Returns the human-readable title.
     *
     * @return string Human-readable title.
     */
    public function getTitle(): string
    {
        return $this->title;
    }


    /**
     * Sets the human-readable title.
     *
     * @param string $title Human-readable title.
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }


    /**
     * Returns the selected preset.
     *
     * @return string Preset identifier.
     */
    public function getPreset(): string
    {
        return $this->preset;
    }


    /**
     * Sets the selected preset.
     *
     * @param string $preset Preset identifier.
     * @return void
     */
    public function setPreset(string $preset): void
    {
        $this->preset = $preset;
    }


    /**
     * Returns the additional wrapper CSS class.
     *
     * @return string CSS class.
     */
    public function getCssClass(): string
    {
        return trim($this->cssClass);
    }


    /**
     * Sets the additional wrapper CSS class.
     *
     * @param string $cssClass CSS class.
     * @return void
     */
    public function setCssClass(string $cssClass): void
    {
        $this->cssClass = $cssClass;
    }


    /**
     * Returns the stable technical identifier.
     *
     * @return string Technical identifier.
     */
    public function getIdentifier(): string
    {
        return trim($this->identifier);
    }


    /**
     * Sets the stable technical identifier.
     *
     * @param string $identifier Technical identifier.
     * @return void
     */
    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }


    /**
     * Returns the AI description.
     *
     * @return string AI description.
     */
    public function getDescription(): string
    {
        return $this->description;
    }


    /**
     * Sets the AI description.
     *
     * @param string $description AI description.
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }


    /**
     * Returns the custom HTML template.
     *
     * @return string HTML template.
     */
    public function getTemplate(): string
    {
        return $this->template;
    }


    /**
     * Sets the custom HTML template.
     *
     * @param string $template HTML template.
     * @return void
     */
    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }


    /**
     * Returns the custom action JSON.
     *
     * @return string Action JSON.
     */
    public function getActions(): string
    {
        return $this->actions;
    }


    /**
     * Sets the custom action JSON.
     *
     * @param string $actions Action JSON.
     * @return void
     */
    public function setActions(string $actions): void
    {
        $this->actions = $actions;
    }


    /**
     * Returns the custom data schema JSON.
     *
     * @return string Data schema JSON.
     */
    public function getDataSchema(): string
    {
        return $this->dataSchema;
    }


    /**
     * Sets the custom data schema JSON.
     *
     * @param string $dataSchema Data schema JSON.
     * @return void
     */
    public function setDataSchema(string $dataSchema): void
    {
        $this->dataSchema = $dataSchema;
    }


    /**
     * Returns the custom placeholder list.
     *
     * @return string Placeholder list.
     */
    public function getPlaceholders(): string
    {
        return $this->placeholders;
    }


    /**
     * Sets the custom placeholder list.
     *
     * @param string $placeholders Placeholder list.
     * @return void
     */
    public function setPlaceholders(string $placeholders): void
    {
        $this->placeholders = $placeholders;
    }


    /**
     * Returns the preset description override.
     *
     * @return string Description override.
     */
    public function getOverrideDescription(): string
    {
        return $this->overrideDescription;
    }


    /**
     * Sets the preset description override.
     *
     * @param string $value Description override.
     * @return void
     */
    public function setOverrideDescription(string $value): void
    {
        $this->overrideDescription = $value;
    }


    /**
     * Returns the preset template override.
     *
     * @return string Template override.
     */
    public function getOverrideTemplate(): string
    {
        return $this->overrideTemplate;
    }


    /**
     * Sets the preset template override.
     *
     * @param string $value Template override.
     * @return void
     */
    public function setOverrideTemplate(string $value): void
    {
        $this->overrideTemplate = $value;
    }


    /**
     * Returns the preset action override JSON.
     *
     * @return string Action override JSON.
     */
    public function getOverrideActions(): string
    {
        return $this->overrideActions;
    }


    /**
     * Sets the preset action override JSON.
     *
     * @param string $value Action override JSON.
     * @return void
     */
    public function setOverrideActions(string $value): void
    {
        $this->overrideActions = $value;
    }


    /**
     * Returns the preset data schema override.
     *
     * @return string Data schema override.
     */
    public function getOverrideDataSchema(): string
    {
        return $this->overrideDataSchema;
    }


    /**
     * Sets the preset data schema override.
     *
     * @param string $value Data schema override.
     * @return void
     */
    public function setOverrideDataSchema(string $value): void
    {
        $this->overrideDataSchema = $value;
    }


    /**
     * Returns the preset placeholder override.
     *
     * @return string Placeholder override.
     */
    public function getOverridePlaceholders(): string
    {
        return $this->overridePlaceholders;
    }


    /**
     * Sets the preset placeholder override.
     *
     * @param string $value Placeholder override.
     * @return void
     */
    public function setOverridePlaceholders(string $value): void
    {
        $this->overridePlaceholders = $value;
    }


    /**
     * Returns configured button child records.
     *
     * @return ObjectStorage<UIComponentButton> Button children.
     */
    public function getButtonActions(): ObjectStorage
    {
        return $this->buttonActions;
    }


    /**
     * Sets configured button child records.
     *
     * @param ObjectStorage<UIComponentButton> $buttonActions Button children.
     * @return void
     */
    public function setButtonActions(ObjectStorage $buttonActions): void
    {
        $this->buttonActions = $buttonActions;
    }
}
