..  _creating-processor:

Creating a pipeline processor
=============================

Create a service implementing
``Madj2k\AiAssistant\Assistant\Pipeline\Processor\ProcessorInterface``.
Processors are resolved through ``ProcessorRegistry`` by their stable identifier
and the configured pipeline step type.

Processor rules
---------------

* Do not hard-code project prompts in the processor.
* Read prompts and settings from the pipeline step record.
* Use the shared context object for input and output.
* Use registered connectors for AI or vector store access.
* Write trace data that helps debug the processor.

A processor should be small and explicit. If it needs LLM access, consider
sharing common logic through an abstract LLM processor.

Step type and enum values
-------------------------

Pipeline processors are selected from pipeline step records. The ``type`` field
is not an arbitrary string. It must use one of the values defined by
``Madj2k\AiAssistant\Assistant\Enum\AssistantPipelineProcessorType``.

Use the enum as the source of truth when creating TCA, seed data, SQL imports or
custom pipeline configurations. This keeps backend records, processor matching
and pipeline execution aligned.

Typical processor types include:

* ``query_optimizer`` for query contextualization and search refinement;
* ``retriever`` for vector store retrieval;
* ``context_optimizer`` for reducing and structuring retrieved context;
* ``answer_generator`` for generating the visible draft answer;
* ``quality_gate`` for validating or improving the final answer.

A processor implementation should declare a stable processor identifier and
match only the step types it supports.

..  code-block:: php

    use Madj2k\AiAssistant\Assistant\Domain\Model\AssistantPipelineStep;
    use Madj2k\AiAssistant\Assistant\Context\Context;
    use Madj2k\AiAssistant\Assistant\Enum\AssistantPipelineProcessorType;
    use Madj2k\AiAssistant\Assistant\Log\PipelineLogMetaData;
    use Madj2k\AiAssistant\Assistant\Pipeline\Processor\ProcessorInterface;

    final class MyAnswerProcessor implements ProcessorInterface
    {
        public function getIdentifier(): string
        {
            return 'vendor.answer_generator.default';
        }

        public function supports(AssistantPipelineProcessorType $type): bool
        {
            return $type === AssistantPipelineProcessorType::AnswerGenerator;
        }

        public function canProcess(Context $context, AssistantPipelineStep $step): bool
        {
            return $context->getCurrentQuery() !== '';
        }

        public function process(
            Context $context,
            AssistantPipelineStep $step,
            ?PipelineLogMetaData $logContext = null
        ): void {
            // Read from $context and $step, call connectors if needed,
            // then write the processor result back to $context.
        }
    }

When writing static SQL configuration, use the enum values for the ``type``
column and the processor identifier for the ``processor_identifier`` column.
Do not invent new type strings unless the enum and TCA are extended accordingly.

Service registration
--------------------

..  code-block:: yaml

    services:
      Vendor\Extension\Assistant\Pipeline\Processor\MyAnswerProcessor:
        tags:
          - name: 'aiassistant.assistant.pipeline.processor'

Streaming processors
--------------------

Processors that can emit output chunks during execution should additionally
implement
``Madj2k\AiAssistant\Assistant\Pipeline\Processor\ProcessorStreamingInterface``.
The regular ``ProcessorInterface`` remains required, because the same processor
must also work when the pipeline runs without streaming.

..  code-block:: php

    use Madj2k\AiAssistant\Assistant\Pipeline\Processor\ProcessorStreamingInterface;

    final class MyAnswerProcessor implements ProcessorInterface, ProcessorStreamingInterface
    {
        public function processStream(
            Context $context,
            AssistantPipelineStep $step,
            callable $onData,
            ?PipelineLogMetaData $logContext = null
        ): void {
            foreach ($this->createChunks($context, $step) as $chunk) {
                $onData($chunk);
            }
        }
    }

When ``Pipeline::runStream()`` is used, the pipeline calls ``processStream()``
only for processors implementing ``ProcessorStreamingInterface``. All other
processors are executed through ``process()``.
