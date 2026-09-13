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

namespace Madj2k\AiAssistant\Assistant\Http;

use Psr\Http\Message\StreamInterface;
use TYPO3\CMS\Core\Http\SelfEmittableStreamInterface;
use TYPO3\CMS\Core\Http\Stream;

/**
 * Class SseStream
 *
 * Self-emitting stream that executes the SSE producer during response emission.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final class SseStream implements StreamInterface, SelfEmittableStreamInterface
{
    /**
     * Producer callback.
     *
     * @var callable
     */
    private mixed $producer;


    /**
     * Whether the producer has already been executed.
     *
     * @var bool
     */
    private bool $emitted = false;


    /**
     * Buffered fallback stream for non self-emitting emitters.
     *
     * @var \Psr\Http\Message\StreamInterface|null
     */
    private ?StreamInterface $fallbackStream = null;


    /**
     * Constructor.
     *
     * @param callable $producer Producer callback.
     */
    public function __construct(callable $producer)
    {
        $this->producer = $producer;
    }


    /**
     * Emits the stream directly to the client.
     *
     * @return void
     */
    public function emit(): void
    {
        if ($this->emitted) {
            return;
        }

        $this->emitted = true;
        ($this->producer)();
    }


    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        try {
            return $this->getContents();
        } catch (\Throwable) {
            return '';
        }
    }


    /**
     * @inheritDoc
     */
    public function close(): void
    {
        $this->fallbackStream?->close();
    }


    /**
     * @inheritDoc
     */
    public function detach()
    {
        return $this->fallbackStream?->detach();
    }


    /**
     * @inheritDoc
     */
    public function getSize(): ?int
    {
        return null;
    }


    /**
     * @inheritDoc
     */
    public function tell(): int
    {
        return $this->getFallbackStream()->tell();
    }


    /**
     * @inheritDoc
     */
    public function eof(): bool
    {
        if ($this->emitted && $this->fallbackStream === null) {
            return true;
        }

        return $this->getFallbackStream()->eof();
    }


    /**
     * @inheritDoc
     */
    public function isSeekable(): bool
    {
        return false;
    }


    /**
     * @inheritDoc
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        if ((int)$offset !== 0 || (int)$whence !== SEEK_SET) {
            throw new \RuntimeException('SSE stream is not seekable.', 1780670011);
        }
    }


    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        // TYPO3 or middleware emitters may call rewind() defensively.
        // The real SSE path is self-emitting and cannot be rewound.
    }


    /**
     * @inheritDoc
     */
    public function isWritable(): bool
    {
        return false;
    }


    /**
     * @inheritDoc
     */
    public function write($string): int
    {
        throw new \RuntimeException('SSE stream is not writable.', 1780670013);
    }


    /**
     * @inheritDoc
     */
    public function isReadable(): bool
    {
        return true;
    }


    /**
     * @inheritDoc
     */
    public function read($length): string
    {
        return $this->getFallbackStream()->read($length);
    }


    /**
     * @inheritDoc
     */
    public function getContents(): string
    {
        return $this->getFallbackStream()->getContents();
    }


    /**
     * @inheritDoc
     */
    public function getMetadata($key = null): mixed
    {
        return null;
    }


    /**
     * Creates the buffered fallback stream lazily.
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    private function getFallbackStream(): StreamInterface
    {
        if ($this->fallbackStream instanceof StreamInterface) {
            return $this->fallbackStream;
        }

        $body = new Stream('php://temp', 'rw');

        if (!$this->emitted) {
            $this->emitted = true;
            ob_start();
            try {
                ($this->producer)();
                /** @var string|false $contents */
                $contents = ob_get_clean();
            } catch (\Throwable) {
                /** @var string|false $contents */
                $contents = ob_get_clean();
                if (is_string($contents) && $contents !== '') {
                    $body->write($contents);
                }
                $body->write('event: error' . "\n");
                $body->write('data: ' . SseResponseFactory::ERROR_MESSAGE . "\n\n");
                $body->write('event: done' . "\n");
                $body->write('data: end' . "\n\n");
                $body->rewind();
                $this->fallbackStream = $body;

                return $this->fallbackStream;
            }

            if (is_string($contents) && $contents !== '') {
                $body->write($contents);
            }
        }

        $body->rewind();
        $this->fallbackStream = $body;

        return $this->fallbackStream;
    }
}
