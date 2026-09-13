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

namespace Madj2k\AiAssistant\Assistant\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;

/**
 * Class SseResponseFactory
 *
 * Creates Server-Sent Events responses for the frontend chat.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>, Maximilian Fäßler <maximilian@faesslerweb.de>
 * @package Madj2k\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
final class SseResponseFactory
{
    public const ERROR_MESSAGE = 'The answer could not be loaded.';


    /**
     * Creates a temporary SSE stream for non-streaming fallback usage.
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    public function createBody(): StreamInterface
    {
        return new Stream('php://temp', 'rw');
    }


    /**
     * Writes a buffering prelude to a PSR-7 body.
     *
     * @param \Psr\Http\Message\StreamInterface $body SSE body.
     * @return void
     */
    public function writePrelude(StreamInterface $body): void
    {
        $body->write(':' . str_repeat(' ', 2048) . "\n\n");
    }


    /**
     * Writes a data event to a PSR-7 body.
     *
     * @param \Psr\Http\Message\StreamInterface $body SSE body.
     * @param string $data Payload.
     * @return void
     */
    public function writeData(StreamInterface $body, string $data): void
    {
        $lines = preg_split('/\R/u', $data) ?: [''];
        foreach ($lines as $line) {
            $body->write('data: ' . $line . "\n");
        }
        $body->write("\n");
    }


    /**
     * Writes a named event to a PSR-7 body.
     *
     * @param \Psr\Http\Message\StreamInterface $body SSE body.
     * @param string $event Event name.
     * @param string $data Event payload.
     * @return void
     */
    public function writeEvent(StreamInterface $body, string $event, string $data = ''): void
    {
        $body->write('event: ' . $event . "\n");
        if ($data !== '') {
            $this->writeData($body, $data);
            return;
        }

        $body->write("\n");
    }


    /**
     * Sends a buffering prelude directly to the client.
     *
     * @return void
     */
    public function sendPrelude(): void
    {
        echo ':' . str_repeat(' ', 2048) . "\n\n";
        $this->flush();
    }


    /**
     * Sends a data event directly to the client.
     *
     * @param string $data Payload.
     * @return void
     */
    public function sendData(string $data): void
    {
        $lines = preg_split('/\R/u', $data) ?: [''];
        foreach ($lines as $line) {
            echo 'data: ' . $line . "\n";
        }
        echo "\n";
        $this->flush();
    }


    /**
     * Sends a named event directly to the client.
     *
     * @param string $event Event name.
     * @param string $data Event payload.
     * @return void
     */
    public function sendEvent(string $event, string $data = ''): void
    {
        echo 'event: ' . $event . "\n";
        if ($data !== '') {
            $lines = preg_split('/\R/u', $data) ?: [''];
            foreach ($lines as $line) {
                echo 'data: ' . $line . "\n";
            }
        }
        echo "\n";
        $this->flush();
    }


    /**
     * Creates a streaming HTTP response.
     *
     * @param callable $producer SSE producer callback.
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function createStreamingResponse(callable $producer): ResponseInterface
    {
        return $this->createResponse(new SseStream($producer));
    }


    /**
     * Creates the final HTTP response.
     *
     * @param \Psr\Http\Message\StreamInterface $body SSE body.
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function createResponse(StreamInterface $body): ResponseInterface
    {
        $response = (new Response())
            ->withHeader('Content-Type', 'text/event-stream; charset=utf-8')
            ->withHeader('Cache-Control', 'no-cache, no-transform')
            ->withHeader('X-Accel-Buffering', 'no')
            ->withHeader('Connection', 'keep-alive');

        if ($body->isSeekable()) {
            $body->rewind();
        }

        return $response->withBody($body);
    }


    /**
     * Flushes all active PHP output buffers.
     *
     * @return void
     */
    private function flush(): void
    {
        while (ob_get_level() > 0) {
            $status = ob_get_status();
            if (($status['del'] ?? false) || ($status['flags'] ?? 0) & PHP_OUTPUT_HANDLER_REMOVABLE) {
                @ob_end_flush();
                continue;
            }

            @ob_flush();
            break;
        }

        flush();
    }
}
