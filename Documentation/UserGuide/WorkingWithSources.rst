..  _working-with-sources:

Working with sources
====================

Sources are the content that can be indexed and searched by assistants.

TYPO3 pages
-----------

Page indexing reads visible pages from configured root pages. An empty root page
configuration can be used to index all visible pages, depending on the project
configuration. A root page usually includes the root page itself and visible
subpages.

Files
-----

File indexing reads files from configured import paths. In TYPO3 projects this
is commonly a FAL identifier such as:

..  code-block:: text

    1:/user_uploads

The storage id and path are resolved through TYPO3 FAL. File adapters extract
text from supported formats and can add metadata such as filename, extension,
size, storage id and public URL.

External sources
----------------

External systems can be indexed through connector-based indexers. A connector
fetches data from the external system, the indexer transforms it into
indexable documents and the vector store receives chunked embeddings.

JSON sources
------------

..  note:: TODO

    JSON indexing configuration is planned. The intended configuration includes
    a stable source identifier field, one or more text fields, metadata fields
    and mapping rules for payload generation.

Metadata
--------

Metadata should describe the source and help users verify answers. Useful
metadata includes:

* title;
* URL;
* source type;
* source identifier;
* language;
* changed timestamp;
* page uid or file path;
* domain-specific fields such as product number, category or department.

