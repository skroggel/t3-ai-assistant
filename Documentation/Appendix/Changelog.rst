..  _changelog:

Changelog
=========

This documentation is intended to be maintained together with the extension.
Use this page for documentation-level changes or link to the project changelog.

Current release: :ref:`14.3.1 <changelog-14-3-1>`.

..  _changelog-14-3-1:

14.3.1 (2026-08-18)
-------------------

Features
~~~~~~~~

Retriever steps can now contribute multiple, separately named retrieval groups
to one pipeline run. The retriever-step title names its retrieval group and is
visible in the LLM prompt, so instructions can distinguish between sources.
Retriever-step titles must therefore be unique within a pipeline. Every
retriever appends its named group, preventing later retrievers from implicitly
discarding previous results. Chunk and character limits apply to each retrieval
group independently.

Vector store connections can define a default collection and further allowed
collections. Assistant profiles provide a default vector store connection.
Every retriever step can override that connection and select one of the
effective connection's collections explicitly. Without overrides, the profile
connection and its default collection are used.

The backend Diagnostics area can validate complete assistant profiles. It checks
pipeline configuration, the AI connection and every effective vector store and
collection used by the default vector retriever. Remote collection checks are
read-only and do not execute the chat pipeline.

Breaking changes
~~~~~~~~~~~~~~~~

The collection field on assistant profiles has been removed. Move a profile's
existing value to the vector store connection's ``default_collection`` or to
the relevant retriever step's ``retrieval_collection`` before updating the
database schema.

The retrieval state in ``Madj2k\\AiCore`` is group-based only. The former flat
result, raw-result and processor-identifier accessors have been removed. Stored
retrieval session snapshots from earlier development versions are not reused.
Normal conversation messages are unaffected.

``PipelineStepConfigurationInterface`` now includes vector store connection and
collection accessors. The existing step title is the prompt-visible retrieval
name. Retrieval processors always append their named group.
``VectorStoreConnectionConfigurationInterface`` now includes
``getCollectionList()``. Internal implementations have been updated to these
contracts.

Database migration
~~~~~~~~~~~~~~~~~~

The pipeline-step fields ``retrieval_vector_store_connection`` and
``retrieval_collection`` and the vector-store-connection field ``collections``
have been added.

The former ``top_k`` field was renamed to ``max_retrieval_results``. Existing
values must be copied before TYPO3 removes the old field:

..  code-block:: sql

    UPDATE tx_aiassistant_assistant_pipeline_step
    SET max_retrieval_results = top_k;

The separate ``frontend_source_fields`` setting was intentionally consolidated
into ``prompt_metadata_fields``. The latter now controls both prompt metadata
and frontend source output, so no value migration is required for
``frontend_source_fields``.

Existing retriever steps must receive meaningful, unique titles as part of the
internal pipeline configuration.

..  _changelog-14-3-0:

14.3.0 (2026-08-17)
-------------------

Breaking changes
~~~~~~~~~~~~~~~~

The temporary ``Madj2k\\AiAssistant`` aliases for framework-independent
assistant, connection and indexing classes have been removed. Extensions must
use the corresponding ``Madj2k\\AiCore`` classes and interfaces directly.
TYPO3-specific models, repositories, controllers and integration services
remain in the ``Madj2k\\AiAssistant`` namespace.
