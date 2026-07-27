..  _creating-indexer:

Creating an indexer
===================

Create a service implementing
``Madj2k\AiAssistant\Indexing\Indexer\IndexerInterface``. For most use cases,
extending the abstract indexer is recommended.

Indexer responsibilities
------------------------

* resolve source configuration;
* read source records;
* create ``IndexableDocument`` instances;
* provide stable source identifiers;
* pass documents to shared indexing logic;
* update result counters;
* handle cursor or batch state where needed.

Source identifiers
------------------

A source identifier must be stable. Good examples:

* ``pages:123`` for a TYPO3 page;
* FAL combined identifier for a file;
* external system id for a product;
* JSON record id for a structured record.


Indexer type and enum values
----------------------------

Indexer configuration records contain both a high-level ``type`` and an optional
``indexer_identifier``. The ``type`` field should use values from
``Madj2k\AiAssistant\Indexing\Enum\IndexerType``.

Use the enum as the source of truth when creating TCA, seed data, SQL imports or
custom indexer configurations. This avoids backend records with type values that
cannot be rendered or resolved by the TYPO3 backend.

Typical indexer types include:

* ``page`` for TYPO3 page and content element indexing;
* ``file`` for FAL/file indexing;
* ``json`` or ``jsonl`` for structured JSON/JSONL indexing;
* custom enum values for project-specific or premium source types, when the
  enum and TCA are extended consistently.

A custom indexer should expose a stable identifier and document which enum type
it supports.

..  code-block:: php

    use Madj2k\AiAssistant\Indexing\Domain\Model\IndexerConfig;
    use Madj2k\AiAssistant\Indexing\Enum\IndexerType;

    public function getIdentifier(): string
    {
        return 'vendor.indexer.custom_json';
    }

    public function supports(IndexerConfig $configuration): bool
    {
        return $configuration->getType() === IndexerType::Json->value;
    }

When a custom source needs a new type, extend the enum and TCA together. Do not
store ad-hoc type strings in database records without adding the matching enum
and TCA type definition.
