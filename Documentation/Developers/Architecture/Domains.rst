..  _domains:

Domains
=======

The framework-independent contracts and runtime live in ``madj2k/ai-core``.
This extension adds TYPO3 configuration, persistence and integration classes.

Assistant domain
----------------

Namespace: ``Madj2k\AiAssistant\Assistant``

The TYPO3 assistant domain contains:

* memory handling;
* pipeline tracing;
* assistant profile and pipeline step models.

The orchestrator, context, pipeline, processors and prompt building are provided
by ``Madj2k\AiCore\Assistant``.

Connection domain
-----------------

Namespace: ``Madj2k\AiAssistant\Connection``

The connection domain contains TYPO3 connection records, repositories and
registries. Connector contracts, normalized DTOs and built-in provider clients
are provided by ``Madj2k\AiCore\Connection``.

Indexing domain
---------------

Namespace: ``Madj2k\AiAssistant\Indexing``

The indexing domain discovers TYPO3 and external sources and persists indexer
state. Generic document DTOs, adapter contracts, chunking and vector writes are
provided by ``Madj2k\AiCore\Indexing``.

Backend domain
--------------

Namespace: ``Madj2k\AiAssistant\Backend``

The backend domain contains only backend module concerns such as diagnostics,
runtime configuration, indexer status and pipeline log presentation.
