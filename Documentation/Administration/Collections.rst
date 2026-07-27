..  _collections:

Collections
===========

A collection is the retrieval space searched by an assistant. It should be
planned deliberately.

Recommended collection strategy
-------------------------------

One collection per assistant scope
    Use this when assistants serve clearly different knowledge areas.

One shared collection
    Use this when multiple assistants should search the same content with
    different prompts or answer styles.

Separate test collection
    Use this when changing chunking, metadata or source identity rules.

Avoid mixing unrelated knowledge in a single collection unless the assistant is
explicitly designed to handle that broad scope.

