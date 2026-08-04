..  _pipeline-architecture:

Pipeline architecture
=====================

The pipeline is the central runtime abstraction of the assistant. It executes a
sequence of configured steps against a request context.

Core classes
------------

``Madj2k\AiCore\Assistant\Pipeline\Pipeline``
    Executes configured steps.

``Madj2k\AiCore\Assistant\Pipeline\Processor\ProcessorInterface``
    Common interface for all pipeline processors.

``Madj2k\AiCore\Assistant\Pipeline\Registry\ProcessorRegistry``
    Resolves processors by identifier.

``Madj2k\AiCore\Assistant\Prompt\PromptBuilder``
    Builds LLM messages from profile prompts, step prompts, history and context.

``Madj2k\AiCore\Assistant\Context\Context``
    Shared state object for one assistant request.

Processor responsibilities
--------------------------

Processors must stay generic. Project-specific instructions belong into database
records. A processor should read step configuration and context, execute its
technical task and write results back into context.

LLM processors
--------------

LLM-based processors should call the AI provider through the configured
``AiConnection`` and ``AiConnectorRegistry``. They should not instantiate provider
clients directly.

Streaming execution
-------------------

``Madj2k\AiCore\Assistant\Pipeline\Pipeline`` can run in synchronous mode
through ``run()`` or in streaming mode through ``runStream()``. In streaming
mode the pipeline forwards streamed chunks to the callback passed by the caller.

A processor can participate in streaming by implementing
``Madj2k\AiCore\Assistant\Pipeline\Processor\ProcessorStreamingInterface``
in addition to ``ProcessorInterface``. If a streaming callback is available and
the processor implements that interface, the pipeline calls ``processStream()``.
Otherwise it falls back to the regular ``process()`` method.

Prompt context builders
-----------------------

Prompt context is assembled through
``Madj2k\AiCore\Assistant\Prompt\Context\Registry\ContextBuilderRegistry``.
The registry collects all tagged context builders that support the current
processor type and returns sorted ``PromptSection`` objects. This keeps prompt
context construction extensible without hard-coding every section in one
processor.

Pipeline tracing
----------------

Processors can emit trace events for started steps, finished steps, LLM requests,
LLM responses, retrieval results and errors. Traces are essential for debugging
pipeline behaviour.
