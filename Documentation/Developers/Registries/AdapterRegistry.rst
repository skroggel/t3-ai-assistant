..  _adapterregistry:

AdapterRegistry
===============

Purpose
-------

Resolves file/content adapters that extract text from source files.

Required interface
------------------

``Madj2k\AiCore\Indexing\Adapter\AdapterInterface``

Service registration
--------------------

Register implementations through a service tag:

..  code-block:: yaml

    services:
      Vendor\Extension\Indexing\Adapter\MyAdapter:
        tags:
          - name: 'aiassistant.indexing.adapter'

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
