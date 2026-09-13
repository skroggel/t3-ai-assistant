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
 * Class PipelineTrace
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
class PipelineTrace extends AbstractEntity
{

    /**
     * @var string
     */
    protected string $level = '';


    /**
     * @var string
     */
    protected string $eventName = '';


    /**
     * @var string
     */
    protected string $route = '';


    /**
     * @var string
     */
    protected string $chatIdentifier = '';


    /**
     * @var string
     */
    protected string $traceId = '';


    /**
     * @var string
     */
    protected string $stepTitle = '';


    /**
     * @var string
     */
    protected string $processorType = '';


    /**
     * @var int
     */
    protected int $durationMs = 0;


    /**
     * @var string
     */
    protected string $queryText = '';


    /**
     * @var string
     */
    protected string $payload = '';


    /**
     * Returns level.
     *
     * @return string level.
     */
    public function getLevel(): string
    {
        return $this->level;
    }


    /**
     * Sets level.
     *
     * @param string $level level.
     * @return void
     */
    public function setLevel(string $level): void
    {
        $this->level = $level;
    }


    /**
     * Returns event_name.
     *
     * @return string event_name.
     */
    public function getEventName(): string
    {
        return $this->eventName;
    }


    /**
     * Sets event_name.
     *
     * @param string $eventName event_name.
     * @return void
     */
    public function setEventName(string $eventName): void
    {
        $this->eventName = $eventName;
    }


    /**
     * Returns route.
     *
     * @return string route.
     */
    public function getRoute(): string
    {
        return $this->route;
    }


    /**
     * Sets route.
     *
     * @param string $route route.
     * @return void
     */
    public function setRoute(string $route): void
    {
        $this->route = $route;
    }


    /**
     * Returns conversation_identifier.
     *
     * @return string conversation_identifier.
     */
    public function getChatIdentifier(): string
    {
        return $this->chatIdentifier;
    }


    /**
     * Sets conversation_identifier.
     *
     * @param string $chatIdentifier conversation_identifier.
     * @return void
     */
    public function setChatIdentifier(string $chatIdentifier): void
    {
        $this->chatIdentifier = $chatIdentifier;
    }


    /**
     * Returns trace_id.
     *
     * @return string trace_id.
     */
    public function getTraceId(): string
    {
        return $this->traceId;
    }


    /**
     * Sets trace_id.
     *
     * @param string $traceId trace_id.
     * @return void
     */
    public function setTraceId(string $traceId): void
    {
        $this->traceId = trim($traceId);
    }


    /**
     * Returns step_title.
     *
     * @return string step_title.
     */
    public function getStepTitle(): string
    {
        return $this->stepTitle;
    }


    /**
     * Sets step_title.
     *
     * @param string $stepTitle step_title.
     * @return void
     */
    public function setStepTitle(string $stepTitle): void
    {
        $this->stepTitle = trim($stepTitle);
    }


    /**
     * Returns processor_type.
     *
     * @return string processor_type.
     */
    public function getProcessorType(): string
    {
        return $this->processorType;
    }


    /**
     * Sets processor_type.
     *
     * @param string $processorType processor_type.
     * @return void
     */
    public function setProcessorType(string $processorType): void
    {
        $this->processorType = trim($processorType);
    }


    /**
     * Returns duration_ms.
     *
     * @return int duration_ms.
     */
    public function getDurationMs(): int
    {
        return $this->durationMs;
    }


    /**
     * Sets duration_ms.
     *
     * @param int $durationMs duration_ms.
     * @return void
     */
    public function setDurationMs(int $durationMs): void
    {
        $this->durationMs = max(0, $durationMs);
    }


    /**
     * Returns query_text.
     *
     * @return string query_text.
     */
    public function getQueryText(): string
    {
        return $this->queryText;
    }


    /**
     * Sets query_text.
     *
     * @param string $queryText query_text.
     * @return void
     */
    public function setQueryText(string $queryText): void
    {
        $this->queryText = $queryText;
    }


    /**
     * Returns payload.
     *
     * @return string payload.
     */
    public function getPayload(): string
    {
        return $this->payload;
    }


    /**
     * Sets payload.
     *
     * @param string $payload payload.
     * @return void
     */
    public function setPayload(string $payload): void
    {
        $this->payload = $payload;
    }


}
