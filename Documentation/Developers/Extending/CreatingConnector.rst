..  _creating-source-connector:

Creating a source connector
===========================

Source connectors fetch data from external systems. They are resolved through
``ConnectorRegistry`` and are usually used by indexers.

Responsibilities
----------------

* expose a stable connector identifier;
* read credentials from connector or indexer configuration;
* fetch records in batches;
* support cursors or date ranges where needed;
* return raw data in a format the indexer can transform;
* keep API-specific logic outside the indexer.

Identifier
----------

..  code-block:: php

    public function getIdentifier(): string
    {
        return 'vendor.external_source';
    }

Example skeleton
----------------

..  code-block:: php

    final class MySourceConnector implements MySourceConnectorInterface
    {
        public function getIdentifier(): string
        {
            return 'vendor.external_source';
        }

        public function fetchRecords(
            IndexerConfig $configuration,
            int $page,
            int $limit
        ): array {
            // Fetch external records and return normalized raw data.
        }
    }

Service registration
--------------------

..  code-block:: yaml

    services:
      Vendor\Extension\Indexing\Connector\MySourceConnector:
        tags:
          - name: 'aiassistant.indexing.connector'

Design rule
-----------

Keep API-specific logic in connectors and indexing decisions in indexers. The
connector fetches data; the indexer decides how source identifiers, text content
and metadata are created.
