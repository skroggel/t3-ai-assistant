..  _commands:

Console commands
================

AI Assistant provides TYPO3 console commands for recurring indexing and
maintenance tasks. Commands are intended for administrators, deployments and
scheduled jobs. They use the configured indexer records, AI connections and
vector store connections from the TYPO3 backend.

Run commands in DDEV
--------------------

In a DDEV project, execute commands through TYPO3's console entry point:

..  code-block:: bash

    ddev exec vendor/bin/typo3 aiassistant:index:pages --help

Depending on the project setup, the binary can also be called from inside the
container:

..  code-block:: bash

    ddev ssh
    vendor/bin/typo3 aiassistant:index:pages --help

Index TYPO3 pages
-----------------

The page indexing command indexes configured page trees and content elements
into the selected vector store collection.

..  code-block:: bash

    ddev exec vendor/bin/typo3 aiassistant:index:pages

Common examples:

..  code-block:: bash

    # Index one configured indexer record
    ddev exec vendor/bin/typo3 aiassistant:index:pages --indexer=1000

    # Override the target collection for this run
    ddev exec vendor/bin/typo3 aiassistant:index:pages --indexer=1000 --collection=assistant_test

    # Process a small batch only
    ddev exec vendor/bin/typo3 aiassistant:index:pages --indexer=1000 --limit=25

    # Preview without writing embeddings or source state
    ddev exec vendor/bin/typo3 aiassistant:index:pages --indexer=1000 --dry-run

    # Start from the beginning instead of using the stored cursor
    ddev exec vendor/bin/typo3 aiassistant:index:pages --indexer=1000 --reset-cursor

Index files
-----------

The file indexing command indexes files from configured import paths. Import
paths may point to local paths or to TYPO3 FAL identifiers such as
``1:/user_uploads``.

..  code-block:: bash

    ddev exec vendor/bin/typo3 aiassistant:index:files --indexer=1001

Useful examples:

..  code-block:: bash

    # Index files into a temporary collection
    ddev exec vendor/bin/typo3 aiassistant:index:files --indexer=1001 --collection=file_test

    # Continue after a known cursor
    ddev exec vendor/bin/typo3 aiassistant:index:files --indexer=1001 --cursor="1:/user_uploads/manual.md"

    # Return machine-readable output for automation
    ddev exec vendor/bin/typo3 aiassistant:index:files --indexer=1001 --json

Premium commands
----------------

Shopware indexing, Shopware cleanup, Shopware smoke tests and ke_search result
summary generation belong to the premium extension. Their command reference is
therefore maintained in the premium extension documentation.

Common options
--------------

``--indexer``
    UID of the indexer configuration record to use. This is recommended for
    scheduled jobs so that the command targets exactly one configuration.

``--collection``
    Overrides the collection configured in the indexer record for this run. This
    is useful for testing, rebuilds and migrations.

``--cursor``
    Starts or continues processing after an explicit cursor. Cursors are stable
    source identifiers managed by the individual indexer.

``--reset-cursor``
    Ignores the stored cursor and starts from the beginning.

``--dry-run``
    Executes the discovery and extraction flow without writing embeddings,
    vector documents or source state.

``--only-changed``
    Skips sources whose content hash did not change since the last successful
    indexing run.

``--limit``
    Limits the number of sources processed in one batch. This is useful for
    cronjobs and large initial imports.

``--json``
    Returns machine-readable output suitable for CI jobs, deployment scripts or
    monitoring.

Scheduling
----------

Commands can be executed through cron, systemd timers or deployment pipelines.
For larger sites, prefer small recurring batches instead of one long-running
indexing process.

Example cron-style call:

..  code-block:: bash

    ddev exec vendor/bin/typo3 aiassistant:index:pages --indexer=1000 --limit=100 --only-changed --json

Recommended workflow
--------------------

#. Configure and test AI and vector store connections.
#. Configure the indexer record in the TYPO3 backend.
#. Run the command once with ``--dry-run``.
#. Run a small batch with ``--limit``.
#. Check indexer status and pipeline logs in the backend module.
#. Schedule recurring indexing once the result is stable.
