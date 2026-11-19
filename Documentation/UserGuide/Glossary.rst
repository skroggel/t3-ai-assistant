..  _user-guide-glossary:

Glossary
========

Assign one glossary to an assistant profile. The glossary is maintained as one
complete text list per language, not as one TYPO3 record per term. Create the
translations with TYPO3's standard localization workflow.

Each localized record represents the target response language and contains the
source-to-target mappings for that language. A simple text format is sufficient,
for example:

.. code-block:: text

    source language: en
    source term: natural flavour
    target term: natürliches Aroma

    source language: en
    source term: clean label
    target term: Clean-Label

The selected glossary translation follows the effective chat response language:
the explicit language code is matched to the TYPO3 site language and falls back
to the default-language glossary when no translation exists.

The glossary is included in the Answer Generator and Quality Gate system prompt.
It is a terminology mapping, not retrieval context, and is therefore not
limited by ``max_context_characters``.
