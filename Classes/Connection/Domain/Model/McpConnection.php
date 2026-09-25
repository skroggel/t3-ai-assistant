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

namespace Madj2k\AiAssistant\Connection\Domain\Model;

use Madj2k\AiMcp\Configuration\McpConnectionConfigurationInterface;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class McpConnection
 *
 * Stores one reusable MCP server connection.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
class McpConnection extends AbstractEntity implements McpConnectionConfigurationInterface
{
    /** @var string Connection title. */
    protected string $title = '';

    /** @var string Stable connection identifier. */
    protected string $identifier = '';

    /** @var string MCP transport identifier. */
    protected string $transport = 'streamable_http';

    /** @var string MCP endpoint. */
    protected string $endpoint = '';

    /** @var string Optional bearer token. */
    protected string $bearerToken = '';
    protected string $authentication = 'none';
    protected string $oauthTokenEndpoint = '';
    protected string $oauthClientId = '';
    protected string $oauthClientSecret = '';
    protected string $oauthScope = '';

    /** @var int HTTP timeout in seconds. */
    protected int $timeout = 10;

    /** @var string Comma- or newline-separated allowed tools. */
    protected string $allowedTools = '';

    /** @var int Maximum tool rounds per assistant request. */
    protected int $maxRounds = 10;

    /** @return string Connection title. */
    public function getTitle(): string
    {
        return $this->title;
    }

    /** @param string $title Connection title. */
    public function setTitle(string $title): void
    {
        $this->title = trim($title);
    }

    public function getIdentifier(): string
    {
        return trim($this->identifier) !== '' ? trim($this->identifier) : (string)$this->getUid();
    }

    /** @param string $identifier Stable connection identifier. */
    public function setIdentifier(string $identifier): void
    {
        $this->identifier = trim($identifier);
    }

    public function getTransport(): string { return $this->transport; }

    /** @param string $transport MCP transport identifier. */
    public function setTransport(string $transport): void
    {
        $this->transport = trim($transport) ?: 'streamable_http';
    }

    public function getEndpoint(): string { return trim($this->endpoint); }

    /** @param string $endpoint MCP endpoint. */
    public function setEndpoint(string $endpoint): void
    {
        $this->endpoint = trim($endpoint);
    }

    public function getBearerToken(): string { return $this->bearerToken; }

    /** @param string $bearerToken Bearer token. */
    public function setBearerToken(string $bearerToken): void
    {
        $this->bearerToken = trim($bearerToken);
    }

    public function getAuthentication(): string { return $this->authentication; }

    public function setAuthentication(string $authentication): void { $this->authentication = trim($authentication) ?: 'none'; }

    public function getOauthTokenEndpoint(): string { return trim($this->oauthTokenEndpoint); }

    public function setOauthTokenEndpoint(string $endpoint): void { $this->oauthTokenEndpoint = trim($endpoint); }

    public function getOauthClientId(): string { return trim($this->oauthClientId); }

    public function setOauthClientId(string $clientId): void { $this->oauthClientId = trim($clientId); }

    public function getOauthClientSecret(): string { return $this->oauthClientSecret; }

    public function setOauthClientSecret(string $clientSecret): void { $this->oauthClientSecret = trim($clientSecret); }

    public function getOauthScope(): string { return trim($this->oauthScope); }

    public function setOauthScope(string $scope): void { $this->oauthScope = trim($scope); }

    public function getTimeout(): int { return max(1, $this->timeout); }

    /** @param int $timeout HTTP timeout in seconds. */
    public function setTimeout(int $timeout): void
    {
        $this->timeout = max(1, $timeout);
    }

    public function getAllowedTools(): array
    {
        return array_values(array_filter(
            array_map('trim', preg_split('/[,\R]+/', $this->allowedTools) ?: []),
            static fn (string $tool): bool => $tool !== '',
        ));
    }

    /** @param string $allowedTools Allowed tool names. */
    public function setAllowedTools(string $allowedTools): void
    {
        $this->allowedTools = trim($allowedTools);
    }

    /** @return int Maximum tool rounds per request. */
    public function getMaxRounds(): int
    {
        return max(1, $this->maxRounds);
    }

    /** @param int $maxRounds Maximum tool rounds per request. */
    public function setMaxRounds(int $maxRounds): void
    {
        $this->maxRounds = max(1, $maxRounds);
    }

    public function getHeaders(): array
    {
        return $this->getAuthentication() === 'bearer' && $this->getBearerToken() !== ''
            ? ['Authorization' => 'Bearer ' . $this->getBearerToken()]
            : [];
    }
}
