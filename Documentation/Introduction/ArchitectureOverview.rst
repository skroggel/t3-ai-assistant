..  _architecture-overview:

Architecture overview
=====================

AI Assistant is organized into domains. The following overview uses the target
namespace and directory layout.

..  code-block:: text

    Classes/
    ├── Assistant/      Conversation, context, pipeline, prompts and traces
    ├── Backend/        Backend module, diagnostics, configuration and logs
    ├── Connection/     AI and vector store connectors and connection records
    ├── Indexing/       Indexers, adapters, connectors, chunking and source state
    ├── Controller/     Frontend and backend controllers
    └── Command/        CLI commands for indexing and maintenance

High-level data flow
--------------------

..  code-block:: mermaid

    flowchart LR
        U[User] --> C[ChatController]
        C --> O[Assistant Orchestrator]
        O --> P[Pipeline]
        P --> Q[Query processing]
        P --> R[Retriever]
        R --> V[Vector store]
        P --> A[Answer generation]
        A --> L[AI connection]
        P --> T[Pipeline traces]
        A --> U

Indexing flow
-------------

..  code-block:: mermaid

    flowchart LR
        S[Source] --> I[Indexer]
        I --> D[IndexableDocument]
        D --> C[TextChunkerService]
        C --> E[Embedding request]
        E --> AI[AI connection]
        C --> W[VectorDocument]
        W --> VS[Vector store connection]
        I --> State[IndexerSource state]

Registry-based extension points
-------------------------------

Most technical extension points are collected by TYPO3's dependency injection
container and exposed through registries. This keeps the core pipeline generic:
new processors, connectors, indexers and adapters can be added without changing
central orchestration code.

