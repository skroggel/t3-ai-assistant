..  _retrieval-selection:

Retrieval selection
===================

The ``intent_selector`` step can control three independent areas:

* ``retriever``: select which configured retrievers execute;
* ``presentation_rules``: select the presentation rules used for the answer;
* ``retriever_payload``: provide values for a dynamic vector-payload filter.

The areas are enabled through a multi-select field in the intent-selector
configuration. Areas that are not enabled retain their normal pipeline default.
Empty or missing legacy configuration keeps retriever and presentation-rule
selection enabled and leaves payload filtering disabled.

Each retriever needs a stable ``retrieval_identifier``. The identifier is used
for routing and is separate from the human-readable step title.

The selector returns a JSON object containing the enabled areas. The complete
contract is:

..  code-block:: json

    {
        "retrieval": {
            "mode": "selected",
            "targets": ["products"]
        },
        "presentationRules": {
            "mode": "selected",
            "rules": ["compact_answer"]
        },
        "retrieverPayload": {
            "values": [12, 15]
        },
        "metadata": {
            "primary_intent": "product_information"
        }
    }

The retrieval and presentation-rule areas support ``all``, ``selected``,
``all_except`` and ``none``. Only identifiers configured on retriever steps or
presentation-rule records may be returned. The payload area uses the plural
``values`` key and contains the values selected by the AI.

Selector configuration
----------------------

The selector step provides selection instructions and an optional metadata
schema in JSON. When ``retriever_payload`` is enabled, TYPO3 additionally
configures:

Payload field
    Dot-notated indexed payload path, for example
    ``meta.additional.category_ids``. This can address any indexed metadata
    field, including ``category_ids``, ``categories`` or project-specific
    additional metadata.

Payload retrievers
    A multi-select list of the retriever steps in the current assistant
    pipeline. Empty selection applies the dynamic filter to all retrievers
    allowed by the retrieval plan. A selected retriever that is not in this
    list runs without the dynamic filter.

Payload conjunction
    ``and`` requires every returned value to match; ``or`` requires at least
    one returned value to match. This is technical configuration and is not
    decided by the AI.

The AI only returns payload values. It does not return the payload field path
or the ``and``/``or`` operation:

..  code-block:: json

    {
        "retrieverPayload": {
            "values": [12, 15]
        }
    }

If the AI returns no usable values, no dynamic payload filter is added. Static
filters configured on a retriever remain active and are combined with a
dynamic filter using ``AND``.

The result is available to later context optimizers, answer generators and
quality gates. Disabled output areas are not overwritten by the selector.
