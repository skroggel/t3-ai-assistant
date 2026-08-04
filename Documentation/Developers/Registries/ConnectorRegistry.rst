..  _connectorregistry:

ConnectorRegistry
=================

Purpose
-------

Resolves external source connectors used by indexers, for example commerce or API connectors.

Required interface
------------------

``Madj2k\AiCore\Indexing\Connector\ConnectorInterface``

Service registration
--------------------

Register implementations through a service tag:

..  code-block:: yaml

    services:
      Vendor\Extension\Indexing\Connector\MyConnector:
        tags:
          - name: 'aiassistant.indexing.connector'

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
