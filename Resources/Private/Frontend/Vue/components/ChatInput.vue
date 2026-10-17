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


<style lang="scss">
@layer default, instance;
@layer default {
    .chat-box-prompt {
        display: flex;
        gap: 0.5rem;
        align-items: flex-end;
        margin-top: 1rem;
        border: 1px solid var(--chat-box-color-secondary);
        border-radius: 0.5rem;
        align-items: stretch;

        &-input {
            flex: 1 1 auto;
            min-height: 2.75rem;
            padding: 0.65rem 0.8rem;
            resize: vertical;
            color: var(--bs-body-color, #1f2933);
            background: #fff;
            border: 1px solid var(--chat-box-color-secondary);
            border-radius: 0.6rem;
            font: inherit;
        }

        &-submit {
            min-height: 2.75rem;
            padding: 0.65rem 1rem;
            color: #fff;
            background: var(--chat-box-color-primary);
            border: 1px solid var(--chat-box-color-primary);
            border-radius: 0.55rem;
            cursor: pointer;
            font: inherit;
            font-weight: 600;
            white-space: nowrap;
        }
    }
}

</style>

