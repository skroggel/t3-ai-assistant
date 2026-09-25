..  _connection-management:

Connection management
=====================

AI Assistant stores provider settings in connection records.

AI connections
--------------

AI connections configure chat and embedding providers. Typical fields are:

* connector identifier;
* base URL;
* authentication mode;
* API key when using API-key authentication;
* OAuth 2.0 Client Credentials settings when using OAuth authentication;
* organization or project;
* default chat model;
* default temperature;
* embedding model;
* embedding dimension;
* additional provider options.

Authentication modes
~~~~~~~~~~~~~~~~~~~~

Use **API Key** for providers that issue a static API key. Use **OAuth 2.0 Client Credentials** for
server-to-server provider access. The OAuth configuration consists of:

* token endpoint;
* client ID;
* client secret;
* optional scope.

The connector obtains and caches the access token automatically. The client secret should be
protected like an API key and must not be included in additional options or log output.

Vector store connections
------------------------

Vector store connections configure vector databases. Typical fields are:

* connector identifier;
* endpoint;
* API key;
* default collection;
* additional allowed collections;
* distance metric;
* additional provider options.

Connection tests
----------------

The backend module provides connection tests for configured AI and vector store connections. It
also provides assistant profile tests that resolve step
overrides and verify that every effective collection exists on the selected
vector store and matches the AI connection's embedding dimension. Use those
tests after changing an embedding configuration and before debugging indexer or
pipeline behaviour.
