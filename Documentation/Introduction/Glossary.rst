..  _glossary:

Glossary
========

Assistant profile
    Configuration record describing the assistant identity, global prompts,
    connections and default collection.

Pipeline step
    One configured step in the assistant pipeline.

Processor
    PHP service that executes a pipeline step.

Retriever
    Processor that searches the vector store and stores retrieved documents in
    the request context.

Retrieved context
    Text representation of retrieved documents and selected metadata for use in
    prompts.

Frontend source
    Source metadata exposed to the frontend so answers can link to the
    documents or pages they were based on.

AI connection
    Configuration record for an AI provider, such as an OpenAI-compatible API.

Vector store connection
    Configuration record for a vector database, such as Qdrant.

Collection
    Logical vector index inside a vector store.

Source hash
    Stable identifier for the source object itself.

Content hash
    Hash used to detect source content changes.

