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
                <span v-if="message.typing" class="chat-box-typing-dots" aria-label="Loading">
                    <span v-for="n in numberOfLoadingDots" class="dot" aria-hidden="true"></span>
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
 * @property {'ui'|undefined} source Optional source of a generated user message.
 * @property {string} html Sanitized rendered HTML.
 */

/** @type {import('vue').DefineProps<{messages: ChatMessage[]}>} */
defineProps({
    messages: { type: Array, required: true },
    userLabel: { type: String, default: 'User' },
    assistantName: { type: String, default: 'Assistant' },
    numberOfLoadingDots: { type: Number, default: 3 }
});
</script>


<style lang="scss">
@layer default, instance;
@layer default {
    .chat-box-messages {
        display: flex;
        flex-direction: column;
        gap: 32px;
        min-height: 300px;
        padding: 1rem;
        overflow-y: auto;
        background-color: #f8f9fa;
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .chat-box-message {
        position: relative;
        max-width: 80%;
        padding: 0.75rem;
        overflow-wrap: anywhere;
        border-radius: 0.5rem;

        &:after {
            content: "";
            position: absolute;
            left: -0.4375rem;
            bottom: 0;
            width: 0.75rem;
            height: 0.875rem;
            background-color: var(--chat-box-color-secondary);
            clip-path: polygon(100% 0, 0 100%, 100% 100%);
        }

        &--from-user::after {
            right: -0.4375rem;
            left: unset;
            background-color: var(--chat-box-color-primary);
            clip-path: polygon(0 0, 0 100%, 100% 100%);
        }

        &--from-ui,
        &--from-user {
            align-self: flex-end;
            color: #fff;
            background-color: var(--chat-box-color-primary);
            border-bottom-right-radius: 0.125rem;
        }

        &--from-bot {
            align-self: flex-start;
            color: var(--bs-body-color);
            background-color: var(--chat-box-color-secondary);
            border-bottom-left-radius: 0.125rem;
        }

        &--from-ui {
            display: none;
        }

        &-content {
            display: block;

            > :first-child {
                margin-top: 0;
            }

            > :last-child {
                margin-bottom: 0;
            }

            p {
                margin: 0 0 1rem;
            }

            ul, ol {
                margin: 0 0 1rem 1.5rem;
                padding: 0;
            }

            h1, h2, h3, h4, h5, h6 {
                margin: 0 0 0.75rem;
                line-height: 1.3;
            }

            code {
                padding: 0.125rem 0.25rem;
                border-radius: 0.25rem;
                background: rgba(0, 0, 0, 0.08);
                font-size: 0.9em;
            }

            pre {
                margin: 0 0 1rem;
                padding: 0.75rem;
                overflow-x: auto;
                border-radius: 0.5rem;
                background: rgba(0, 0, 0, 0.08);

                code {
                    padding: 0;
                    background: transparent;
                }
            }

            blockquote {
                margin: 0 0 1rem;
                padding-left: 1rem;
                border-left: 3px solid rgba(0, 0, 0, 0.15);
            }

            a {
                color: inherit;
                text-decoration: underline;
                text-underline-offset: 0.12em;
            }

            table {
                width: 100%;
                margin: 0 0 1rem;
                border-collapse: collapse;
            }

            th, td {
                padding: 0.5rem;
                border: 1px solid rgba(0, 0, 0, 0.15);
                text-align: left;
            }

            th {
                font-weight: 600;
            }

            .chat-box-typing-dots {
                display: none;
                margin-top: 0.6rem;
                gap: 0.25rem;
                align-items: center;
                min-height: 1rem;
                color: #212529;
                visibility: visible;
                overflow: visible;

                .dot {
                    display: inline-block;
                    flex: 0 0 0.5rem;
                    width: 0.5rem;
                    height: 0.5rem;
                    min-width: 0.5rem;
                    min-height: 0.5rem;
                    background-color: #212529;
                    border-radius: 50%;
                    animation: aiassistant-vue-blink 1.4s infinite both;
                    opacity: 0.35;

                    &:nth-child(2) {
                        animation-delay: 0.2s;
                    }

                    &:nth-child(3) {
                        animation-delay: 0.4s;
                    }
                }
            }

            & > .chat-box-typing-dots:first-child {
                display: inline-flex;
            }
        }
    }
}

@keyframes aiassistant-vue-blink {
    0%, 100% { opacity: 0.35; transform: translateY(0); }
    50% { opacity: 1; transform: translateY(-0.2rem); }
}

</style>

