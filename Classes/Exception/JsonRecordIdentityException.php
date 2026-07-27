<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 extension ai_assistant.
 *
 * For the full copyright and license information, please read the LICENSE file that was distributed with this source code.
 */

namespace Madj2k\AiAssistant\Exception;

/**
 * Exception for JSON records that cannot be mapped to unique source identities.
 */
final class JsonRecordIdentityException extends IndexingException
{
    /**
     * @param string $message Exception message.
     * @param array<string, mixed> $details Structured diagnostic details.
     * @param int $code Exception code.
     */
    public function __construct(
        string $message,
        private readonly array $details = [],
        int $code = 1782295401
    ) {
        parent::__construct($message, $code);
    }


    /**
     * Returns structured diagnostic details.
     *
     * @return array<string, mixed> Details.
     */
    public function getDetails(): array
    {
        return $this->details;
    }
}
