..  _processorregistry:

ProcessorRegistry
=================

Purpose
-------

Resolves assistant pipeline processors by identifier.

Required interface
------------------

``Madj2k\AiCore\Assistant\Pipeline\Processor\ProcessorInterface``

Service registration
--------------------

Register implementations through a service tag:

..  code-block:: yaml

    services:
      Vendor\Extension\Assistant\Pipeline\Processor\MyProcessor:
        tags:
          - name: 'aiassistant.assistant.pipeline.processor'

Identifier
----------

The implementation should expose a stable identifier. Configuration records use
that identifier to resolve the implementation at runtime.

Implementation rules
--------------------

* Keep services stateless where possible.
* Read credentials and runtime options from configuration records.
* Throw meaningful exceptions for invalid configuration.
* Return core DTOs rather than provider-specific arrays where interfaces
  define DTOs.


Processor type matching
-----------------------

The registry resolves processors by ``processor_identifier``. The pipeline step
``type`` is still important and should always be one of the values defined by
``Madj2k\AiCore\Assistant\Enum\AssistantPipelineProcessorType``.

Processor implementations should use this enum when implementing ``supports()``
or equivalent matching logic. Static SQL and default configurations should also
use enum values for the ``type`` column.
