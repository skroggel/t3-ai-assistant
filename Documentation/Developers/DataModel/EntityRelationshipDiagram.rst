..  _entity-relationship-diagram:

Entity relationship diagram
===========================

The following diagram is intentionally abstract. It shows the relationships that
are relevant for understanding the extension, not every database field.

..  code-block:: mermaid

    erDiagram
        AI_CONNECTION ||--o{ ASSISTANT_PROFILE : used_by
        VECTOR_STORE_CONNECTION ||--o{ ASSISTANT_PROFILE : used_by
        ASSISTANT_PROFILE ||--o{ PIPELINE_STEP : contains
        AI_CONNECTION ||--o{ INDEXER_CONFIG : creates_embeddings
        VECTOR_STORE_CONNECTION ||--o{ INDEXER_CONFIG : stores_vectors
        INDEXER_CONFIG ||--o{ INDEXER_RUN : creates
        INDEXER_CONFIG ||--o{ INDEXER_SOURCE : tracks
        ASSISTANT_PROFILE ||--o{ PIPELINE_TRACE : writes
        PIPELINE_STEP ||--o{ PIPELINE_TRACE : writes

Conceptual tables
-----------------

Assistant configuration
    Assistant profiles and pipeline steps.

Connection configuration
    AI and vector store connections.

Indexing configuration
    Indexer configurations, external connector settings, indexer runs and source
    states.

Runtime diagnostics
    Pipeline traces and backend diagnostics.

