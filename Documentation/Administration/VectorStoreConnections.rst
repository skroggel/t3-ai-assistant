..  _vector-store-connections:

Vector store connections
========================

Vector store connections define where embeddings and source chunks are stored.

Configuration checklist
-----------------------

* choose the connector identifier;
* enter the endpoint;
* enter API credentials if required;
* configure default collection;
* configure additional collections available to retriever steps;
* configure vector size to match the embedding model;
* choose the distance metric;
* test the connection in the backend module.

Storage and collection scope
----------------------------

An assistant profile selects the default vector store connection. A retriever
step may override this connection and may select one of the effective
connection's configured collections. Without overrides, the profile connection
and its default collection are used.

The same collection name can exist in different vector stores. Indexing state
therefore needs to consider both the vector store connection and the collection.
The source hash itself should identify the source object and should not include
storage or collection information.
