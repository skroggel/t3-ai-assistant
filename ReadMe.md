# AI Assistant Extension (TYPO3)
AI Assistant is a flexible TYPO3 extension for building AI-powered assistants based on Retrieval-Augmented Generation (RAG), configurable processing pipelines, and pluggable AI and vector store integrations.

The extension allows editors, integrators and developers to create specialized assistants that answer questions based on TYPO3 content, files, Shopware data, JSON sources or custom data providers. Instead of relying on a fixed workflow, AI Assistant uses configurable pipelines consisting of multiple processing steps such as query optimization, retrieval, context optimization, answer generation and quality assurance.

Built for enterprise knowledge assistants and AI search solutions.

Features
* Retrieval-Augmented Generation (RAG) for TYPO3
* Configurable assistant profiles
* Fully configurable AI processing pipelines
* Multiple pipeline stages
* Support for multiple AI providers
* Support for multiple vector databases
* Pluggable architecture based on registries
* TYPO3 page indexing
* File indexing
* Shopware indexing
* Metadata-aware retrieval
* Source attribution and citation support
* Chat memory and conversation history
* Pipeline tracing and debugging
* Backend monitoring and log analysis
* Collection-based knowledge separation
* Multi-assistant setups
* Custom prompt engineering per assistant and pipeline step
* Extensible retrieval and ranking strategies
* Extensible connectors for external systems
* Extensible custom indexers
* Extensible file content adapters

## Requirements
- TYPO3 installation with this extension installed and activated
- OpenAI API key stored in the TYPO3 backend module
- Reachable Qdrant instance with correct `qdrant.host`

## Working with DDEV
Install a Qdrant-container with:
```
ddev get netz98/ddev-qdrant
```
then:
```
ddev restart
```

## Documentation
see Documentation-folder!

## Local Qdrant (DDEV)
Install the Qdrant add-on:

```bash
ddev get netz98/ddev-qdrant
ddev restart
```

Access Qdrant:

```text
https://<yourname>.ddev.site:6333
```

Dashboard:

```text
https://<yourname>.ddev.site:6333/dashboard
```

## Enable API Key (Local Test)
To test `qdrant.apiKey`, enable auth locally by adding `QDRANT__SERVICE__API_KEY` to `.ddev/docker-compose.qdrant.yaml`:

```yaml
environment:
  - VIRTUAL_HOST=$DDEV_HOSTNAME
  - HTTPS_EXPOSE=6334:6334
  - HTTPS_EXPOSE=6333:6333
  - QDRANT__SERVICE__API_KEY=mein_geheimer_key
```

Then restart:

```bash
ddev restart
```

## Generate the documentation as HTML
Execute in the extension-root:
```
docker run --rm --pull always -v "$(pwd)":/project -it ghcr.io/typo3-documentation/render-guides:latest --config=Documentation
```

## Troubleshooting
**Qdrant returns 401**
Check that the `qdrant.apiKey` Registry value matches the key configured in `.ddev/docker-compose.qdrant.yaml` and that Qdrant was restarted.

**OpenAI key missing**
The extension requires `openai.apiKey` to be present in the Registry. Set it via the backend module and save.

**Qdrant host not respected**
If `qdrant.host` is set in the Registry, it overrides TypoScript. Clear the Registry value to fall back to `plugin.tx_aiassistant_chat.settings.qdrant.host`.
