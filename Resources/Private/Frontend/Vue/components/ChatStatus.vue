<template>
    <p v-if="status" class="chat-box-status aiassistant-visually-hidden" role="status" aria-live="polite">
        {{ status }}
    </p>
</template>

<script setup>
/** @type {import('vue').DefineProps<{status?: string}>} */
defineProps({
    status: { type: String, default: '' },
});
</script>
