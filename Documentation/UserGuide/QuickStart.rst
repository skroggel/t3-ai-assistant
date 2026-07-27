..  _quick-start:

Quick start
===========

A minimal setup consists of:

#. one AI connection;
#. one vector store connection;
#. one or more indexer configurations;
#. one assistant profile;
#. a pipeline for the assistant profile;
#. a frontend plugin or route that exposes the assistant.

Recommended first setup
-----------------------

For a first test, use TYPO3 pages as the knowledge source:

#. Create an AI connection with chat and embedding model settings.
#. Create a vector store connection and choose a collection name.
#. Create a page indexer configuration for a small page tree.
#. Run indexing.
#. Create an assistant profile using the same vector store connection and
   collection.
#. Add a pipeline with retrieval and answer generation.
#. Enable pipeline logging while testing.

Default setup import
--------------------

The extension can ship a default setup through TYPO3's extension import
mechanism. Importing the default records is useful as a starting point because it
creates a working baseline for assistant profiles and pipeline steps.

After importing a default setup, review connection records and credentials.
Default setup records cannot know your provider keys, vector database endpoint,
collection names or project-specific source configuration.

