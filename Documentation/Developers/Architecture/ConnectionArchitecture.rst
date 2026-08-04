..  _connection-architecture:

Connection architecture
=======================

The connection layer abstracts provider-specific APIs.

AI connector interface
----------------------

An AI connector is responsible for:

* chat completions;
* embeddings;
* translating provider responses into core DTOs;
* exposing usage information where available.

Vector store connector interface
--------------------------------

A vector store connector is responsible for:

* creating or validating collections;
* writing vector documents;
* searching vectors;
* deleting documents by source hash;
* translating provider responses into core DTOs.

Connection records
------------------

Connection records store runtime configuration and credentials. They are passed
into connectors for every operation. This keeps connector services reusable and
stateless.
