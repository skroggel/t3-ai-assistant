..  _about:

About AI Assistant
==================

AI Assistant is a TYPO3 extension for building retrieval-augmented assistants.
It is designed for projects where answers should be grounded in content managed
by TYPO3, uploaded files, structured data or external systems.

The extension separates three concerns:

* assistant configuration and conversation handling;
* indexing and synchronization of knowledge sources;
* provider connections for AI models and vector storage.

This separation allows integrators to create different assistants for different
use cases while developers can add new providers, processors, indexers and
adapters through registries.

Typical use cases
-----------------

AI Assistant can be used for:

* website assistants that answer questions from TYPO3 pages;
* documentation assistants for manuals, text files and structured knowledge bases;
* product assistants that retrieve data from commerce systems;
* internal support assistants for policies, contact directories or procedures;
* experimental assistant pipelines where query rewriting, retrieval, context
  optimization and answer validation are configured independently.

The extension does not assume one fixed chat workflow. Instead, each assistant
profile references a sequence of pipeline steps. Each step decides what happens
at that point in the request lifecycle.



Premium extension scope
-----------------------

Shopware indexing, the PDF file adapter and the ke_search integration with
result summaries are maintained in the premium extension. This manual documents
the public extension scope and its extension points only.
