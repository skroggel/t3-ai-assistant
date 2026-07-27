..  _upgrade:

Upgrade
=======

Before upgrading in production:

* export assistant profiles and pipeline step records;
* note configured connection records and collection names;
* backup vector store collections if supported by the backend;
* review database schema changes;
* run indexing in a test collection after changes to source identity or chunking.

When prompts or default setup records change, existing customized records should
not be overwritten automatically. Review changes manually and apply them where
they improve the project-specific assistant.

