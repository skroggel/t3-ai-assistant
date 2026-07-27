..  _metadata-handling:

Metadata handling
=================

Metadata travels from indexable documents into vector payloads, retrieved
documents, prompt context and frontend sources.

Important metadata fields
-------------------------

source_type
    Type of source, for example page, file or product.

source_identifier
    Stable identifier of the source object.

title
    Human-readable source title.

url
    Link to the source if available.

changed_at
    Timestamp or date used for freshness-sensitive content.

Domain-specific metadata
    Examples are product number, category, department, contact name or page uid.

Prompt metadata
---------------

Only selected metadata fields are written into the prompt. Configure
``prompt_metadata_fields`` per step to avoid bloating prompts.

Frontend metadata
-----------------

Only selected metadata fields are exposed as frontend sources. Configure
``frontend_source_fields`` per step so users see useful, safe source data.

