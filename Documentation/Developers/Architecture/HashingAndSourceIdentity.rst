..  _hashing-source-identity:

Hashing and source identity
===========================

Source identity and change detection must be separate.

Source hash
-----------

The source hash identifies the source object. It should be derived from stable
source information, such as:

..  code-block:: text

    source_type | source_identifier | language

It should not include content, collection or vector store information.

Content hash
------------

The content hash detects changes. It can include normalized text and relevant
metadata that should trigger re-indexing.

Storage scope
-------------

The same source can be indexed into multiple vector stores or collections. The
indexing state must therefore track vector store connection and collection
separately from the source hash.

Vector document identity
------------------------

Vector document ids should be stable per source and chunk. The vector database
may require UUID-compatible identifiers. Do not use arbitrary SHA-1 hex strings
as ids for vector stores that require UUIDs or integers.

