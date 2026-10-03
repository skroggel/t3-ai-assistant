/**
 * Registers the framework-independent Vue chat as a browser custom element.
 *
 * @module ai-assistant-chat
 */
import { defineCustomElement } from 'vue';
import AiAssistantChat from './components/AiAssistantChat.ce.vue';

// Vue only injects SFC styles automatically into a shadow root. The chat uses
// light DOM, so expose the component styles globally once for the host page.
const styleId = 'ai-assistant-chat-component-styles';
if (!document.getElementById(styleId) && Array.isArray(AiAssistantChat.styles)) {
    const style = document.createElement('style');
    style.id = styleId;
    style.textContent = AiAssistantChat.styles.join('\n');
    document.head.appendChild(style);
}

// Keep the component's DOM themeable by host applications. The component
// remains isolated by its Vue API, while project CSS can override its classes.
customElements.define(
    'ai-assistant-chat',
    defineCustomElement(AiAssistantChat, { shadowRoot: false }),
);
