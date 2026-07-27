..  _understanding-pipelines:

Understanding pipelines
=======================

The assistant pipeline defines how a user request is processed. It can be as
simple or as advanced as the project requires.

Common step types
-----------------

Query optimizer
    Rewrites or clarifies the search query. Before retrieval it uses the current
    query and optional chat history. After retrieval it can also use the first
    retrieved context to improve the search query. It must return only a search
    query, never an answer.

Retriever
    Searches the vector store and stores retrieved documents in the context.

Context optimizer
    Reduces, orders or restructures retrieved context before answer generation.

Answer generator
    Generates the final user-facing answer from the current query, chat history,
    retrieved context and assistant rules.

Quality gate
    Reviews the answer for grounding, consistency and rule compliance. Depending
    on configuration, it can pass, improve or reject an answer.

Example pipeline
----------------

..  code-block:: text

    1. Query optimization before retrieval
    2. Retrieval
    3. Query refinement after retrieval
    4. Retrieval with refined query
    5. Context optimization
    6. Answer generation
    7. Quality gate

Not every project needs every step. For small sites, retrieval plus answer
generation may be sufficient. For ambiguous queries, product data or legal-like
content, query optimization and quality gates are usually helpful.

