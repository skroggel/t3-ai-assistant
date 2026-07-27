..  _connection-management:

Connection management
=====================

AI Assistant stores provider settings in connection records.

AI connections
--------------

AI connections configure chat and embedding providers. Typical fields are:

* connector identifier;
* base URL;
* API key;
* organization or project;
* default chat model;
* default temperature;
* embedding model;
* additional provider options.

Vector store connections
------------------------

Vector store connections configure vector databases. Typical fields are:

* connector identifier;
* endpoint;
* API key;
* default collection;
* vector size;
* distance metric;
* additional provider options.

Connection tests
----------------

The backend module provides connection tests for configured AI and vector store
connections. Use those tests before debugging indexer or pipeline behaviour.

