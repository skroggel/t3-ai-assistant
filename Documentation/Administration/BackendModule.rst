..  _backend-module:

Backend module
==============

The backend module is intentionally focused on runtime administration and
debugging.

Areas
-----

Runtime configuration
    Logging and memory-related settings.

Connection tests
    Tests for configured AI and vector store connection records.

Indexer status
    Status information for indexer configurations and source processing.

Pipeline logs
    Trace view for pipeline execution, including filtering and deletion.

Non-goals
---------

The backend module intentionally does not try to be a full indexing workbench. It
is not designed as a preview system, old indexer execution UI, broad health check
center or mass settings editor.
