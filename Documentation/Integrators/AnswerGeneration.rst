..  _answer-generation:

Answer generation
=================

The answer generator creates the final user-facing response.

Good answer generation rules
----------------------------

* answer in the user's language;
* use retrieved context as the primary source;
* do not invent facts;
* state when context is insufficient;
* cite or mention sources when the frontend requires source transparency;
* keep the answer concise unless the user asks for detail.

The answer generator may use chat history for follow-up questions, but history
should not override retrieved project knowledge.

