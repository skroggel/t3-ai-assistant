..  _retrieval-configuration:

Retrieval configuration
=======================

Retrieval configuration controls what the assistant can see before answering.

Recommended defaults
--------------------

..  code-block:: text

    top_k: 8
    score_threshold: 0
    max_context_chunks: 6
    max_context_characters: 9000
    prompt_metadata_fields: title,url,source_type,source_identifier
    frontend_source_fields: title,url,source_type,source_identifier

Tune these values with real questions and pipeline traces. A higher ``top_k`` can
improve recall but may add irrelevant context. A lower context limit can improve
precision but may hide necessary details.

