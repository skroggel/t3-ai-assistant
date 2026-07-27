..  _creating-ai-connector:

Creating an AI connector
========================

AI connectors integrate language model and embedding providers into AI
Assistant. They are resolved through the AI connector registry by their
identifier and are configured through AI connection records.

Responsibilities
----------------

* expose a stable connector identifier;
* accept an AI connection record;
* create synchronous chat completions from ``AiRequest``;
* stream chat completions to a callback;
* create embeddings from ``EmbeddingRequest``;
* create batch embeddings for multiple ``EmbeddingRequest`` objects;
* map provider responses to ``AiResponse`` and ``EmbeddingResponse``;
* preserve usage information where available;
* keep provider-specific authentication and request mapping inside the
  connector.

Identifier
----------

The identifier is stored in the AI connection record and is used by
``AiConnectorRegistry`` to resolve the service.

..  code-block:: php

    public function getIdentifier(): string
    {
        return 'vendor.my_ai';
    }

Use a namespaced identifier to avoid collisions with other extensions.

Skeleton
--------

..  code-block:: php

    <?php
    declare(strict_types=1);

    namespace Vendor\Extension\Connection\Ai;

    use Madj2k\AiAssistant\Connection\Ai\AiConnectorInterface;
    use Madj2k\AiAssistant\Connection\Ai\DTO\AiRequest;
    use Madj2k\AiAssistant\Connection\Ai\DTO\AiResponse;
    use Madj2k\AiAssistant\Connection\Ai\DTO\EmbeddingRequest;
    use Madj2k\AiAssistant\Connection\Ai\DTO\EmbeddingResponse;
    use Madj2k\AiAssistant\Connection\Domain\Model\AiConnection;

    final class MyAiConnector implements AiConnectorInterface
    {
        public function getIdentifier(): string
        {
            return 'vendor.my_ai';
        }

        public function chat(AiConnection $connection, AiRequest $request): AiResponse
        {
            // Build provider request from the connection and DTO.
            // Return a normalized AiResponse.
        }

        public function streamChat(AiConnection $connection, AiRequest $request, callable $onData): void
        {
            // Build a streaming provider request and call $onData($chunk)
            // for every emitted text chunk.
        }

        public function embed(AiConnection $connection, EmbeddingRequest $request): EmbeddingResponse
        {
            // Build provider embedding request.
            // Return one normalized embedding response.
        }

        public function embedBatch(AiConnection $connection, array $requests): array
        {
            // Return one EmbeddingResponse per EmbeddingRequest.
        }
    }

Service registration
--------------------

Register the connector as a service and tag it for the registry.

..  code-block:: yaml

    services:
      Vendor\Extension\Connection\Ai\MyAiConnector:
        tags:
          - name: 'aiassistant.connection.ai_connector'

Configuration values
--------------------

Provider credentials, model names, temperature values and additional provider
options should come from the AI connection record. Avoid hard-coded credentials
or project-specific defaults in the connector class.
