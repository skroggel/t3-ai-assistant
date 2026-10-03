..  _vue-frontend:

Vue frontend integration
========================

The AI Assistant frontend is implemented as a Vue component and can be used in
two ways:

* as a regular Vue component in an existing Vue application;
* as a framework-independent custom element in a project without Vue.

The production bundle is compiled with Vite and is included in the extension.
Projects using the custom element therefore do not need to install Vue or run a
frontend build themselves.

Common data
-----------

The component expects the following core values:

``endpoint``
    SSE endpoint of ``ChatController->streamAction``.

``assistant-profile``
    Assistant profile UID.

``chat-identifier``
    Stable identifier for the current conversation.

``start-timestamp``
    Timestamp used to reset the conversation scope.

``settings-json``
    Runtime settings sent back to TYPO3 with every chat request.

``chat-options-json``
    Normalized frontend options. This includes language, accessibility and
    sanitizing configuration.

``labels-json``
    Translated labels for the chat UI.

The TYPO3 Fluid template creates these values automatically. Custom integrations
must provide them explicitly.

Regular Vue component
---------------------

Import the component into an existing Vue application and register it like any
other component:

..  code-block:: js

    import { createApp } from 'vue';
    import AiAssistantChat from '/path/to/AiAssistantChat.ce.vue';
    import App from './App.vue';

    const app = createApp(App);
    app.component('AiAssistantChat', AiAssistantChat);
    app.mount('#app');

Use it in a Vue template:

..  code-block:: vue

    <AiAssistantChat
        :endpoint="endpoint"
        :assistant-profile="assistantProfile"
        :chat-identifier="chatIdentifier"
        :start-timestamp="startTimestamp"
        :settings-json="settingsJson"
        :chat-options-json="chatOptionsJson"
        :labels-json="labelsJson"
        :require-consent="requireConsent"
        :show-language-selector="showLanguageSelector"
    />

Custom elements in a Vue application
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The framework-independent frontend and the premium integration register their
components as native custom elements. When these elements are used directly in
a Vue template, configure Vue's compiler so it does not try to resolve them as
Vue components:

..  code-block:: js

    // vue.config.js
    module.exports = {
        chainWebpack: (config) => {
            config.module
                .rule('vue')
                .use('vue-loader')
                .tap((options) => ({
                    ...options,
                    compilerOptions: {
                        ...(options.compilerOptions || {}),
                        isCustomElement: (tag) => tag.startsWith('ai-assistant-'),
                    },
                }));
        },
    };

For Vite, use the equivalent Vue plugin configuration:

..  code-block:: js

    // vite.config.js
    import { defineConfig } from 'vite';
    import vue from '@vitejs/plugin-vue';

    export default defineConfig({
        plugins: [
            vue({
                template: {
                    compilerOptions: {
                        isCustomElement: (tag) => tag.startsWith('ai-assistant-'),
                    },
                },
            }),
        ],
    });

This prevents warnings such as ``Failed to resolve component:
ai-assistant-search-summary``. The custom-element bundles still need to be
loaded before the elements are used.

The shared transport must be available as ``window.AiAssistantTransport``. A
Vue application can load the extension's browser-compatible transport before
mounting the application:

..  code-block:: html

    <script src="/typo3conf/ext/ai_assistant/Resources/Public/JavaScript/ai-assistant-transport.js"></script>

When the component is imported directly, ``marked`` and ``DOMPurify`` are
resolved by the consuming Vue build through the component's package
dependencies.

Framework-independent custom element
-------------------------------------

For projects without Vue, load the compiled transport and chat bundle:

..  code-block:: html

    <script src="/typo3conf/ext/ai_assistant/Resources/Public/JavaScript/ai-assistant-transport.js"></script>
    <script src="/typo3conf/ext/ai_assistant/Resources/Public/JavaScript/ai-assistant-chat.js"></script>

Then render the custom element:

..  code-block:: html

    <ai-assistant-chat
        endpoint="/index.php?type=1790831370"
        assistant-profile="1000"
        chat-identifier="conversation-abc123"
        start-timestamp="1790843556"
        settings-json="{&quot;assistantProfile&quot;:&quot;1000&quot;}"
        chat-options-json="{&quot;language&quot;:{&quot;responseLanguage&quot;:&quot;German&quot;},&quot;accessibility&quot;:{&quot;plainLanguage&quot;:false}}"
        labels-json="{&quot;chatLabel&quot;:&quot;Chat history&quot;,&quot;submitLabel&quot;:&quot;Send&quot;}"
        require-consent="0"
        show-language-selector="1"
    ></ai-assistant-chat>

The custom element uses the Light DOM. Its classes are namespaced below
``.aiassistant-chat`` and can be overridden by the host project.

Boolean attributes
~~~~~~~~~~~~~~~~~~

Boolean values may be passed as real Vue booleans or as HTML/Fluid values. The
component normalizes all of the following forms:

..  code-block:: text

    true, false, 1, 0, "true", "false", "1", "0"

HTML attributes should use ``0`` or ``1`` when generated by Fluid.
