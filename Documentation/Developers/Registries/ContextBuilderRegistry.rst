..  _contextbuilderregistry:

ContextBuilderRegistry
======================

Purpose
-------

Collects structured prompt context sections from all registered context
builders that support the current pipeline processor type.

The registry is used by prompt building to keep prompt sections modular. Each
builder can contribute one or more ``PromptSection`` instances for a specific
processor type, for example query optimization, retrieval context optimization
or answer generation.

Required interface
------------------

``Madj2k\AiCore\Assistant\Prompt\Context\Builder\ContextBuilderInterface``

Service registration
--------------------

Register implementations through the context builder service tag:

..  code-block:: yaml

    services:
      Vendor\Extension\Assistant\Prompt\Context\Builder\MyContextBuilder:
        tags:
          - name: 'aiassistant.assistant.prompt.context_builder'

Selection
---------

The registry calls ``supports()`` with the current
``AssistantPipelineProcessorType``. Builders that return ``true`` can add
sections through ``build()``.

..  code-block:: php

    use Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType;

    public function supports(AssistantPipelineProcessorType $type): bool
    {
        return $type === AssistantPipelineProcessorType::AnswerGenerator;
    }

Output
------

``build()`` must return an array of ``PromptSection`` objects. Empty sections are
ignored by the registry. Non-empty sections are sorted by their priority before
they are used for the final prompt context.

Implementation rules
--------------------

* Keep builders focused on one context concern.
* Read from the shared assistant ``Context`` instead of loading unrelated state.
* Return structured ``PromptSection`` objects instead of formatted prompt text
  where possible.
* Use priorities deliberately so project-specific sections can be placed before
  or after the built-in context sections.
