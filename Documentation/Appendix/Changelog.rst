..  _changelog:

Changelog
=========

This documentation is intended to be maintained together with the extension.
Use this page for documentation-level changes or link to the project changelog.

2026-08-17
----------

Breaking changes
~~~~~~~~~~~~~~~~

The temporary ``Madj2k\\AiAssistant`` aliases for framework-independent
assistant, connection and indexing classes have been removed. Extensions must
use the corresponding ``Madj2k\\AiCore`` classes and interfaces directly.
TYPO3-specific models, repositories, controllers and integration services
remain in the ``Madj2k\\AiAssistant`` namespace.
