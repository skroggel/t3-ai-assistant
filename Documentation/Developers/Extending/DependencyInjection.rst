..  _dependency-injection:

Dependency injection
====================

AI Assistant extension points are registered with TYPO3's service container.
Services are collected by registries through tagged iterators configured in
``Configuration/Services.yaml``.

Common service tags
-------------------

..  code-block:: yaml

    services:
      Vendor\Extension\Assistant\Pipeline\Processor\MyProcessor:
        tags:
          - name: 'aiassistant.assistant.pipeline.processor'

      Vendor\Extension\Assistant\Prompt\Context\Builder\MyContextBuilder:
        tags:
          - name: 'aiassistant.assistant.prompt.context_builder'

      Vendor\Extension\Indexing\Indexer\MyIndexer:
        tags:
          - name: 'aiassistant.indexing.indexer'

      Vendor\Extension\Indexing\Adapter\MyAdapter:
        tags:
          - name: 'aiassistant.indexing.adapter'

      Vendor\Extension\Indexing\Connector\MyConnector:
        tags:
          - name: 'aiassistant.indexing.connector'

      Vendor\Extension\Connection\Ai\MyAiConnector:
        tags:
          - name: 'aiassistant.connection.ai_connector'

      Vendor\Extension\Connection\VectorStore\MyVectorStoreConnector:
        tags:
          - name: 'aiassistant.connection.vector_store_connector'

Avoid manual instantiation for services that depend on constructor injection.
Use the container and registries instead.
