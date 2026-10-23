Chunking strategies
===================

The indexer field ``chunking_strategy`` is stored as ``fixed`` or ``atomic``.
Existing empty values resolve to ``fixed``.

``fixed``
    Uses ``chunk_size``, ``chunk_overlap``, ``min_chunk_chars`` and
    ``max_chunks`` as before.

``atomic``
    Keeps every ``IndexableDocument`` as exactly one chunk. ``chunk_size`` is
    a hard maximum; an oversized document fails before embedding and storage.
    The text is not truncated or split, and overlap, minimum length and the
    maximum chunk count are ignored. Empty documents are still skipped.

For example, a Markdown file is split by the Markdown adapter into sections::

    # Handbook

    Introduction.

    ## Installation

    Follow these steps.

    ### Requirements

    PHP 8.4 is required.

With ``fixed``, each section is chunked independently by character limits.
With ``atomic``, the introduction and each ``##`` section are each stored as
one complete chunk, or fail if they exceed ``chunk_size``. Fenced code blocks
are kept in their section even when they contain ``##`` text.

After changing the strategy or an adapter, perform a complete re-index and
disable unchanged-source skipping for that run. This is required because the
source content may be unchanged while its vector boundaries have changed.
Atomic chunking guarantees complete chunks in the index only; later retrieval
and context limits remain unchanged.
