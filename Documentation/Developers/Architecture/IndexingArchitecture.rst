..  _indexing-architecture:

Indexing architecture
=====================

Indexing converts external or TYPO3-managed content into vector documents.

Core classes
------------

``Madj2k\AiAssistant\Indexing\Indexer\IndexerInterface``
    Common interface for indexers.

``Madj2k\AiAssistant\Indexing\Indexer\AbstractIndexer``
    Shared indexing logic such as configuration resolution, chunk settings,
    embeddings and vector writes.

``Madj2k\AiAssistant\Indexing\DTO\IndexableDocument``
    Text plus metadata to be indexed.

``Madj2k\AiAssistant\Indexing\DTO\IndexableMetadata``
    Structured metadata for source identity and payload.

``Madj2k\AiAssistant\Indexing\Service\TextChunkerService``
    Splits text into chunks.

``Madj2k\AiAssistant\Indexing\Service\SourceStateService``
    Creates source hashes, content hashes and source state decisions.

Indexing stages
---------------

#. Resolve indexer configuration.
#. Read source content.
#. Build ``IndexableDocument`` with metadata.
#. Detect source changes.
#. Split text into chunks.
#. Request embeddings.
#. Write vector documents.
#. Update source state.

Adapters and connectors
-----------------------

File adapters extract text from files. Source connectors fetch external data.
Indexers coordinate those services and decide how data is transformed into
indexable documents.

JSON and JSONL handling
-----------------------

JSON and JSONL indexers can separate searchable text from metadata. The
configuration distinguishes ``json_text_fields`` and ``json_metadata_fields``.
Both use comma-separated dot notation and support nested paths, numeric array
indices and wildcards.

Metadata mapping can write values to custom payload keys using the syntax
``payload_key=json.path``. This allows a source field such as ``author.name`` to
be stored as ``contact_name`` in the vector payload.

See :ref:`json-jsonl-indexing-architecture` for details.
