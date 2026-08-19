..  _retrieval-configuration:

Retrieval configuration
=======================

Retrieval configuration controls what the assistant can see before answering.

Recommended defaults
--------------------

..  code-block:: text

    max_retrieval_results: 8
    score_threshold: 0
    max_context_chunks: 6
    max_context_characters: 9000
    prompt_metadata_fields: title,url,source_type,source_identifier

Named multiple retrievals
-------------------------

Retriever steps have a unique title. The title names the retrieval in prompts,
logs and stored context. Every retriever appends its named group to the current
retrieval context, so all sources remain available to subsequent pipeline
steps. Documents are intentionally not deduplicated. The prompt keeps each
retrieval under its step title so the context optimizer can compare sources.

The chunk and character limits configured on a retriever apply only to that
retrieval. The context limit on the consuming LLM step remains the final global
safety limit.

Assistant profiles provide a default vector store connection. Each retriever
can override that connection. Vector store connections define a default and
additional allowed collections; each retriever can also override the collection.
The effective connection is resolved first, followed by its effective collection.

Tune these values with real questions and pipeline traces. A higher
``max_retrieval_results`` can improve recall but may add irrelevant context. A
lower context limit can improve precision but may hide necessary details.
