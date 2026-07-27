..  _retrieval-architecture:

Retrieval architecture
======================

Retrieval links the indexing and assistant domains.

Data flow
---------

..  code-block:: mermaid

    flowchart LR
        Q[Current query] --> R[RetrieverProcessor]
        R --> Emb[Embedding request]
        Emb --> AI[AiConnector]
        AI --> Search[VectorSearchRequest]
        Search --> VS[VectorStoreConnector]
        VS --> Docs[RetrievedDocument list]
        Docs --> State[RetrievalState]
        State --> Builder[AnswerContextBuilder]
        Builder --> Prompt[Retrieved Context]

Retrieved documents
-------------------

A retrieved document should carry:

* text;
* score;
* title;
* URL;
* source type;
* source identifier;
* original payload metadata.

Answer context builder
----------------------

The answer context builder formats selected metadata and text into a prompt
section. It applies step limits and metadata field selection.

