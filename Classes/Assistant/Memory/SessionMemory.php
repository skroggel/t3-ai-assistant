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

namespace Madj2k\AiAssistant\Assistant\Memory;

use Madj2k\AiAssistant\Assistant\Context\Retrieval\RetrievalResult;
use Madj2k\AiAssistant\Assistant\DTO\LastRetrievalResult;
use Madj2k\AiAssistant\Config\Config;
use Madj2k\AiAssistant\Exception\AppException;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Class ChatSessionMemory
 *
 * Stores the visible frontend conversation per chat identifier in session memory.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class SessionMemory
{
    private const string SESSION_KEY = 'aiassistant_frontend_conversations';


    /**
     * Starts or resets a conversation.
     *
     * @param string $chatIdentifier Conversation identifier.
     * @param int $chatStartTimestamp Frontend page timestamp.
     * @return void
     */
    public function start(string $chatIdentifier, int $chatStartTimestamp): void
    {
        $chatIdentifier = $this->normalizeIdentifier($chatIdentifier);
        $conversations = $this->loadConversations($chatIdentifier);

        $storedChatStartTimestamp = (int)($conversations[$chatIdentifier]['start_timestamp'] ?? 0);
        if ($storedChatStartTimestamp !== $chatStartTimestamp) {
            $conversations[$chatIdentifier]['start_timestamp'] = $chatStartTimestamp;
            $this->saveConversations($conversations);
        }
    }


    /**
     * Returns visible messages for a identifier.
     *
     * @param string $chatIdentifier Conversation identifier.
     * @return array<int,array{role:string,content:string}>
     */
    public function getMessages(string $chatIdentifier): array
    {
        $chatIdentifier = $this->normalizeIdentifier($chatIdentifier);
        $conversations = $this->loadConversations($chatIdentifier);
        $messages = $conversations[$chatIdentifier]['messages'] ?? [];
        if (!is_array($messages)) {
            return [];
        }

        return array_values(array_filter(
            $messages,
            static fn (mixed $message): bool => is_array($message)
                && in_array((string)($message['role'] ?? ''), ['user', 'assistant'], true)
                && trim((string)($message['content'] ?? '')) !== ''
        ));
    }


    /**
     * Adds one message to the conversation.
     *
     * @param string $chatIdentifier Conversation identifier.
     * @param string $role Message role.
     * @param string $content Message content.
     * @return void
     * @throws \Madj2k\AiAssistant\Exception\AppException
     */
    public function addMessage(string $chatIdentifier, string $role, string $content): void
    {
        $chatIdentifier = $this->normalizeIdentifier($chatIdentifier);
        $conversations = $this->loadConversations($chatIdentifier);
        $role = $role === 'assistant' ? 'assistant' : 'user';
        $content = trim($content);
        if ($content === '') {
            return;
        }

        $conversations[$chatIdentifier]['messages'][] = [
            'role' => $role,
            'content' => $content,
        ];

        $this->trimMessages($conversations, $chatIdentifier);
        $this->saveConversations($conversations);
    }


    /**
     * Stores the last retrieval result for a conversation.
     *
     * @param string $chatIdentifier Conversation identifier.
     * @param \Madj2k\AiAssistant\Assistant\Context\Retrieval\RetrievalResult $retrievalResult Retrieval result.
     * @return \Madj2k\AiAssistant\Assistant\DTO\LastRetrievalResult|null Stored retrieval result.
     */
    public function setLastRetrievalResult(string $chatIdentifier, RetrievalResult $retrievalResult): ?LastRetrievalResult
    {
        $chatIdentifier = $this->normalizeIdentifier($chatIdentifier);
        $conversations = $this->loadConversations($chatIdentifier);
        $documents = $retrievalResult->getResults();
        $rawData = $retrievalResult->getRawResults();

        if ($documents === [] && $rawData === []) {
            $conversations[$chatIdentifier]['lastRetrievalResult'] = [];
            $this->saveConversations($conversations);
            return null;
        }

        $lastRetrievalResult = new LastRetrievalResult(
            chatIdentifier: $chatIdentifier,
            retrievalIdentifier: $retrievalResult->getProcessorIdentifier(),
            documents: $documents,
            rawData: $rawData,
        );

        $conversations[$chatIdentifier]['lastRetrievalResult'] = $lastRetrievalResult->toArray();
        $this->saveConversations($conversations);

        return $lastRetrievalResult;
    }


    /**
     * Returns the last retrieval result for a conversation.
     *
     * @param string $chatIdentifier Conversation identifier.
     * @return \Madj2k\AiAssistant\Assistant\DTO\LastRetrievalResult|null Retrieval result.
     */
    public function getLastRetrievalResult(string $chatIdentifier): ?LastRetrievalResult
    {
        $chatIdentifier = $this->normalizeIdentifier($chatIdentifier);
        $conversations = $this->loadConversations($chatIdentifier);
        $result = $conversations[$chatIdentifier]['lastRetrievalResult'] ?? [];
        if (!is_array($result) || $result === []) {
            return null;
        }

        return new LastRetrievalResult(
            chatIdentifier: (string)($result['chatIdentifier'] ?? ''),
            retrievalIdentifier: (string)($result['retrievalIdentifier'] ?? ''),
            query: (string)($result['query'] ?? ''),
            optimizedQuery: (string)($result['optimizedQuery'] ?? ''),
            documents: is_array($result['documents'] ?? null) ? $result['documents'] : [],
            rawData: is_array($result['rawData'] ?? null) ? $result['rawData'] : [],
            createdAt: (int)($result['createdAt'] ?? 0),
        );
    }


    /**
     * Returns retrieved documents from the last retrieval result.
     *
     * @param string $chatIdentifier Conversation identifier.
     * @return array<int,\Madj2k\AiAssistant\Assistant\DTO\RetrievalDocument>
     */
    public function getLastRetrievalDocuments(string $chatIdentifier): array
    {
        /** @var \Madj2k\AiAssistant\Assistant\DTO\LastRetrievalResult|null $retrievalResult */
        $retrievalResult = $this->getLastRetrievalResult($chatIdentifier);
        if (!$retrievalResult instanceof LastRetrievalResult) {
            return [];
        }

        return $retrievalResult->documents;
    }


    /**
     * Normalizes the identifier key.
     *
     * @param string $chatIdentifier Raw identifier.
     * @return string
     */
    private function normalizeIdentifier(string $chatIdentifier): string
    {
        $chatIdentifier = trim($chatIdentifier);
        return $chatIdentifier !== '' ? $chatIdentifier : 'default';
    }


    /**
     * Trims old messages in the history.
     *
     * @param array<string,mixed> $conversations Stored conversations.
     * @param string $chatIdentifier Conversation identifier.
     * @return void
     * @throws \Madj2k\AiAssistant\Exception\AppException
     */
    private function trimMessages(array &$conversations, string $chatIdentifier): void
    {
        $maxMessages = max(2, (int)Config::get('assistant.memory.maxPrompts', 20));
        $messages = $conversations[$chatIdentifier]['messages'] ?? [];
        if (is_array($messages) && count($messages) > $maxMessages) {
            $conversations[$chatIdentifier]['messages'] = array_slice($messages, -$maxMessages);
        }
    }


    /**
     * Loads stored conversations and ensures the requested identifier exists.
     *
     * @param string $chatIdentifier Conversation identifier.
     * @return array<string,mixed>
     */
    private function loadConversations(string $chatIdentifier): array
    {
        $chatIdentifier = $this->normalizeIdentifier($chatIdentifier);
        $frontendUser = $this->getFrontendUser();

        if ($frontendUser instanceof FrontendUserAuthentication) {
            $storedData = $frontendUser->getKey('ses', self::SESSION_KEY);
            $conversations = is_array($storedData) ? $storedData : [];
        } else {
            $this->ensurePhpSessionStarted();
            $conversations = is_array($_SESSION[self::SESSION_KEY] ?? null)
                ? $_SESSION[self::SESSION_KEY]
                : [];
        }

        if (!isset($conversations[$chatIdentifier]) || !is_array($conversations[$chatIdentifier])) {
            $conversations[$chatIdentifier] = [
                'start_timestamp' => 0,
                'messages' => [],
                'lastRetrievalResult' => [],
            ];
        }

        if (!isset($conversations[$chatIdentifier]['messages']) || !is_array($conversations[$chatIdentifier]['messages'])) {
            $conversations[$chatIdentifier]['messages'] = [];
        }

        if (!isset($conversations[$chatIdentifier]['lastRetrievalResult']) || !is_array($conversations[$chatIdentifier]['lastRetrievalResult'])) {
            $conversations[$chatIdentifier]['lastRetrievalResult'] = [];
        }

        return $conversations;
    }


    /**
     * Persists all stored conversations.
     *
     * @param array<string,mixed> $conversations Stored conversations.
     * @return void
     */
    private function saveConversations(array $conversations): void
    {
        $frontendUser = $this->getFrontendUser();
        if ($frontendUser instanceof FrontendUserAuthentication) {
            $frontendUser->setKey('ses', self::SESSION_KEY, $conversations);
            $frontendUser->storeSessionData();
            return;
        }

        $this->ensurePhpSessionStarted();
        $_SESSION[self::SESSION_KEY] = $conversations;
    }


    /**
     * Returns the current TYPO3 frontend user session if available.
     *
     * @return \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication|null
     */
    private function getFrontendUser(): ?FrontendUserAuthentication
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (!$request instanceof ServerRequestInterface) {
            return null;
        }

        $frontendUser = $request->getAttribute('frontend.user');
        if (!$frontendUser instanceof FrontendUserAuthentication) {
            return null;
        }

        return $frontendUser;
    }


    /**
     * Starts PHP session if TYPO3 frontend session storage is unavailable.
     *
     * @return void
     */
    private function ensurePhpSessionStarted(): void
    {
        if (PHP_SESSION_ACTIVE !== session_status()) {
            session_start();
        }
    }
}
