..  _domains:

Domains
=======

Assistant domain
----------------

Namespace: ``Madj2k\AiAssistant\Assistant``

The assistant domain contains the runtime chat orchestration:

* application orchestrator;
* request and assistant context;
* pipeline and processor interfaces;
* prompt builder;
* retrieval and answer state;
* memory handling;
* pipeline tracing;
* assistant profile and pipeline step models.

Connection domain
-----------------

Namespace: ``Madj2k\AiAssistant\Connection``

The connection domain provides provider abstraction for AI and vector stores. It
contains connection records, repositories, connector interfaces, DTOs and
registries.

Indexing domain
---------------

Namespace: ``Madj2k\AiAssistant\Indexing``

The indexing domain turns source content into vector documents. It contains
indexers, adapters, external connectors, chunking, source state and indexing
repositories.

Backend domain
--------------

Namespace: ``Madj2k\AiAssistant\Backend``

The backend domain contains only backend module concerns such as diagnostics,
runtime configuration, indexer status and pipeline log presentation.

