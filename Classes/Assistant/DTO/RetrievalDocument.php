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



namespace Madj2k\AiAssistant\Assistant\DTO;

use Madj2k\AiAssistant\DTO\DocumentMetadata;

/**
 * Class RetrievalDocument
 *
 * Represents one normalized document chunk returned by a retrieval connector.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel <developer@steffenkroggel.de>
 * @package Madj2k\\AiAssistant
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
final readonly class RetrievalDocument
{
    /**
     * Result identifier.
     *
     * @var string
     */
    public string $id;


    /**
     * Retrieval score.
     *
     * @var float
     */
    public float $score;


    /**
     * Main text sent to the answer context.
     *
     * @var string
     */
    public string $text;


    /**
     * Structured document metadata.
     *
     * @var \Madj2k\AiAssistant\DTO\DocumentMetadata
     */
    public DocumentMetadata $documentMetadata;


    /**
     * Constructor.
     *
     * @param string $id Result identifier.
     * @param float $score Retrieval score.
     * @param string $text Main text sent to the answer context.
     * @param \Madj2k\AiAssistant\DTO\DocumentMetadata|array $documentMetadata
     */
    public function __construct(
        string $id,
        float $score,
        string $text,
        DocumentMetadata|array $documentMetadata,
    ) {
        $this->id = $id;
        $this->score = $score;
        $this->text = $text;
        $this->documentMetadata = is_array($documentMetadata)
            ? DocumentMetadata::fromArray($documentMetadata)
            : $documentMetadata;
    }


    /**
     * Returns the structured document metadata.
     *
     * @return \Madj2k\AiAssistant\DTO\DocumentMetadata Metadata.
     */
    public function getDocumentMetadata(): DocumentMetadata
    {
        return $this->documentMetadata;
    }


    /**
     * Returns a serializable representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'score' => $this->score,
            'text' => $this->text,
            'metadata' => $this->getDocumentMetadata()->toArray(),
        ];
    }



    /**
     * Returns a compact representation
     *
     * @return array<string,mixed>
     */
    public function toTraceArray(): array
    {
        return [
            'id' => $this->id,
            'score' => $this->score,
            'text_excerpt' => mb_substr($this->text, 0, 300, 'UTF-8'),
            'metadata' => $this->getDocumentMetadata()->toArray(),
        ];
    }


    /**
     * Returns one normalized metadata value.
     *
     * @param string $field Metadata field name.
     * @return mixed Metadata value.
     */
    public function getMetadataValue(string $field): mixed
    {
        return match ($field) {
            'id' => $this->id,
            'score' => $this->score,
            'text' => $this->text,
            default => $this->documentMetadata->getValue($field) ?? null,
        };
    }


    /**
     * Returns selected metadata fields for frontend source output.
     *
     * @param array<int,string> $fields Source field names.
     * @return array<string,mixed> Source data.
     */
    public function toSourceArray(array $fields): array
    {
        $source = [];

        foreach ($fields as $field) {
            $value = $this->getMetadataValue($field);
            if ($value === null || $value === '') {
                continue;
            }

            $source[$field] = $value;
        }

        return $source;
    }
}
