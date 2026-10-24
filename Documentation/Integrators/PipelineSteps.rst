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

Intent selector settings
    An ``intent_selector`` step has an ``Intent selector outputs`` multi-select
    with ``retriever``, ``presentation_rules`` and ``retriever_payload``. Empty
    legacy values preserve the original retriever and presentation-rule
    behavior.

    When ``retriever_payload`` is enabled, configure a dot-notated payload field
    such as ``meta.additional.category_ids``. The AI returns only values in the
    ``retrieverPayload.values`` JSON array. The optional payload-retriever
    multi-select is populated from the current pipeline's retriever steps; an
    empty selection applies the filter to all retrievers. The payload value
    conjunction can be ``and`` or ``or`` and is applied by the server, not by
    the AI.

Failure strategy
    Defines how the pipeline reacts if the step fails.

Step prompt placement
---------------------

Prompts belong into assistant profile or pipeline step records. Processor code
should stay generic and should not contain project-specific prompt text.
