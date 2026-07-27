..  _context-optimization:

Context optimization
====================

A context optimizer step is useful when retrieval returns many chunks, duplicate
snippets or mixed topics. It can ask the model to select only the most relevant
context before answer generation.

Good instructions for a context optimizer
-----------------------------------------

* keep only context relevant to the current user query;
* preserve source metadata;
* remove duplicates;
* keep contradictory information if it matters;
* do not answer the user;
* return structured context for the answer generator.

Do not use a context optimizer as a replacement for correct indexing or
retrieval configuration.

