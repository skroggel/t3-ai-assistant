..  _json-jsonl-indexing-architecture:

JSON and JSONL indexing
=======================

AI Assistant supports JSON and JSONL indexing. The JSON indexer can distinguish
between values that become searchable text and values that are stored as
structured metadata in the vector payload.

This makes JSON sources useful for both full-text retrieval and metadata-aware
answers. Typical examples are product feeds, knowledge base exports, API dumps,
FAQ data or custom application records.

Configuration fields
--------------------

``json_text_fields``
    Defines which JSON values are included in the indexed text.

``json_metadata_fields``
    Defines which JSON values are copied into the additional metadata payload.

Both options are configured as comma-separated lists using dot notation.
Each list entry is prefixed with the configuration option it belongs to.
The same entries can also be written on separate lines in the indexer metadata
field.

Examples:

..  code-block:: text

    json_text_fields.title,json_text_fields.description,json_text_fields.content.body
    json_metadata_fields.author.name,json_metadata_fields.department,json_metadata_fields.keywords.*

Dot notation
------------

Dot notation supports nested objects, numeric array indices and wildcards.

Examples:

..  code-block:: text

    json_metadata_fields.author.name
    json_metadata_fields.items.0.title
    json_metadata_fields.keywords.*

``json_metadata_fields.author.name``
    Reads the ``name`` property from the nested ``author`` object.

``json_metadata_fields.items.0.title``
    Reads the ``title`` property from the first item in the ``items`` array.

``json_metadata_fields.keywords.*``
    Reads all values from the ``keywords`` array.

Text fields
-----------

``json_text_fields`` defines which JSON values are concatenated into the
indexable text.

Example:

..  code-block:: text

    json_text_fields.title,json_text_fields.description,json_text_fields.content.body,json_text_fields.items.0.title

If no explicit ``json_text_fields`` are configured, the complete JSON document is
flattened recursively and indexed as text. This fallback is useful for quick
setup, but explicit text fields usually produce better retrieval quality.

Metadata fields
---------------

``json_metadata_fields`` defines which JSON values are stored as additional
metadata in the vector payload.

Simple example:

..  code-block:: text

    json_metadata_fields.author.name,json_metadata_fields.department,json_metadata_fields.keywords.*

Metadata mapping
----------------

Metadata values can optionally be mapped to custom payload keys.

Syntax:

..  code-block:: text

    json_metadata_fields.<json.path>=<payload_key>

Example:

..  code-block:: text

    json_metadata_fields.author.name=contact_name,json_metadata_fields.author.email=contact_email,json_metadata_fields.meta.department=department

The part after ``json_metadata_fields.`` is the JSON path in the source document.
The optional value after ``=`` becomes the metadata key in the payload. If no
explicit target key is configured, the source path is used as metadata key.

For example, this JSON-style configuration:

..  code-block:: json

    {
      "json_text_fields": ["type", "content.text", "keywords.*"],
      "json_metadata_fields": {
        "type": "type",
        "category": "meta.category",
        "external_id": "id"
      }
    }

is equivalent to this dot-notation configuration:

..  code-block:: text

    json_text_fields.type
    json_text_fields.content.text
    json_text_fields.keywords.*
    json_metadata_fields.type
    json_metadata_fields.meta.category=category
    json_metadata_fields.id=external_id

Complete example
----------------

JSON document:

..  code-block:: json

    {
      "title": "AI Assistant",
      "description": "Configurable RAG assistant for TYPO3.",
      "author": {
        "name": "Steffen Kroggel",
        "email": "developer@example.com"
      },
      "keywords": [
        "TYPO3",
        "AI",
        "RAG"
      ]
    }

Configuration:

..  code-block:: text

    json_text_fields.title,json_text_fields.description
    json_metadata_fields.author.name=contact_name,json_metadata_fields.author.email=contact_email,json_metadata_fields.keywords.*=keywords

Resulting indexed text:

..  code-block:: text

    AI Assistant
    Configurable RAG assistant for TYPO3.

Resulting metadata:

..  code-block:: json

    {
      "contact_name": "Steffen Kroggel",
      "contact_email": "developer@example.com",
      "keywords": [
        "TYPO3",
        "AI",
        "RAG"
      ]
    }

Source identity
---------------

JSON records should use a stable source identifier. The configured source
identifier must not depend on mutable text content. Content changes are detected
through the content hash; source identity is tracked separately through the
source hash.

Operational advice
------------------

* Use explicit ``json_text_fields`` for production configurations.
* Store values needed for source attribution in ``json_metadata_fields``.
* Use mapping when JSON paths are too technical for prompt or frontend output.
* Rebuild a collection when source identifier rules change.
