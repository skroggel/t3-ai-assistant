..  _concepts:

Core concepts
=============

Assistant profile
-----------------

An assistant profile defines the visible assistant and its global behaviour. It
contains identity, behaviour, retrieval and output rules and references the AI
connection, vector store connection and default collection used by the assistant.

Pipeline
--------

A pipeline is an ordered sequence of processing steps. A simple pipeline may only
retrieve context and generate an answer. A more advanced pipeline may optimize
the query before and after retrieval, reduce the context, generate an answer and
validate the answer with a quality gate.

Pipeline step
-------------

A pipeline step defines one processor execution. It can include parts of the
assistant profile prompt, provide step-specific instructions and configure model,
temperature, token limits, retrieval limits, metadata fields and failure strategy.

Retrieval
---------

Retrieval searches a vector store for documents relevant to the current query.
The retrieved documents are transformed into a prompt context. Metadata fields
can be selected for the prompt and for frontend source attribution.

Indexing
--------

Indexing converts source content into chunks, creates embeddings and writes
vector documents into a vector store. Supported source families include TYPO3
pages, files and external systems. The indexing architecture can be extended with
custom indexers, source connectors and file adapters.

Connections
-----------

AI connections define access to chat and embedding providers. Vector store
connections define access to vector databases. Assistant profiles and indexer
configurations reference those connection records instead of storing credentials
directly in processors or indexers.

Source identity
---------------

The extension differentiates source identity and content changes:

* the source hash identifies the same source object across indexing runs;
* the content hash detects whether the source content changed;
* collection and vector store connection define where the source is indexed.

This allows re-indexing to update or remove the right vector documents without
creating duplicates.

