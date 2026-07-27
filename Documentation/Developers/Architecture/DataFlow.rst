..  _data-flow:

Data flow
=========

Request lifecycle
-----------------

..  code-block:: mermaid

    sequenceDiagram
        participant User
        participant Controller
        participant Orchestrator
        participant Pipeline
        participant AI
        participant VectorStore

        User->>Controller: message
        Controller->>Orchestrator: AssistantRequest
        Orchestrator->>Pipeline: Context
        Pipeline->>AI: query optimization / answer generation
        Pipeline->>VectorStore: retrieval
        VectorStore-->>Pipeline: documents
        Pipeline-->>Orchestrator: AssistantResponse
        Orchestrator-->>Controller: response
        Controller-->>User: answer

Indexing lifecycle
------------------

..  code-block:: mermaid

    sequenceDiagram
        participant Command
        participant Indexer
        participant Source
        participant Chunker
        participant AI
        participant VectorStore
        participant State

        Command->>Indexer: IndexingRequest
        Indexer->>Source: fetch/read source
        Source-->>Indexer: document data
        Indexer->>State: compare source/content hash
        Indexer->>Chunker: split text
        Chunker-->>Indexer: chunks
        Indexer->>AI: embeddings
        AI-->>Indexer: vectors
        Indexer->>VectorStore: write/delete vectors
        Indexer->>State: update state

