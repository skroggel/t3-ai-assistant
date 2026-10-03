<template>
    <div class="chat-box-messages" role="log" aria-live="polite">
        <article
            v-for="message in messages"
            :key="message.id"
            class="chat-box-message"
            :class="message.role === 'user' ? 'chat-box-message--from-user' : 'chat-box-message--from-bot'"
            :lang="message.role === 'assistant' ? message.languageCode : undefined"
        >
            <span class="aiassistant-visually-hidden">
                {{ message.role === 'user' ? userLabel : assistantLabel }}:
            </span>
            <div class="chat-box-message-content">
                <span v-html="message.html" />
                <span v-if="message.typing" class="chat-box-typing-dots" aria-label="Loading">
                    <span class="dot" aria-hidden="true"></span>
                    <span class="dot" aria-hidden="true"></span>
                    <span class="dot" aria-hidden="true"></span>
                </span>
            </div>
        </article>
    </div>
</template>

<script setup>
/**
 * @typedef {Object} ChatMessage
 * @property {string} id Stable message identifier.
 * @property {'user'|'assistant'} role Message origin.
 * @property {string} html Sanitized rendered HTML.
 */

/** @type {import('vue').DefineProps<{messages: ChatMessage[]}>} */
defineProps({
    messages: { type: Array, required: true },
    userLabel: { type: String, default: 'User' },
    assistantLabel: { type: String, default: 'Assistant' },
});
</script>
