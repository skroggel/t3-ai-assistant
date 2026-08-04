..  _aiconnectorregistry:

AiConnectorRegistry
===================

Purpose
-------

Resolves AI provider connectors for chat completions and embeddings.

Required interface
------------------

``Madj2k\AiCore\Connection\Ai\AiConnectorInterface``

Service registration
--------------------

Register implementations through a service tag:

..  code-block:: yaml

    services:
      Vendor\Extension\Connection\Ai\MyAiConnector:
        tags:
          - name: 'aiassistant.connection.ai_connector'

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
