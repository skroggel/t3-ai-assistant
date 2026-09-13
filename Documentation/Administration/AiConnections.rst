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
* set the embedding model and its output dimension;
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

Gemini embedding models return 3072 dimensions by default. Store the desired
dimension on the AI connection. The Gemini connector automatically sends it as
``outputDimensionality`` for single and batch embedding requests. When reducing
the embedding dimensions, follow the normalization requirements of the selected
Gemini embedding model.

Existing installations
----------------------

The schema update initializes existing AI connections with the default dimension
of ``1536``. If an installation uses another dimension, update every affected AI
connection and run its connection test. Afterwards, run the assistant diagnostics
to verify the configured dimension against every collection used by that assistant.
