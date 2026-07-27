<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension ai_assistant.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiAssistant\Connection\Registry;

use Madj2k\AiAssistant\Connection\Ai\AiConnectorInterface;

/**
 * Class AiConnectorRegistry
 *
 * Provides access to registered AI connectors.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class AiConnectorRegistry
{
    /**
     * Registered connectors by identifier.
     *
     * @var array<string, \Madj2k\AiAssistant\Connection\Ai\AiConnectorInterface>
     */
    protected array $connectors = [];


    /**
     * Constructor.
     *
     * @param iterable<\Madj2k\AiAssistant\Connection\Ai\AiConnectorInterface> $connectors Registered connectors.
     */
    public function __construct(iterable $connectors)
    {
        foreach ($connectors as $connector) {
            $this->connectors[$connector->getIdentifier()] = $connector;
        }
    }


    /**
     * Returns a connector by identifier.
     *
     * @param string $identifier Connector identifier.
     * @return \Madj2k\AiAssistant\Connection\Ai\AiConnectorInterface Connector.
     */
    public function get(string $identifier): AiConnectorInterface
    {
        if (!isset($this->connectors[$identifier])) {
            throw new \InvalidArgumentException(
                sprintf('No AI connector registered for identifier "%s".', $identifier),
                1780001001
            );
        }

        return $this->connectors[$identifier];
    }


    /**
     * Checks whether a connector is registered.
     *
     * @param string $identifier Connector identifier.
     * @return bool True if connector exists.
     */
    public function has(string $identifier): bool
    {
        return isset($this->connectors[$identifier]);
    }


    /**
     * Returns all registered connectors.
     *
     * @return array<string, \Madj2k\AiAssistant\Connection\Ai\AiConnectorInterface> Registered connectors.
     */
    public function all(): array
    {
        return $this->connectors;
    }
}
