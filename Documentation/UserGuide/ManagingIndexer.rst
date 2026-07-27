..  _managing-indexer:

Managing indexers
=================

Indexers synchronize source content into vector stores. They are usually run via
CLI commands or scheduled tasks.

General indexer configuration
-----------------------------

An indexer configuration typically defines:

* source type or indexer identifier;
* AI connection for embeddings;
* vector store connection;
* target collection;
* chunk size, overlap and limits;
* source-specific settings such as root pages, import path or connector fields;
* additional metadata.

Chunking
--------

Chunking splits long source documents into smaller pieces. Smaller chunks can
improve precision. Larger chunks can preserve context. Use overlap when answers
need information that may cross chunk boundaries.

Source state
------------

The source state tracks indexed sources and helps the indexer decide whether a
source has changed. The extension differentiates source identity and content
changes so a re-index can update existing vectors rather than create duplicates.

JSON and JSONL sources
----------------------

JSON and JSONL indexing can separate searchable text from structured metadata.
This is useful when imported records contain both answer content and additional
fields such as authors, departments, categories, URLs or contact details.

``json_text_fields``
    Defines which JSON values become indexed text.

``json_metadata_fields``
    Defines which JSON values are written to additional metadata in the vector
    payload.

Both options are configured as comma-separated lists using dot notation.
Each list entry is prefixed with the configuration option it belongs to.
Entries can also be written on separate lines in the additional metadata field.

Examples:

..  code-block:: text

    json_text_fields.title,json_text_fields.description,json_text_fields.content.body
    json_metadata_fields.author.name,json_metadata_fields.department,json_metadata_fields.keywords.*

Dot notation supports nested structures, numeric array indices and wildcards:

..  code-block:: text

    json_metadata_fields.author.name
    json_metadata_fields.items.0.title
    json_metadata_fields.keywords.*

Metadata fields can also be mapped to custom payload keys:

..  code-block:: text

    json_metadata_fields.author.name=contact_name,json_metadata_fields.author.email=contact_email,json_metadata_fields.meta.department=department

Example for a JSON record with ``type``, ``content.text``, ``keywords``,
``meta.category`` and ``id`` fields:

..  code-block:: text

    json_text_fields.type
    json_text_fields.content.text
    json_text_fields.keywords.*
    json_metadata_fields.type
    json_metadata_fields.meta.category=category
    json_metadata_fields.id=external_id

If no ``json_text_fields`` are configured, the JSON document is flattened and
indexed as text. For production setups, explicit text fields are recommended
because they usually produce more precise retrieval results.

Operational advice
------------------

* Use a separate test collection before changing production chunk settings.
* Rebuild a collection when source identity rules or chunking strategy changes.
* Keep pipeline logs enabled during setup and reduce logging later.
* Use deterministic source identifiers for custom sources.
* For JSON and JSONL sources, configure text fields and metadata fields
  explicitly whenever possible.
