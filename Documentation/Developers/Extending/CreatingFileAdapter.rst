..  _creating-file-adapter:

Creating a file adapter
=======================

File adapters extract indexable text from files. They are resolved through
``AdapterRegistry`` by path, extension or support checks and are used by file
indexers.

The public extension ships adapters for plain text-like files and JSON/JSONL.
Specialized adapters, for example the PDF adapter, are documented in the
premium extension when they are installed there.

Responsibilities
----------------

* expose a stable adapter identifier;
* list supported file extensions;
* decide whether a file path is supported;
* extract UTF-8 text;
* add parser warnings or metadata where useful;
* avoid throwing for recoverable parser issues when metadata can describe the
  problem.

Identifier
----------

..  code-block:: php

    public function getIdentifier(): string
    {
        return 'vendor.markdown';
    }

Supported extensions
--------------------

Adapters expose the file extensions they can process. Return the extension
without leading dot.

..  code-block:: php

    public function getSupportedExtensions(): array
    {
        return ['md', 'markdown'];
    }

Support checks
--------------

A typical implementation uses the file extension or MIME type.

..  code-block:: php

    public function supports(string $path): bool
    {
        return in_array(
            strtolower((string)pathinfo($path, PATHINFO_EXTENSION)),
            $this->getSupportedExtensions(),
            true
        );
    }

Extraction
----------

The adapter receives the local file path and a metadata object. It returns the
indexable text and may enrich metadata with parser details.

..  code-block:: php

    use Madj2k\AiCore\DTO\DocumentMetadata;

    public function extract(string $path, DocumentMetadata $metadata): string
    {
        $metadata->addAdditional('parser', $this->getIdentifier());

        return trim((string)file_get_contents($path));
    }

Service registration
--------------------

..  code-block:: yaml

    services:
      Vendor\Extension\Indexing\Adapter\MarkdownAdapter:
        tags:
          - name: 'aiassistant.indexing.adapter'

Multi-document adapters
-----------------------

When one source file contains multiple logical records, implement
``Madj2k\AiAssistant\Indexing\Adapter\MultiDocumentAdapterInterface`` in
addition to ``AdapterInterface``. The JSON/JSONL adapter uses this pattern to
turn one file into multiple ``IndexableDocument`` instances.
