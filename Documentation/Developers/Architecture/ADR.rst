..  _adr:

Architecture decisions
======================

ADR-001: Registry-based extension architecture
----------------------------------------------

The extension uses registries for processors, indexers, connectors and adapters.
This keeps orchestration generic and allows third-party extensions to add
functionality through dependency injection tags.

ADR-002: Separate AI and vector store connections
-------------------------------------------------

AI providers and vector databases are configured independently. This allows the
same assistant to use different providers for chat, embeddings and vector
storage without changing pipeline code.

ADR-003: Pipeline instead of fixed chat workflow
-----------------------------------------------

The assistant request is processed by configured steps. This supports simple
retrieval-augmented answers and advanced workflows with query refinement,
context optimization and quality gates.

ADR-004: Prompts in records, not processors
-------------------------------------------

Processors implement behaviour. Project-specific prompt text belongs into
assistant profile and pipeline step records so it can be configured and imported.

ADR-005: SourceHash and ContentHash separation
----------------------------------------------

A stable source hash identifies the same source. A content hash detects changes.
This avoids duplicate vectors after re-indexing.

ADR-006: Collection and storage are indexing scope
--------------------------------------------------

The same collection name can exist in different vector stores. Indexing state
therefore considers both vector store connection and collection. Source hash
remains independent of storage and collection.

ADR-007: Metadata-driven retrieval
----------------------------------

Retrieved documents must preserve payload metadata. Prompt metadata and frontend
source fields are configured per step to balance traceability, safety and prompt
size.

