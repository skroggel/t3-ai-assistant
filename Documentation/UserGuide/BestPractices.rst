..  _best-practices:

Best practices
==============

Start simple
------------

Begin with one assistant, one collection and a short pipeline. Add query
optimization, context optimization and quality gates only when you can observe a
specific need in traces.

Use metadata deliberately
-------------------------

Metadata is essential for source attribution and for answering questions about
where information comes from. Keep metadata stable and do not rely on generated
text for source identity.

Keep prompts step-specific
--------------------------

Avoid putting every instruction into the assistant profile. Step-specific rules
make pipeline behaviour easier to test and debug.

Test with ambiguous queries
---------------------------

Use test questions with abbreviations, follow-ups, conflicting documents and
source questions. These queries reveal retrieval and prompt issues quickly.

Avoid duplicate indexing
------------------------

When several indexers can write into the same collection and vector store, they
must agree on stable source identifiers. Otherwise the same content can appear
multiple times in retrieval.

