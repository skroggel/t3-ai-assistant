..  _retrieval-and-context:

Retrieval and context
=====================

Retrieval is the step where the assistant searches indexed content. The quality
of answers depends strongly on retrieval configuration and metadata.

Key retrieval settings
----------------------

max_retrieval_results
    Maximum number of results contributed by this retriever.

score_threshold
    Minimum similarity score. A threshold of ``0`` usually means no explicit
    threshold.

max_context_chunks
    Maximum number of chunks passed into the prompt context. A value of ``0``
    can be used by the implementation to mean no explicit chunk limit.

max_context_characters
    Maximum number of characters passed into the prompt context. A value of
    ``0`` can be used by the implementation to mean no explicit character limit.

prompt_metadata_fields
    Comma-separated list of metadata fields included in the prompt context and
    exposed as answer sources in the frontend response.

Recommended metadata fields
---------------------------

For most assistants, start with:

..  code-block:: text

    title,url,source_type,source_identifier

For debugging and source attribution you can add project-specific fields such as
``page_uid``, ``record_identifier``, ``collection`` or ``chunk_index`` if they are
available in the payload.
