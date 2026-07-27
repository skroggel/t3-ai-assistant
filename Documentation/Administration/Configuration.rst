..  _configuration:

Configuration
=============

Runtime configuration controls operational behaviour such as memory and logging.
Project-specific behaviour should generally be configured in assistant profiles
and pipeline steps, not in global PHP configuration.

Important runtime areas
-----------------------

Memory
    Maximum number of prompts or messages kept in session memory.

Pipeline logging
    Logging mode, PSR log forwarding, sensitive data masking and maximum payload
    size.

Backend module
    Displays diagnostics, indexer status and pipeline traces.

Default setup
-------------

A default setup can be imported through TYPO3's extension imports. Use it as a
baseline, then adapt profiles, steps, collections and connections to the project.

