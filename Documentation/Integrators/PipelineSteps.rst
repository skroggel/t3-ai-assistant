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

MCP connections
    optional MCP connection override for a tool-calling answer step. Empty means
    that the assistant profile's MCP connections are inherited. If connections
    are selected, only those MCP servers and their allowed tools are exposed to
    the model.

Tool-calling steps
------------------

Select the processor identifier `aiassistant.answer_generator.tool_calling` for an answer-generator
step to enable the model/tool loop. The step receives only the effective MCP connections. The model
may select a tool among those connections, but it cannot expand the configured scope.

The loop is bounded by the global core round limit and by the `max_rounds` value of each MCP
connection. Tool names are qualified by connection, for example `mcp.shop.list_products`, so tools
with the same server-side name remain unambiguous.

Failure strategy
    Defines how the pipeline reacts if the step fails.

Step prompt placement
---------------------

Prompts belong into assistant profile or pipeline step records. Processor code
should stay generic and should not contain project-specific prompt text.
