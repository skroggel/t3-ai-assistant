..  _data-pipeline-steps:

Pipeline step records
=====================

Pipeline step records store per-step configuration:

* assistant profile reference;
* title and processor identifier;
* stage and type;
* prompt inclusion flags;
* step-specific prompts;
* history mode and history limit;
* LLM settings;
* retrieval settings;
* context limits;
* metadata field lists;
* failure strategy.

Prompt text for project behaviour belongs here or in assistant profiles, not in
processor PHP code.

