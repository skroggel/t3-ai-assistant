..  _creating-vector-store-connector:

Creating a vector store connector
=================================

Vector store connectors integrate vector databases into AI Assistant. They are
resolved through ``VectorStoreConnectorRegistry`` by their identifier and are
configured through vector store connection records.

Responsibilities
----------------

* expose a stable connector identifier;
* write vector documents;
* search vectors;
* delete all chunks belonging to a source hash;
* create or validate collections if supported;
* preserve metadata payloads;
* map backend responses to extension DTOs.

Identifier
----------

The identifier is stored in the vector store connection record.

..  code-block:: php

    public function getIdentifier(): string
    {
        return 'vendor.vector_store';
    }

Required capabilities
---------------------

A vector store connector should support at least:

* collection-aware writes;
* similarity search with configurable ``top_k`` and score threshold;
* payload metadata storage;
* deletion by source hash;
* stable document IDs.

Deletion by source hash
-----------------------

Deletion by source hash is required for re-indexing and cleanup. The connector
must delete all vector chunks that belong to the same source in the selected
collection and vector store.

..  code-block:: php

    public function deleteBySourceHash(
        VectorStoreConnection $connection,
        string $collection,
        string $sourceHash
    ): void {
        // Delete all points with payload.source_hash = $sourceHash.
    }

Service registration
--------------------

..  code-block:: yaml

    services:
      Vendor\Extension\Connection\VectorStore\MyVectorStoreConnector:
        tags:
          - name: 'aiassistant.connection.vector_store_connector'

Configuration usage
-------------------

After registration, create a vector store connection record with
``connector_identifier`` set to the connector identifier.

Example:

..  code-block:: text

    connector_identifier = vendor.vector_store
