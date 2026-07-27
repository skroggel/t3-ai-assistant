..  _source-attribution:

Source attribution
==================

Source attribution helps users understand where an answer came from.

Recommended source fields
-------------------------

..  code-block:: text

    title,url,source_type,source_identifier

For TYPO3 pages, include page-specific identifiers if they are available. For
files, include filename and path or public URL. For external systems, include a
stable record identifier and display label.

Prompt wording
--------------

If the assistant should answer source questions such as "Where does this come
from?", ensure the retrieved context contains metadata fields and the answer
prompt allows the assistant to mention them.

