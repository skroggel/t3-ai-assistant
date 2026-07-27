..  _ai-connections:

AI connections
==============

AI connection records centralize provider access. Processors and indexers should
not contain provider credentials.

Configuration checklist
-----------------------

* choose the connector identifier;
* enter the provider endpoint if needed;
* enter API credentials;
* set a default chat model;
* set embedding model and dimensions according to the vector store;
* test the connection in the backend module.

Model defaults
--------------

Pipeline steps can define model, temperature and token limits. If a step does
not set a model, the connection default should be used.

