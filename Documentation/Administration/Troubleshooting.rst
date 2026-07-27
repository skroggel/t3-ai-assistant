..  _troubleshooting:

Troubleshooting
===============

No answers from indexed content
-------------------------------

Check:

* indexer has written documents;
* assistant uses the same vector store connection and collection;
* embedding model vector size matches the vector store;
* retrieval top_k is greater than zero;
* score threshold is not too strict.

Duplicate retrieval results
---------------------------

Check:

* stable source identifiers;
* source hash generation;
* collection and vector store connection scoping;
* whether old collection data must be rebuilt after indexing changes.

Wrong or missing sources
------------------------

Check:

* metadata is present in vector payload;
* ``prompt_metadata_fields`` includes relevant fields;
* ``frontend_source_fields`` includes title and URL;
* answer generator prompt asks for source-aware answers if needed.

Query optimizer answers instead of rewriting
--------------------------------------------

Check that the query optimizer step prompt says it must return only a search
query and must not answer or summarize the retrieved result.

