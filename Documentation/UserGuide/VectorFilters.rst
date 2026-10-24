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

Intent-selector payload filters
-------------------------------

An intent selector can create a filter dynamically from the AI-selected values.
The payload field path is configured on the intent-selector step, for example:

..  code-block:: text

    meta.additional.category_ids

The AI returns only the values:

..  code-block:: json

    {
        "retrieverPayload": {
            "values": [12, 15]
        }
    }

The selected retriever steps are configured separately in the
``Payload filter retrievers`` multi-select. With no selected retrievers, the
dynamic filter applies to all retrievers allowed by the intent-selection plan.
The ``and``/``or`` conjunction is configured in TYPO3 and is applied by the
server. It is not part of the AI response.
