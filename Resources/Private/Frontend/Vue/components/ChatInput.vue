<template>
<form class="chat-box-prompt" @submit.prevent="submit">
        <input
            v-model="value"
            class="chat-box-prompt-input form-control"
            :placeholder="placeholder"
            :disabled="disabled"
            required
        />
        <button class="btn btn-primary chat-box-prompt-submit" type="submit" :disabled="disabled || !value.trim()">
            {{ submitLabel }}
        </button>
    </form>
</template>

<script setup>
import { ref } from 'vue';

/**
 * @typedef {Object} ChatInputProps
 * @property {string} placeholder Input placeholder.
 * @property {string} submitLabel Submit button label.
 * @property {boolean} disabled Whether input is disabled.
 */

/** @type {import('vue').DefineProps<ChatInputProps>} */
defineProps({
    placeholder: { type: String, default: 'Your message...' },
    submitLabel: { type: String, default: 'Send' },
    disabled: { type: Boolean, default: false },
});

/** @type {Record<'submit', (value: string) => void>} */
const emit = defineEmits(['submit']);

/** @type {import('vue').Ref<string>} */
const value = ref('');

/**
 * Emits the current prompt and clears the input like the legacy chatbox.
 *
 * @return {void}
 */
const submit = () => {
    const prompt = value.value.trim();
    if (!prompt) return;
    value.value = '';
    emit('submit', prompt);
};
</script>
