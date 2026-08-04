# AI Assistant Extension for TYPO3

AI Assistant provides configurable Retrieval-Augmented Generation (RAG) assistants for TYPO3.
Editors can combine TYPO3 pages, files and custom data sources with configurable chat pipelines
and source attribution. Additional source types can be supplied by extensions.

The extension provides the TYPO3 integration: database records, backend configuration, source
discovery, persistence, commands, session handling, diagnostics and frontend output. The
framework-independent runtime, pipeline contracts, provider connectors and indexing primitives
live in `madj2k/ai-core`, which Composer installs as a dependency.

## Features

- Assistant profiles and configurable processing pipelines
- AI and vector-store connection records
- TYPO3 page and file indexing, including structured file adapters
- Metadata-aware retrieval and source links
- Chat memory, pipeline traces and backend diagnostics
- Extension points for processors, connectors, indexers and file adapters

## Requirements

- TYPO3 13.4 or 14.3
- PHP 8.2 or newer
- An AI provider for chat and embeddings
- A reachable vector store such as Qdrant

## Installation

```bash
composer require madj2k/ai-assistant
```

After installation:

1. Apply the TYPO3 database schema updates.
2. Create and test an AI connection and a vector-store connection in the backend module.
3. Create an indexer and index its sources.
4. Create or import an assistant profile using the same vector-store connection and collection.
5. Add the chat plugin to a page.

See the [Documentation](Documentation/Index.rst) for configuration, commands and extension points.

## Local Qdrant with DDEV

```bash
ddev get netz98/ddev-qdrant
ddev restart
```

The dashboard is normally available at:

```text
https://<project-name>.ddev.site:6333/dashboard
```

To test API-key authentication locally, add `QDRANT__SERVICE__API_KEY` to the Qdrant service
environment and enter the same key in the TYPO3 vector-store connection record.

## Diagnostics

Set `Pipeline log mode` to `verbose` under **AI Assistant > Configuration**, then inspect a chat
request under **AI Assistant > Diagnostics**. Optional TYPO3 PSR log output is written to
`var/log/tx_aiassistant.log`; general TYPO3 errors are available in `var/log/typo3_*.log`.

## Render the documentation

Run from the extension root:

```bash
docker run --rm --pull always -v "$(pwd)":/project -it ghcr.io/typo3-documentation/render-guides:latest --config=Documentation
```
