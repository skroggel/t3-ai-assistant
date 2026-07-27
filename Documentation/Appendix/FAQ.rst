..  _faq:

FAQ
===

Why does the assistant need indexing?
-------------------------------------

The assistant can only retrieve knowledge that has been indexed into the vector
store. Indexing transforms source content into embeddings that can be searched.

Can one assistant use multiple collections?
-------------------------------------------

The standard profile has one default collection. Advanced behaviour can be
implemented through custom processors or separate assistants.

Why are prompts stored in records?
----------------------------------

Prompts are configuration. Storing them in assistant profile and pipeline step
records allows integrators to adapt behaviour without changing PHP code.

Why do I see duplicate retrieval results?
-----------------------------------------

Usually because source identity, collection scope or cleanup rules changed after
content was already indexed. Rebuild the affected collection after fixing source
identity rules.

