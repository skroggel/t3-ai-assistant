..  _retrieval-selection:

Retrieval selection
===================

The optional ``retrieval_selector`` step allows the AI to choose which
configured retrievers execute for a request. Without a selector result, all
retrievers remain enabled.

Each retriever needs a stable ``retrieval_identifier``. The identifier is used
for routing and is separate from the human-readable step title.

The selector returns a fixed JSON envelope:

..  code-block:: json

    {
        "mode": "selected",
        "targets": ["products"],
        "metadata": {
            "primary_intent": "product_information"
        }
    }

The available modes are ``all``, ``selected``, ``all_except`` and ``none``.
Only identifiers configured on retriever steps may be returned.

Selector configuration
----------------------

The selector step provides selection instructions and an optional metadata
schema in JSON. The result is available to later context optimizers, answer
generators and quality gates.
