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

namespace Madj2k\AiAssistant\Assistant\Log;

use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile;

/**
 * Class ChatPipelineLogContext
 *
 * Carries the trace identity for one chat request. The context is intentionally
 * small so it can be passed through legacy and new chat services without coupling
 * them to a specific pipeline implementation.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package ai_assistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PipelineLogMetaData
{
    /**
     * Unique trace identifier shared by all log events of one chat request.
     *
     * @var string
     */
    protected string $traceId = '';


    /**
     * Original user query.
     *
     * @var string
     */
    protected string $query = '';


    /**
     * Logical conversation identifier, for example an assistant-profile scope.
     *
     * @var string
     */
    protected string $chatIdentifier = '';


    /**
     * assistant profile.
     *
     * @var \Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile|null
     */
    protected ?AssistantProfile $assistantProfile = null;


    /**
     * Current route or pipeline mode.
     *
     * @var string
     */
    protected string $route = '';


    /**
     * Microtime at which the trace was created.
     *
     * @var float
     */
    protected float $startedAt = 0.0;


    /**
     * @param string $traceId Trace identifier.
     * @param string $query Original user query.
     * @param string $chatIdentifier Conversation identifier.
     * @param AssistantProfile $assistantProfile
     * @param string $route Route name.
     */
    public function __construct(
        string $traceId,
        string $query,
        string $chatIdentifier,
        AssistantProfile $assistantProfile,
        string $route = '',
    ) {
        $this->traceId = $traceId;
        $this->query = $query;
        $this->chatIdentifier = $chatIdentifier;
        $this->assistantProfile = $assistantProfile;
        $this->route = $route;
        $this->startedAt = microtime(true);
    }


    /**
     * @return string Trace identifier.
     */
    public function getTraceId(): string
    {
        return $this->traceId;
    }


    /**
     * @return string Original user query.
     */
    public function getQuery(): string
    {
        return $this->query;
    }


    /**
     * @return string Conversation identifier.
     */
    public function getChatIdentifier(): string
    {
        return $this->chatIdentifier;
    }


    /**
     * Updates the conversation identifier.
     *
     * @param string $chatIdentifier Conversation identifier.
     * @return void
     */
    public function setChatIdentifier(string $chatIdentifier): void
    {
        $this->chatIdentifier = trim($chatIdentifier);
    }


    /**
     * @return \Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile
     */
    public function getAssistantProfile(): AssistantProfile
    {
        return $this->assistantProfile;
    }


    /**
     * Updates the resolved assistant profile data.
     *
     * @param \Madj2k\AiAssistant\Assistant\Domain\Model\AssistantProfile $assistantProfile
     * @return void
     */
    public function setAssistantProfile(AssistantProfile $assistantProfile): void
    {
        $this->assistantProfile = $assistantProfile;
    }


    /**
     * @return string Route name.
     */
    public function getRoute(): string
    {
        return $this->route;
    }


    /**
     * Updates the current route.
     *
     * @param string $route Route name.
     * @return void
     */
    public function setRoute(string $route): void
    {
        $this->route = trim($route);
    }


    /**
     * @return float Start time as microtime.
     */
    public function getStartedAt(): float
    {
        return $this->startedAt;
    }


    /**
     * Converts the context into payload data for trace events.
     *
     * @return array<string,mixed>
     */
    public function toPayload(): array
    {
        return [
            'trace_id' => $this->traceId,
            'query' => $this->query,
            'conversation_identifier' => $this->chatIdentifier,
            'assistant_profile_uid' => $this->assistantProfile->getUid(),
            'route' => $this->route,
        ];
    }
}
