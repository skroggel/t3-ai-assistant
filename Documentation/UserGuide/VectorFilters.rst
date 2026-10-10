..  _vector-filters:

Vector filters
==============

Retrieval steps can restrict vector results using indexed payload metadata.
Filters are configured as inline conditions in the retrieval step.

Each condition contains:

* a payload field path, for example ``meta.document_type``;
* an operator: ``Equals``, ``In list`` or ``Exists``;
* a value.

All conditions in a step are currently combined with ``AND``.

Examples
--------

Exact value:

..  code-block:: text

    Field: meta.document_type
    Operator: Equals
    Value: faq

Multiple values:

..  code-block:: text

    Field: meta.language
    Operator: In list
    Value: de, en

TYPO3 frontend language
-----------------------

The TYPO3 resolver supports:

..  code-block:: text

    ###FE_LANG###

It resolves to the current frontend language code, such as ``de`` or ``en``.
This is useful for language-aware retrieval:

..  code-block:: text

    Field: meta.language
    Operator: Equals
    Value: ###FE_LANG###

The filter model remains framework-independent in ``ai-core``. TYPO3-specific
runtime placeholders are resolved by the TYPO3 adapter before the connector is
called.
