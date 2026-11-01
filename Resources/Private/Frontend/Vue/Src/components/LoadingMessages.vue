<template>

    <span v-if="message.typing && normalizedLoadingMessages.length" class="chat-box-typing-messages"
          aria-label="Loading" v-html="normalizedLoadingMessages[currentLoadingMessage]">

    </span>

</template>

<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';

/**
 * @property {Object} message Current chat message.
 * @property {Array|string} loadingMessages Optional rotating loading messages.
 * @property {number|string} loadingMessagesTimeout Rotation interval in milliseconds.
 */

const props = defineProps({
    message: { type: Object, required: true },
    loadingMessages: { type: [Array, String], default: () => [] },
    loadingMessagesTimeout: { type: [String, Number], default: 2000 },
});

/**
 * Parses loading messages supplied as an array or JSON-encoded attribute.
 *
 * @param {Array|string} value Loading message configuration.
 * @return {string[]} Non-empty loading messages.
 */
const parseLoadingMessages = (value) => {
    if (Array.isArray(value)) {
        return value.filter((message) => typeof message === 'string' && message !== '');
    }

    if (typeof value !== 'string' || value.trim() === '') {
        return [];
    }

    try {
        const parsed = JSON.parse(value);
        return Array.isArray(parsed)
            ? parsed.filter((message) => typeof message === 'string' && message !== '')
            : [];
    } catch (error) {
        return [];
    }
};

/** @type {import('vue').ComputedRef<string[]>} Rotating loading messages. */
const normalizedLoadingMessages = computed(() => parseLoadingMessages(props.loadingMessages));

/** @type {import('vue').Ref<number>} Current loading message index. */
const currentLoadingMessage = ref(0);

/** @type {number|null} Loading message rotation timer. */
let loadingMessageTimer = null;

/** Stops the loading message rotation. */
const stopLoadingMessageRotation = () => {
    if (loadingMessageTimer !== null) {
        window.clearInterval(loadingMessageTimer);
        loadingMessageTimer = null;
    }
};

/** Starts the loading message rotation when multiple messages are configured. */
const startLoadingMessageRotation = () => {
    stopLoadingMessageRotation();
    currentLoadingMessage.value = 0;

    if (normalizedLoadingMessages.value.length < 2) {
        return;
    }

    const timeout = Math.max(250, Number(props.loadingMessagesTimeout) || 3000);
    loadingMessageTimer = window.setInterval(() => {
        currentLoadingMessage.value = (currentLoadingMessage.value + 1)
            % normalizedLoadingMessages.value.length;
    }, timeout);
};

/** @type {import('vue').ComputedRef<boolean>} Whether the current message is loading. */
const isTyping = computed(() => props.message.typing === true);

watch([isTyping, normalizedLoadingMessages], ([typing]) => {
    if (typing) {
        startLoadingMessageRotation();
    } else {
        stopLoadingMessageRotation();
    }
}, { immediate: true });

onBeforeUnmount(stopLoadingMessageRotation);
</script>

