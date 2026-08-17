..  _indexerregistry:

IndexerRegistry
===============

Purpose
-------

Resolves indexers for configured source types or indexer identifiers.

Required interface
------------------

``Madj2k\AiCore\Indexing\Indexer\IndexerInterface``

Service registration
--------------------

Register implementations through a service tag:

..  code-block:: yaml

    services:
      Vendor\Extension\Indexing\Indexer\MyIndexer:
        tags:
          - name: 'aiassistant.indexing.indexer'

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


Indexer type matching
---------------------

The registry resolves indexers by ``indexer_identifier`` and/or configured
source type. The indexer ``type`` should always be one of the values defined by
``Madj2k\AiAssistant\Indexing\Enum\IndexerType``.

Indexer implementations and static SQL configuration should use the enum values
as the canonical type names. If a project introduces a new source type, the enum,
TCA types and registry configuration must be extended consistently.
