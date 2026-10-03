<template>
    <section class="aiassistant-chat chat-box container" role="region" :aria-label="labels.chatLabel || chatLabel">
        <div v-if="requireConsent && !consentGiven" ref="consentPanel" class="chat-box-consent alert alert-warning" role="note">
            <p>{{ labels.consentMessage || consentMessage }}</p>
            <button ref="consentButton" type="button" class="btn btn-primary" @click="acceptConsent">{{ labels.consentLabel || consentLabel }}</button>
        </div>

        <template v-else>
            <ChatMessageList
                :messages="messages"
                :user-label="labels.userLabel || userLabel"
                :assistant-label="labels.assistantLabel || assistantLabel"
            />
                <ChatStatus :status="statusMessage" />
            <LanguageSelector
                :visible="showLanguageSelector"
                :site-language="effectiveResponseLanguage"
                :language-code="effectiveLanguageCode"
                :language-label="labels.languageLabel || languageLabel"
                :site-language-label="labels.siteLanguageLabel || siteLanguageLabel"
                :browser-language-label="labels.browserLanguageLabel || browserLanguageLabel"
                :apply-label="labels.languageApplyLabel || languageApplyLabel"
                :placeholder="labels.languagePlaceholder || languagePlaceholder"
                @language-selected="confirmLanguage"
            />
            <ChatInput
                :placeholder="labels.inputPlaceholder || inputPlaceholder"
                :submit-label="labels.submitLabel || submitLabel"
                :disabled="loading"
                @submit="send"
            />
        </template>
    </section>
</template>

<script setup>
import DOMPurify from 'dompurify';
import { marked } from 'marked';
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';

import ChatInput from './ChatInput.vue';
import ChatMessageList from './ChatMessageList.vue';
import ChatStatus from './ChatStatus.vue';
import LanguageSelector from './LanguageSelector.vue';

/**
 * @typedef {Object} AiAssistantChatProps
 * @property {string} endpoint SSE chat endpoint.
 * @property {string|number} assistantProfile Assistant profile uid.
 * @property {string} chatIdentifier Conversation identifier.
 * @property {string|number} startTimestamp Session reset timestamp.
 * @property {string} settingsJson Serialized runtime settings.
 * @property {string} chatOptionsJson Serialized chat options.
 * @property {string} labelsJson Serialized translated labels.
 * @property {object} sanitizeOptions DOMPurify options passed directly as an object.
 * @property {string} autoQuery Optional query sent automatically after mount.
 * @property {string} initialMessage Optional initial assistant message.
 * @property {number|string} initialMessageDelay Delay in milliseconds between initial-message updates.
 * @property {number|string} responseMessageDelay Delay in milliseconds between streamed response updates.
 * @property {boolean|string|number} requireConsent Whether consent is required. Accepts boolean values and 0/1 strings or numbers.
 * @property {boolean|string|number} plainLanguage Whether plain-language mode is enabled. Accepts boolean values and 0/1 strings or numbers.
 * @property {boolean|string|number} showLanguageSelector Whether the language selector is shown. Accepts boolean values and 0/1 strings or numbers.
 * @property {string} responseLanguage Configured response language name.
 * @property {string} languageCode Configured response language code.
 * @property {string} consentMessage Consent text.
 * @property {string} consentLabel Consent button label.
 * @property {string} inputPlaceholder Input placeholder.
 * @property {string} submitLabel Submit button label.
 * @property {string} languageLabel Language selector label.
 * @property {string} siteLanguageLabel Website-language option label.
 * @property {string} browserLanguageLabel Browser-language option label.
 * @property {string} languageApplyLabel Apply-language button label.
 * @property {string} languagePlaceholder Language input placeholder.
 * @property {string} languageConfirmationFallback Fallback confirmation message. `%s` is replaced with the selected language.
 * @property {string} chatLabel Accessible chat region label.
 * @property {string} userLabel Accessible user message label.
 * @property {string} assistantLabel Accessible assistant message label.
 */

/** @type {import('vue').DefineProps<AiAssistantChatProps>} */
const props = defineProps({
    // Request identity.
    endpoint: { type: String, default: '' },
    assistantProfile: { type: [String, Number], default: 0 },
    chatIdentifier: { type: String, default: '' },
    startTimestamp: { type: [String, Number], default: 0 },

    // Runtime configuration.
    settingsJson: { type: String, default: '{}' },
    chatOptionsJson: { type: String, default: '{}' },
    labelsJson: { type: String, default: '{}' },
    sanitizeOptions: { type: Object, default: () => ({}) },

    // Chat behavior.
    autoQuery: { type: String, default: '' },
    initialMessage: { type: String, default: '' },
    initialMessageDelay: { type: [String, Number], default: 22 },
    responseMessageDelay: { type: [String, Number], default: 14 },
    requireConsent: { type: [String, Number, Boolean], default: false },
    plainLanguage: { type: [String, Number, Boolean], default: false },

    // Language behavior.
    showLanguageSelector: { type: [String, Number, Boolean], default: false },
    responseLanguage: { type: String, default: '' },
    languageCode: { type: String, default: '' },

    // Labels and messages.
    errorMessage: { type: String, default: '' },
    consentMessage: { type: String, default: '' },
    consentLabel: { type: String, default: 'Start chat' },
    inputPlaceholder: { type: String, default: 'Your message...' },
    submitLabel: { type: String, default: 'Send' },
    languageLabel: { type: String, default: 'Response language' },
    siteLanguageLabel: { type: String, default: 'Use website language' },
    browserLanguageLabel: { type: String, default: 'Use browser language' },
    languageApplyLabel: { type: String, default: 'Use selected language' },
    languagePlaceholder: { type: String, default: 'Language' },
    languageConfirmationFallback: { type: String, default: 'Language selected: %s' },
    chatLabel: { type: String, default: 'AI Assistant chat' },
    userLabel: { type: String, default: 'User' },
    assistantLabel: { type: String, default: 'Assistant' },
});

/** @type {Record<string, any>} Normalized frontend chat options. */
let chatOptions = {};
try {
    const parsedOptions = JSON.parse(props.chatOptionsJson || '{}');
    chatOptions = parsedOptions && typeof parsedOptions === 'object' ? parsedOptions : {};
} catch (error) {
    chatOptions = {};
}

/** @type {Record<string, string>} Translated labels provided by TYPO3. */
let labels = {};
try {
    const parsedLabels = JSON.parse(props.labelsJson || '{}');
    labels = parsedLabels && typeof parsedLabels === 'object' ? parsedLabels : {};
} catch (error) {
    labels = {};
}

/**
 * Converts HTML/Fluid boolean values into real booleans.
 *
 * @param {unknown} value Value received from Vue or an HTML attribute.
 * @return {boolean} Normalized boolean value.
 */
const toBoolean = (value) => {
    if (typeof value === 'string') {
        return value.trim().toLowerCase() === 'true' || value.trim() === '1' || value.trim() === '';
    }

    return value === true || value === 1;
};


/** @type {Record<string, any>} Request options for the shared transport. */
const requestOptions = chatOptions.request || {};

/** @type {Record<string, any>} Accessibility options from runtime settings. */
const accessibilityOptions = chatOptions.accessibility || {};

/** @type {string} Effective response language name. */
const effectiveResponseLanguage = props.responseLanguage || chatOptions.language?.responseLanguage || '';

/** @type {string} Effective response language code. */
const effectiveLanguageCode = props.languageCode || chatOptions.language?.languageCode || '';

/** @type {string} Extbase plugin parameter namespace. */
const parameterPrefix = 'tx_aiassistant_chat';

/** @type {number} Initial-message delay in milliseconds. */
const initialMessageDelay = Math.max(0, Number(props.initialMessageDelay) || 0);

/** @type {number} Response-chunk delay in milliseconds. */
const responseMessageDelay = Math.max(0, Number(props.responseMessageDelay) || 0);

/** @type {import('vue').ComputedRef<boolean>} */
const requireConsent = computed(() => toBoolean(props.requireConsent));

/** @type {import('vue').ComputedRef<boolean>} */
const showLanguageSelector = computed(() => toBoolean(props.showLanguageSelector));

/** @type {import('vue').ComputedRef<boolean>} */
const plainLanguage = computed(() => toBoolean(props.plainLanguage) || toBoolean(accessibilityOptions.plainLanguage));

/** @type {import('vue').Ref<Array<Record<string, any>>>} */
const messages = ref([]);

/** @type {import('vue').Ref<boolean>} */
const loading = ref(false);

/** @type {import('vue').Ref<boolean>} */
const consentGiven = ref(!requireConsent.value);

/** @type {import('vue').Ref<string>} */
const statusMessage = ref('');

/** @type {import('vue').Ref<string>} */
const selectedLanguageCode = ref('');

/** @type {import('vue').Ref<HTMLElement|null>} */
const consentButton = ref(null);

/** @type {import('vue').Ref<HTMLElement|null>} */
const consentPanel = ref(null);

/** @type {AbortController|null} */
let abortController = null;

/** @type {boolean} */
let initialMessageAdded = false;

/** @type {import('dompurify').Config} Secure DOMPurify defaults. */
const defaultSanitizeOptions = {
    ALLOWED_TAGS: [
        'a', 'blockquote', 'br', 'code', 'del', 'em', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'hr', 'li', 'ol', 'p', 'pre', 'strong', 'table', 'tbody', 'td', 'th', 'thead', 'tr', 'ul',
    ],
    ALLOWED_ATTR: ['href', 'title'],
};

const sanitizeOptions = {
    ...defaultSanitizeOptions,
    ...(chatOptions.sanitizeOptions && typeof chatOptions.sanitizeOptions === 'object' ? chatOptions.sanitizeOptions : {}),
    ...props.sanitizeOptions,
};

/**
 * Adds the legacy external-link behavior after sanitization.
 *
 * @param {string} html Sanitized HTML.
 * @return {string} Enhanced HTML.
 */
const enhanceLinks = (html) => {
    const template = document.createElement('template');
    template.innerHTML = html;
    template.content.querySelectorAll('a[href]').forEach((link) => {
        const href = link.getAttribute('href') || '';
        if (/^(https?:|mailto:|tel:)/i.test(href)) {
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noopener noreferrer');
        }
    });
    return template.innerHTML;
};

/**
 * Converts Markdown to sanitized HTML and enhances external links.
 *
 * @param {string} content Markdown content.
 * @return {string} Sanitized HTML.
 */
const renderMarkdown = (content) => enhanceLinks(
    DOMPurify.sanitize(marked.parse(content || '', { gfm: true, breaks: true }), sanitizeOptions),
);

/**
 * Waits for the next browser paint.
 *
 * @return {Promise<void>} Resolves after the next animation frame.
 */
const waitForPaint = () => new Promise((resolve) => {
    if (typeof requestAnimationFrame === 'function') {
        requestAnimationFrame(resolve);
        return;
    }

    setTimeout(resolve, 0);
});

/**
 * Waits for a configured number of milliseconds.
 *
 * @param {number} milliseconds Delay duration.
 * @return {Promise<void>} Resolves after the delay.
 */
const wait = (milliseconds) => new Promise((resolve) => {
    setTimeout(resolve, milliseconds);
});

/**
 * Adds the configured initial assistant message once and pseudo-streams it.
 *
 * @return {Promise<void>} Resolves when the initial message is complete.
 */
const addInitialMessage = async () => {
    const initialContent = props.initialMessage.trim();
    if (initialMessageAdded || initialContent === '') {
        return;
    }
    initialMessageAdded = true;

    messages.value.push({
        id: crypto.randomUUID(),
        role: 'assistant',
        languageCode: selectedLanguageCode.value || effectiveLanguageCode,
        html: '',
        typing: true,
    });
    const messageIndex = messages.value.length - 1;
    const parts = initialContent.split(/(\s+)/);
    let content = '';

    loading.value = true;
    for (const part of parts) {
        content += part;
        messages.value[messageIndex].html = renderMarkdown(content);
        await wait(initialMessageDelay);
        await nextTick();
        await waitForPaint();
    }
    loading.value = false;
    messages.value[messageIndex].typing = false;
};

/**
 * Accepts consent, starts the initial message and focuses the chat input.
 *
 * @return {Promise<void>} Resolves after the chat becomes visible.
 */
const acceptConsent = async () => {
    consentGiven.value = true;
    await nextTick();
    await addInitialMessage();
    consentButton.value?.closest('.chat-box')?.querySelector('.chat-box-prompt-input')?.focus();
};

/**
 * Sends one user message and streams the assistant response.
 *
 * @param {string} query User query or direct-interaction input.
 * @param {string} directInteraction Optional direct-interaction identifier.
 * @param {{showUserMessage?: boolean, fallbackMessage?: string}} options Request options.
 * @return {Promise<void>} Resolves when the stream is complete.
 */
const send = async (query, directInteraction = '', options = {}) => {
    if (loading.value) return;

    if (options.showUserMessage !== false) {
        messages.value.push({ id: crypto.randomUUID(), role: 'user', html: renderMarkdown(query) });
    }
    loading.value = true;
    statusMessage.value = '';

    const formData = new FormData();
    formData.set(`${parameterPrefix}[query]`, query);
    formData.set(`${parameterPrefix}[assistantProfile]`, String(props.assistantProfile));
    formData.set(`${parameterPrefix}[chatIdentifier]`, props.chatIdentifier);
    formData.set(`${parameterPrefix}[startTimestamp]`, String(props.startTimestamp || Math.floor(Date.now() / 1000)));
    formData.set(`${parameterPrefix}[settingsJson]`, props.settingsJson || '{}');
    formData.set(`${parameterPrefix}[directInteraction]`, directInteraction);
    formData.set(`${parameterPrefix}[userLanguage]`, selectedLanguageCode.value);
    formData.set(`${parameterPrefix}[plainLanguage]`, plainLanguage.value ? '1' : '0');

    messages.value.push({
        id: crypto.randomUUID(),
        role: 'assistant',
        html: '',
        typing: true,
        languageCode: selectedLanguageCode.value || effectiveLanguageCode,
    });
    const messageIndex = messages.value.length - 1;

    try {
        await nextTick();
        const Transport = window.AiAssistantTransport;
        if (typeof Transport !== 'function') {
            throw new Error('AI Assistant transport is not available.');
        }

        let answer = '';
        const transport = new Transport();
        abortController = new AbortController();
        await transport.streamSse(props.endpoint, formData, async (content) => {
            answer = content;
            messages.value[messageIndex].html = renderMarkdown(answer);
            await nextTick();
            await waitForPaint();
            await wait(responseMessageDelay);
        }, {
            signal: abortController.signal,
            method: requestOptions.method || 'POST',
            accept: requestOptions.accept || 'text/event-stream',
            credentials: requestOptions.credentials || 'same-origin',
        });
        messages.value[messageIndex].typing = false;
    } catch (error) {
        messages.value[messageIndex].typing = false;
        if (options.showUserMessage === false && options.fallbackMessage) {
            messages.value[messageIndex].html = renderMarkdown(options.fallbackMessage);
        } else {
            statusMessage.value = props.errorMessage || labels.errorMessage || (error instanceof Error ? error.message : 'The answer could not be loaded.');
        }
    } finally {
        abortController = null;
        loading.value = false;
    }
};

/**
 * Sends the existing language confirmation interaction.
 *
 * @param {{value: string, name: string}} selection Selected language.
 * @return {Promise<void>} Resolves after the confirmation response.
 */
const confirmLanguage = async (selection) => {
    selectedLanguageCode.value = selection.code || '';
    await send(selection.name || selection.value, 'language_confirmation', {
        showUserMessage: false,
        fallbackMessage: (labels.languageConfirmationFallback || props.languageConfirmationFallback).replace('%s', selection.name || selection.value),
    });
};

/**
 * Sends a direct interaction without adding a user message.
 *
 * @param {string} interaction Direct-interaction identifier.
 * @param {string} query Direct-interaction input.
 * @return {Promise<void>} Resolves after the interaction completes.
 */
const startDirectInteraction = (interaction, query = '') => send(query, interaction, {
    showUserMessage: false,
});

/**
 * Initializes the chat after the custom element is mounted.
 *
 * @return {Promise<void>} Resolves after initial content is prepared.
 */
onMounted(async () => {
    if (!requireConsent.value) {
        await addInitialMessage();
    }

    if (requireConsent.value) {
        nextTick(() => consentButton.value?.focus());
    } else if (props.autoQuery.trim() !== '') {
        await send(props.autoQuery);
    }
});

/**
 * Aborts an active request when the custom element is removed.
 *
 * @return {void}
 */
onBeforeUnmount(() => {
    abortController?.abort();
});

defineExpose({ startDirectInteraction });
</script>

<style lang="scss">
.aiassistant-chat {
    .aiassistant-visually-hidden {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
    }

    max-width: 700px;
    margin: 0 auto;
    --chat-box-color-primary: var(--bs-primary, #205eb5);
    --chat-box-color-secondary: var(--bs-secondary, #d6d6d6);

    .chat-box-consent,
    .chat-box-language {
        margin-bottom: 1rem;
    }

    .chat-box-language-toggle {
        max-width: 100%;
        white-space: normal;
    }

    .chat-box-language-options {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr));
        gap: 0.5rem;
        margin-top: 0.75rem;
        padding: 0.75rem;
        border: 1px solid rgba(0, 0, 0, 0.15);
        border-radius: 0.5rem;
    }

    .chat-box-messages {
        display: flex;
        flex-direction: column;
        gap: 32px;
        min-height: 300px;
        padding: 1rem;
        overflow-y: auto;
        background-color: #f8f9fa;
        border-radius: 0.5rem;
    }

    .chat-box-message {
        position: relative;
        max-width: 80%;
        padding: 0.75rem;
        overflow-wrap: anywhere;
        border-radius: 0.5rem;

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
    }

    .chat-box-message-content {
        display: block;

        > :first-child { margin-top: 0; }
        > :last-child { margin-bottom: 0; }

        p { margin: 0 0 1rem; }
        ul, ol { margin: 0 0 1rem 1.5rem; padding: 0; }
        h1, h2, h3, h4, h5, h6 { margin: 0 0 0.75rem; line-height: 1.3; }

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

            code { padding: 0; background: transparent; }
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

        th { font-weight: 600; }
    }

    .chat-box-prompt {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
        border: 1px solid var(--chat-box-color-secondary);
        border-radius: 0.5rem;

        textarea { flex: 1 1 auto; resize: vertical; }
    }

    .chat-box-typing-dots {
        display: inline-flex !important;
        margin-top: 0.6rem;
        gap: 0.25rem;
        align-items: center;
        min-height: 1rem;
        color: #212529 !important;
        visibility: visible !important;
        overflow: visible !important;

        .dot {
            display: inline-block !important;
            flex: 0 0 0.5rem !important;
            width: 0.5rem !important;
            height: 0.5rem !important;
            min-width: 0.5rem !important;
            min-height: 0.5rem !important;
            background-color: #212529 !important;
            border-radius: 50% !important;
            animation: aiassistant-vue-blink 1.4s infinite both !important;
            opacity: 0.35 !important;

            &:nth-child(2) { animation-delay: 0.2s; }
            &:nth-child(3) { animation-delay: 0.4s; }
        }
    }
}

@keyframes aiassistant-vue-blink {
    0%, 100% { opacity: 0.35; transform: translateY(0); }
    50% { opacity: 1; transform: translateY(-0.2rem); }
}

/* Basic host styling stays with the portable custom element. */
.aiassistant-chat {
    --aiassistant-primary: #1769aa;
    --aiassistant-primary-dark: #0f4f82;
    --aiassistant-surface: #f4f7fa;
    --aiassistant-border: #d9e1e8;
    --aiassistant-text: #1f2933;
    width: min(100%, 48rem);
    max-width: 48rem;
    margin: 0 auto;
    padding: 1.25rem;
    color: var(--aiassistant-text);
}

.aiassistant-chat .chat-box-messages {
    min-height: 12rem;
    max-height: 34rem;
    padding: 1rem;
    overflow-y: auto;
    background: var(--aiassistant-surface);
    border: 1px solid var(--aiassistant-border);
    border-radius: 1rem;
}

.aiassistant-chat .chat-box-message {
    width: fit-content;
    max-width: min(85%, 38rem);
    padding: 0.8rem 1rem;
    line-height: 1.5;
    border: 1px solid var(--aiassistant-border);
    border-radius: 1rem;
    box-shadow: 0 0.15rem 0.4rem rgb(31 41 51 / 5%);
}

.aiassistant-chat .chat-box-message--from-bot {
    align-self: flex-start;
    background: #fff;
    border-bottom-left-radius: 0.3rem;
}

.aiassistant-chat .chat-box-message--from-user {
    align-self: flex-end;
    color: #fff;
    background: var(--aiassistant-primary);
    border-color: var(--aiassistant-primary);
    border-bottom-right-radius: 0.3rem;
}

.aiassistant-chat .chat-box-language-options {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
    margin-top: 0.65rem;
    padding: 0.75rem;
    background: #fff;
    border: 1px solid var(--aiassistant-border);
    border-radius: 0.75rem;
}

.aiassistant-chat .chat-box-language-options {
    display: flex;
}

.aiassistant-chat .chat-box-language-custom {
    display: flex;
    flex: 1 1 100%;
    gap: 0.5rem;
    align-items: stretch;
}

.aiassistant-chat .chat-box-language-custom input {
    flex: 1 1 auto;
}

.aiassistant-chat .chat-box-language-custom button {
    flex: 0 0 auto;
}

.aiassistant-chat .chat-box-language-toggle,
.aiassistant-chat .chat-box-language-options button,
.aiassistant-chat .chat-box-prompt-submit {
    border: 1px solid var(--aiassistant-primary);
    border-radius: 0.55rem;
    cursor: pointer;
    font: inherit;
}

.aiassistant-chat .chat-box-language-toggle,
.aiassistant-chat .chat-box-language-options button {
    padding: 0.45rem 0.75rem;
    color: var(--aiassistant-primary);
    background: #fff;
    font-weight: 600;
}

.aiassistant-chat .chat-box-language-options input,
.aiassistant-chat .chat-box-prompt-input {
    padding: 0.65rem 0.8rem;
    color: var(--aiassistant-text);
    background: #fff;
    border: 1px solid var(--aiassistant-border);
    border-radius: 0.6rem;
    font: inherit;
}

.aiassistant-chat .chat-box-language-options input {
    flex: 1 1 12rem;
    min-width: 12rem;
}

.aiassistant-chat .chat-box-prompt {
    display: flex;
    gap: 0.6rem;
    align-items: flex-end;
    margin-top: 1rem;
}

.aiassistant-chat .chat-box-prompt-input {
    flex: 1 1 auto;
    min-height: 2.75rem;
    resize: vertical;
}

.aiassistant-chat .chat-box-prompt-submit {
    min-height: 2.75rem;
    padding: 0.65rem 1rem;
    color: #fff;
    background: var(--aiassistant-primary);
    font-weight: 600;
    white-space: nowrap;
}

.aiassistant-chat .chat-box-consent {
    padding: 1rem;
    background: #fff8e6;
    border: 1px solid #f0cf78;
    border-radius: 0.75rem;
}

@media (max-width: 40rem) {
    .aiassistant-chat {
        padding: 0.75rem 0;
    }

    .aiassistant-chat .chat-box-message {
        max-width: 92%;
    }

    .aiassistant-chat .chat-box-prompt {
        flex-direction: column;
        align-items: stretch;
    }

    .aiassistant-chat .chat-box-prompt-submit {
        width: 100%;
    }

    .aiassistant-chat .chat-box-language-options {
        display: flex;
        flex-direction: column;
    }

    .aiassistant-chat .chat-box-language-options > button,
    .aiassistant-chat .chat-box-language-custom,
    .aiassistant-chat .chat-box-language-custom input,
    .aiassistant-chat .chat-box-language-custom button {
        width: 100%;
    }

    .aiassistant-chat .chat-box-language-custom {
        flex-direction: column;
    }
}
</style>
