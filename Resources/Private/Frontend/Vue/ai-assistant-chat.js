/**
 * Registers the framework-independent Vue chat as a browser custom element.
 *
 * @module ai-assistant-chat
 */
import { defineCustomElement } from 'vue';
import AiAssistantChat from './components/AiAssistantChat.ce.vue';


// Keep the component's DOM themeable by host applications. The component
// remains isolated by its Vue API, while project CSS can override its classes.
customElements.define(
    'ai-assistant-chat',
    defineCustomElement(AiAssistantChat, { shadowRoot: false }),
);
