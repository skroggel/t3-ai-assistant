<template>
    <!--
        The custom element deliberately contains no chat markup of its own.
        It delegates the complete presentation to the regular Vue component so
        both entry points share exactly the same template and behavior.

        `v-bind="props"` forwards the complete public chat contract. This is
        important for custom-element attributes because Vue has already
        normalized their values according to `chatProps` in this wrapper.
        Errors are forwarded explicitly because the native custom-element
        consumer listens to the wrapper's emitted `error` event.
    -->
    <AiAssistantChat ref="chat" v-bind="props" @error="forwardError" />
</template>

<script setup>
/**
 * Framework-independent custom-element adapter for the AI Assistant chat.
 *
 * The actual state management, transport handling, response rendering and
 * standard markup live in `AiAssistantChat.vue` and `useAiAssistantChat.js`.
 * Keeping this file as a thin adapter prevents the custom-element build from
 * becoming a second implementation of the chat.
 *
 * This component is compiled with Vue's `defineCustomElement` by
 * `ai-assistant-chat.js`. Consumers without a Vue application can therefore
 * use the generated `<ai-assistant-chat>` element, while Vue applications can
 * import the regular `AiAssistantChat.vue` component directly.
 *
 * @module AiAssistantChatCustomElement
 */
import { ref } from 'vue';

// The shared prop definition keeps attribute handling identical between the
// custom-element adapter and the regular Vue component.
import { chatProps } from '../chatProps.js';
import AiAssistantChat from './AiAssistantChat.vue';

/**
 * Receives and normalizes all public chat attributes before passing them to the
 * nested component. Keeping the contract here also makes Vue convert native
 * custom-element attributes such as booleans and numbers consistently.
 *
 * @type {import('vue').DefineProps<AiAssistantChatProps>}
 */
const props = defineProps(chatProps);

/**
 * The custom element exposes errors as a Vue event. The browser-facing custom
 * element adapter does not interpret the error; the host application decides
 * whether it wants to log it, display custom UI or ignore it.
 */
const emit = defineEmits(['error']);

/**
 * Reference to the nested standard component.
 *
 * The ref is needed only for forwarding the imperative
 * `startDirectInteraction` API. Conversation state remains inside the shared
 * composable and is not duplicated by this wrapper.
 *
 * @type {import('vue').Ref<InstanceType<typeof AiAssistantChat>|null>}
 */
const chat = ref(null);

/**
 * Forwards the standard component's error event through the custom element.
 *
 * @param {unknown} error Error emitted by the nested chat component.
 * @return {void}
 */
const forwardError = (error) => emit('error', error);

/**
 * Exposes the small imperative API supported by the existing chat contract.
 *
 * Optional chaining keeps the method safe during the short interval before
 * the nested component is mounted or after it has been unmounted. The method
 * still uses the same `send()` path as normal input, language confirmation and
 * configured UI actions; this wrapper does not introduce another request
 * mechanism.
 */
defineExpose({
    startDirectInteraction: (...args) => chat.value?.startDirectInteraction(...args),
});
</script>
