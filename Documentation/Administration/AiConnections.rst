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

Gemini
------

Select ``gemini`` as connector and leave the base URL empty to use the official
Gemini API endpoint. Configure a chat model and an embedding model available to
the API key.

Gemini embedding models return 3072 dimensions by default. The vector size of
the selected vector-store connection must match. Operation-specific connection
options can be grouped under ``chat`` and ``embedding`` so embedding settings
are not forwarded to chat requests. For example:

..  code-block:: json

    {
      "embedding": {
        "embedContentConfig": {
          "outputDimensionality": 1536
        }
      }
    }

When reducing the embedding dimensions, use the same dimension in the
vector-store connection and follow the normalization requirements of the
selected Gemini embedding model.
