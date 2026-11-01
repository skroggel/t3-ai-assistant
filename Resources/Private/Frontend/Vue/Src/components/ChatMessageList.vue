<template>
    <div class="chat-box-messages" role="log" aria-live="polite">
        <article
            v-for="message in messages"
            :key="message.id"
            class="chat-box-message"
            :class="[
                message.source === 'ui'
                    ? 'chat-box-message--from-ui'
                    : (message.role === 'user' ? 'chat-box-message--from-user' : 'chat-box-message--from-bot'),
            ]"
            :lang="message.role === 'assistant' ? message.languageCode : undefined"
        >
            <span class="aiassistant-visually-hidden">
                {{ message.role === 'user' ? userLabel : assistantName }}:
            </span>
            <div class="chat-box-message-content">
                <div v-html="message.html" v-if="message.html"/>
                <span v-if="message.typing" class="chat-box-typing-placeholder" aria-label="Loading">
                    <LoadingMessages
                        :message="message"
                        :loading-messages="loadingMessages"
                        :loading-messages-timeout="loadingMessagesTimeout"
                    />
                    <span v-if="loadingHtml" class="chat-box-typing-html" v-html="loadingHtml" aria-hidden="true"></span>
                    <span v-if="numberOfLoadingDots" class="chat-box-typing-dots" aria-hidden="true">
                        <span
                            v-for="n in numberOfLoadingDots"
                            :key="n"
                            class="dot"
                            :style="{ '--dot-index': n }"
                            aria-hidden="true"
                        ></span>
                    </span>
                </span>
            </div>
        </article>
    </div>
</template>

<script setup>
import LoadingMessages from "./LoadingMessages.vue";

/**
 * @typedef {Object} ChatMessageListProps
 * @property {ChatMessage[]} messages Chat messages.
 * @property {string} userLabel User label for accessibility output.
 * @property {string} assistantName Assistant label for accessibility output.
 * @property {number} numberOfLoadingDots Number of fallback loading dots.
 * @property {string} loadingHtml Optional HTML for the loading indicator.
 * @property {Array|string} loadingMessages Optional rotating loading messages.
 * @property {number|string} loadingMessagesTimeout Rotation interval in milliseconds.
 */

const props = defineProps({
    messages: { type: Array, required: true },
    userLabel: { type: String, default: 'User' },
    assistantName: { type: String, default: 'Assistant' },
    numberOfLoadingDots: { type: Number, default: 3 },
    loadingHtml: { type: String, default: '' },
    loadingMessages: { type: [Array, String], default: () => [] },
    loadingMessagesTimeout: { type: [String, Number], default: 2000 },
});
</script>
