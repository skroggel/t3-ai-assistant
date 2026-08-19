..  _pipeline-steps:

Pipeline steps
==============

Each pipeline step references a processor and configures what prompt sections are
included.

Common fields
-------------

Title
    Human-readable step name. Retriever-step titles must be unique within the
    pipeline because they also name the retrieval in prompts and stored context.

Stage
    Position in the logical pipeline, such as pre-retrieval, retrieval,
    post-retrieval or answer generation.

Processor identifier
    Registry identifier of the PHP processor service.

Prompt include flags
    Decide whether identity, behaviour, retrieval and output rules from the
    assistant profile are included.

Step prompt fields
    Step identity, behaviour, retrieval and output rules.

LLM settings
    Model, temperature and max tokens.

Retrieval settings
    optional connection and collection overrides, maximum retrieval results,
    score threshold, context limits and metadata fields. Each retriever adds a
    named group to the existing retrieval context.

Failure strategy
    Defines how the pipeline reacts if the step fails.

Step prompt placement
---------------------

Prompts belong into assistant profile or pipeline step records. Processor code
should stay generic and should not contain project-specific prompt text.
