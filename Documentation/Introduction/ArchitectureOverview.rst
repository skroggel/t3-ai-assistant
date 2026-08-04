..  _architecture-overview:

Architecture overview
=====================

AI Assistant combines the framework-independent ``madj2k/ai-core`` package with
TYPO3-specific integration code.

..  code-block:: text

    madj2k/ai-core
    ├── Assistant/      Runtime, context, pipelines and prompts
    ├── Connection/     Provider contracts and clients
    └── Indexing/       Documents, adapters, chunking and vector writes

    madj2k/ai-assistant
    ├── Assistant/      TYPO3 profiles, memory and traces
    ├── Backend/        Configuration and diagnostics
    ├── Connection/     TYPO3 connection records and registries
    ├── Indexing/       TYPO3 sources and persisted indexing state
    ├── Controller/     Frontend and backend controllers
    └── Command/        CLI commands

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
        D --> C[TextChunker]
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
