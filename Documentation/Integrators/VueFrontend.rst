..  _vue-frontend:

Vue Frontend Integration
========================

This guide describes three frontend cases. Choose exactly one case for each
instance.

Beginner's overview
-------------------

There are three different things involved:

* **TYPO3/Fluid** renders the initial HTML and provides secure configuration
  values such as endpoints, tokens, labels and JSON settings;
* **the existing Vue application** owns the browser-side component tree;
* **the Assistant composable** owns requests, streaming, messages, consent and
  language handling.

In Integration mode, TYPO3 does not render the Chat itself. It renders a plain
``div`` containing ``data-*`` attributes. The existing Vue application reads
these attributes and renders the Vue component in its own template.

There is no additional Vue application for each Chat or Summary. There is one
Vue root for the site, normally created once in ``main.js`` with
``createApp(App).mount(...)``.

JavaScript imports are not Fluid paths
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Paths beginning with ``EXT:`` are TYPO3/Fluid paths. They are valid in Fluid
templates and documentation, but they are **not** valid JavaScript imports.

The site bundler needs an alias. Add this ``resolve.alias`` section to the
existing ``configureWebpack`` object in ``vue.config.js``:

..  code-block:: js

    const path = require('path');

    module.exports = {
        configureWebpack: {
            resolve: {
                alias: {
                    '@ai-assistant': path.resolve(
                        __dirname,
                        '../../../../../vendor/madj2k/t3-ai-assistant/Resources/Private/Frontend/Vue/Src',
                    ),
                    '@ai-assistant-premium': path.resolve(
                        __dirname,
                        '../../../../../vendor/madj2k/t3-ai-assistant-premium/Resources/Private/Frontend/Vue/Src',
                    ),
                },
            },
        },
    };

    // If module.exports/configureWebpack already exists, merge only the
    // `resolve.alias` block into that existing configuration.

The relative paths above are for a site package whose Vue project is located at
``packages/mv_template/Resources/Private/build``. Other projects must adjust
the filesystem path to their Composer ``vendor`` directory.

After adding the aliases, imports are simple and stable:

..  code-block:: js

    import {
        ChatInput,
        ChatMessageList,
        ChatStatus,
        LanguageSelector,
        chatProps,
        useAiAssistantChat,
    } from '@ai-assistant/index.js';

    import {
        AiAssistantSearchSummary,
        summaryProps,
    } from '@ai-assistant-premium/index.js';

Do not write imports such as ``EXT:...`` or ``PATH_TO_...`` in JavaScript.

The extension is ``ai_assistant``. ``ai-core`` is a separate underlying library
and is not the frontend integration package.

Case 1: Standalone Chat
-----------------------

Use this when no existing Vue application should own the Chat.

1. Set the site setting:

   ..  code-block:: yaml

       aiAssistant:
         useVueIntegration: false

2. Add the **AI Assistant Chat** content element.
3. Do not import Vue source files and do not add a Vue mount.
4. Optionally enable ``includeDefaultStyles`` in the Chat FlexForm.

TYPO3 uses:

``EXT:ai_assistant/Resources/Private/Frontend/Vue/Standalone/Templates/Index/Index.html``

The template loads the transport and Custom Element bundles and renders the
complete ``<ai-assistant-chat>`` element.

Case 2: Chat in an existing Vue App
------------------------------------

Use this when an existing Vue application should render the Chat or a custom
Chat layout.

1. Set ``aiAssistant.useVueIntegration: true``.
2. Add the AI Assistant Chat content element.
3. Set the Chat FlexForm field ``settings.elementId`` to a stable ID, for
   example ``example-ai-chat``. Keep the same value in all translations.
4. TYPO3 selects:

   ``EXT:ai_assistant/Resources/Private/Frontend/Vue/Integration/Templates/Index/Index.html``

5. The template creates a data bridge with ``data-ai-assistant-chat-mount`` and
   the server-generated Chat props. The bridge remains in the DOM; it is not a
   second Vue mount.
6. Add the Chat component to an existing Vue component template. Read the
   bridge by its configured ID and expose its attributes as reactive props. Then
   render either the standard ``AiAssistantChat`` or a site-owned
   ``CustomerChat`` with ``v-if`` and ``v-bind``. The complete code pattern is
   shown in the **Chat component in an existing Vue app** section below.
7. Do not load ``ai-assistant-chat.js`` and do not call ``createApp`` for the
   bridge element.

The shared Chat API is maintained in the source:

* ``Resources/Private/Frontend/Vue/Src/chatProps.js``;
* ``Resources/Private/Frontend/Vue/Src/composables/useAiAssistantChat.js``;
* ``Resources/Private/Frontend/Vue/Src/components/AiAssistantChat.vue``;
* ``Resources/Private/Frontend/Vue/Src/components/ChatInput.vue``;
* ``Resources/Private/Frontend/Vue/Src/components/ChatMessageList.vue``;
* ``Resources/Private/Frontend/Vue/Src/components/ChatStatus.vue``;
* ``Resources/Private/Frontend/Vue/Src/components/LanguageSelector.vue``.

### Create the custom Chat component

If the standard markup is sufficient, render the existing
``AiAssistantChat.vue`` component. If the markup must be customized, create a
site component such as ``CustomerChat.vue``.

The standard implementation to use as the structural reference is:

``EXT:ai_assistant/Resources/Private/Frontend/Vue/Src/components/AiAssistantChat.vue``

Use its template structure and the reusable child components as a reference for
``CustomerChat.vue``. The site component may change the markup and styles, but
must keep the shared prop and composable contract.

The custom component must follow this structure:

1. Import ``chatProps`` and ``useAiAssistantChat`` from the configured
   ``@ai-assistant`` alias:
   ..  code-block:: js

       import { chatProps, useAiAssistantChat } from '@ai-assistant/index.js';

2. Define the shared props and error event:

   ..  code-block:: js

       const props = defineProps(chatProps);
       const emitEvent = defineEmits(['error']);

3. Call the composable and expose its state to the template:

   ..  code-block:: js

       const {
           messages,
           loading,
           consentGiven,
           requireConsent,
           showLanguageSelector,
           effectiveResponseLanguage,
           effectiveLanguageCode,
           labels,
           numberOfLoadingDots,
           loadingHtml,
           loadingMessages,
           loadingMessagesTimeout,
           statusMessage,
           send,
           acceptConsent,
           confirmLanguage,
           handleComponentClick,
       } = useAiAssistantChat(props, { emit: emitEvent });

4. Render the returned values with the reusable child components:

   ..  code-block:: vue

       <section class="aiassistant-chat" @click="handleComponentClick">
           <ChatMessageList
               :messages="messages"
               :user-label="labels.userLabel"
               :assistant-name="labels.assistantName"
               :number-of-loading-dots="numberOfLoadingDots"
               :loading-html="loadingHtml"
               :loading-messages="loadingMessages"
               :loading-messages-timeout="loadingMessagesTimeout"
           />
           <ChatStatus :status="statusMessage" />
           <ChatInput
               :placeholder="labels.inputPlaceholder"
               :submit-label="labels.submitLabel"
               :disabled="loading"
               @submit="send"
           />
       </section>

5. Add consent and language markup when those features are enabled. Use
   ``AiAssistantChat.vue`` and ``LanguageSelector.vue`` as the references for
   the required bindings and focus callbacks.

Do not copy the request, streaming, JSON, consent or message-state
implementation. Only the presentation belongs in ``CustomerChat.vue``.

Case 3: Premium Summary in an Existing Vue App
-----------------------------------------------

Use this when the Premium Summary and its nested Chat must use custom Vue
markup.

1. Keep ``aiAssistant.useVueIntegration: true``.
2. Add the Premium Search Summary content element.
3. Set its FlexForm field ``settings.elementId`` to a stable ID, for example
   ``example-ai-summary``. Keep the same value in all translations.
4. TYPO3 selects:

   ``EXT:ai_assistant_premium/Resources/Private/Frontend/Vue/Integration/Templates/Search/SearchSummary.html``

5. The template creates a Summary data bridge with
   ``data-ai-assistant-summary-mount``. It does not render
   ``<ai-assistant-search-summary>``.
6. Keep the Premium payload builder/search integration available. There are two
   supported ways to do this:

   **Existing Premium search elements**
       Keep the Premium bundle and the server-rendered
       ``<ai-assistant-search-payload-builder>``. The bundle registers the
       element and exposes:

       ..  code-block:: js

           window.AiAssistantPremiumSearchPayloadBuilder.build;

       ``SearchApp`` can call ``build({ rows, fields, total })`` to normalize
       the current result rows.

   **Site-owned Vue search integration**
       Import ``AiAssistantSearchPayloadBuilder`` from
       ``@ai-assistant-premium/index.js`` and render it in the existing Vue
       application. It is a hidden helper component that exposes the same
       ``window.AiAssistantPremiumSearchPayloadBuilder`` API. The search app
       can then call ``build`` directly and pass the result as
       ``capturedResults``.

   The direct Vue Summary does not require ``publish()``. ``publish()`` is only
   needed when another Premium consumer still listens for the global payload
   event.
7. Create ``SearchAiSummaryChat.vue`` using the Case 2 custom Chat procedure.
   Use the Core source components as references; do not duplicate the request
   implementation.
8. Create ``SearchAiSummary.vue`` as a thin wrapper:

   ..  code-block:: vue

       <template>
           <AiAssistantSearchSummary
               v-bind="props"
               :chat-component="SearchAiSummaryChat"
           />
       </template>

       <script setup>
    import { summaryProps } from '@ai-assistant-premium/summaryProps.js';
    import AiAssistantSearchSummary from '@ai-assistant-premium/components/AiAssistantSearchSummary.vue';
       import SearchAiSummaryChat from './SearchAiSummaryChat.vue';

       const props = defineProps(summaryProps);
       </script>

9. Render ``SearchAiSummary`` directly inside the existing SearchApp template:

   ..  code-block:: vue

       <SearchAiSummary
           v-if="summaryProps"
           v-bind="summaryProps"
       />

10. Read the Summary bridge by its configured ID:

    ..  code-block:: js

        const bridge = document.getElementById('example-ai-summary');

11. Build ``capturedResults`` with the Premium payload builder before setting
    the Summary props. In ``SearchApp.vue``, use this complete sequence:

    ..  code-block:: js

        const bridge = document.getElementById('example-ai-summary');
        const payloadBuilder = await getSearchPayloadBuilder();

        if (!bridge || !payloadBuilder?.build) {
            return;
        }

        const payloadOptions = {
            rows: resultRows.value,
            fields: {
                id: 'number',
                title: 'title_text',
                text: 'content_text',
                url: 'url',
                type: 'type',
                pageId: 'uid',
            },
            total: filtersCount.value.all || 0,
        };
        const capturedResults = payloadBuilder.build(payloadOptions);

        summaryProps.value = {
            sseEndpoint: bridge.dataset.sseEndpoint || '',
            jsonEndpoint: bridge.dataset.jsonEndpoint || '',
             // The bridge contains the initially cached query. SearchApp's
             // reactive value is authoritative after a client-side search.
             query: searchWord.value,
            assistantProfile: bridge.dataset.assistantProfile || '',
            chatIdentifier: bridge.dataset.chatIdentifier || '',
            requestToken: bridge.dataset.requestToken || '',
            integration: bridge.dataset.integration || '',
            startTimestamp: bridge.dataset.startTimestamp || '',
            settingsJson: bridge.dataset.settingsJson || '{}',
            chatOptionsJson: bridge.dataset.chatOptionsJson || '{}',
            labelsJson: bridge.dataset.labelsJson || '{}',
            uiComponentsJson: bridge.dataset.uiComponentsJson || '[]',
            errorHandling: bridge.dataset.errorHandling || 'default',
            streamResponse: bridge.dataset.streamResponse || '1',
            showWithoutResults: bridge.dataset.showWithoutResults || '0',
            capturedResults,
         };

The ``data-query`` value is the query that was present when TYPO3 rendered the
page. It must not be reused blindly after a client-side search. Always use the
current ``searchWord.value`` when preparing Summary props, otherwise the second
Summary request can use the previous search query.

12. Let Vue render the Summary. In this direct Vue integration, no Premium
    payload event is required because ``capturedResults`` is passed directly.
    Only call ``payloadBuilder.publish(...)`` if another Premium consumer
    explicitly needs the global event.

13. Reset the Summary before starting another search on the first result page:

    ..  code-block:: js

        if (currentPage.value === 1) {
            summaryVisible.value = false;
            summaryProps.value = null;
        }

    This creates a fresh Summary/Chat instance for the new query. Otherwise the
    previous Chat can keep ``autoQueryStarted`` and its old messages, so the
    second search shows neither fresh loading messages nor a new answer.

The component relationship is:

..  code-block:: text

    SearchApp
    `-- SearchAiSummary
        `-- AiAssistantSearchSummary
            `-- SearchAiSummaryChat
                `-- useAiAssistantChat

Shared Vue Bundler Configuration
--------------------------------

Apply this once to the existing site Vue application before using Case 2 or
Case 3.

### Runtime Custom Elements

The root application compiles TYPO3-rendered HTML at runtime. Tell Vue that
``ai-assistant-*`` tags are native Custom Elements:

..  code-block:: js

    const app = createApp(App);
    app.config.compilerOptions.isCustomElement = (tag) => (
        tag.startsWith('ai-assistant-')
    );
    app.mount('#website');

### Vue CLI and Vue Runtime

Ensure imported Assistant source uses the site's Vue runtime and define Vue's
compile-time flags in ``vue.config.js``:

..  code-block:: js

    const path = require('path');
    const webpack = require('webpack');

    module.exports = {
        configureWebpack: {
            resolve: {
                alias: {
                    vue$: path.resolve(
                        __dirname,
                        'node_modules/vue/dist/vue.esm-bundler.js',
                    ),
                },
            },
            plugins: [
                new webpack.DefinePlugin({
                    __VUE_OPTIONS_API__: true,
                    __VUE_PROD_DEVTOOLS__: false,
                    __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false,
                }),
            ],
        },
    };

Two Vue runtimes can execute the request while the visible refs and lifecycle
state do not update. The alias prevents the Assistant package's local Vue
installation from being bundled separately.

Default Styles
--------------

Default styles are maintained centrally in:

``@ai-assistant/styles/chat-default.scss``

The file imports the component-specific partials:

* ``_chat.scss``;
* ``_chat-input.scss``;
* ``_chat-message-list.scss``;
* ``_language-selector.scss``.

The Standalone Vite entry imports ``chat-default.scss`` and produces the Public
CSS asset. Integration component imports do not load this stylesheet
automatically. Load it only when default styling is wanted; otherwise provide
site-owned styles.

Payload and Lifecycle Order
---------------------------

For Case 3, the correct order is:

1. SearchApp receives result rows.
2. Premium payload builder creates ``capturedResults``.
3. SearchApp sets Summary props, including ``capturedResults``.
4. Vue renders ``SearchAiSummary``.
5. ``AiAssistantSearchSummary`` passes ``chatComponent`` to the custom Chat.
6. The custom Chat receives ``autoQuery``.
7. ``useAiAssistantChat`` sends the POST/SSE request.
8. The assistant message changes from ``typing: true`` to ``typing: false``.

Do not use a changing ``:key`` on the running Chat. A remount destroys the
reactive message list and loading state. Do not process the same payload through
both direct ``capturedResults`` and the global event in the same Summary.

Browser Verification
--------------------

Check these points in browser developer tools:

1. The configured bridge ID exists in the DOM.
2. The bridge contains endpoint, settings, labels and Chat options.
3. ``capturedResults.results`` is not empty.
4. ``autoQuery`` is not empty in the custom Chat.
5. The Chat message list receives a message with ``typing: true``.
6. The POST/SSE request completes.
7. The same Chat instance receives rendered HTML and changes to
   ``typing: false``.

If the backend request succeeds but the DOM does not update, check component
remounts, duplicate payload processing and Vue-runtime resolution before adding
another request path.
