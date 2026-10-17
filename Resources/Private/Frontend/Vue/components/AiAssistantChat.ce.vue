<template>
    <section
        :class="['aiassistant-chat', 'container', 'chat-box']"
        role="region"
        :aria-label="labels.chatLabel"
        @click="handleComponentClick"
    >
        <div v-if="requireConsent && !consentGiven" ref="consentPanel" class="chat-box-consent alert alert-warning" role="note">
            <p>
                {{ labels.consentMessage }}
                <a v-if="privacyLink" :href="privacyLink" target="_blank" rel="noopener noreferrer">
                    {{ labels.privacyLinkLabel }}
                </a>
            </p>
            <p>{{ labels.consentMessage }}</p>
            <button ref="consentButton" type="button" class="btn btn-primary" @click="acceptConsent">{{ labels.consentLabel }}</button>
        </div>

        <template v-else>

            <header class="chat-box-header" v-if="assistantName || introText || privacyLink">
                <div class="chat-box-header-top" v-if="assistantName">
                    <p class="chat-box-header-brand">
                        {{ assistantName }}
                    </p>
                </div>

                <p class="chat-box-header-intro" v-if="introText || privacyLink">
                    {{ introText }}
                    <a v-if="privacyLink" :href="privacyLink" target="_blank" rel="noopener noreferrer">
                        {{ labels.privacyLinkLabel }}
                    </a>
                </p>

                <LanguageSelector
                    :visible="showLanguageSelector"
                    :site-language="effectiveResponseLanguage"
                    :language-code="effectiveLanguageCode"
                    :language-label="labels.languageLabel"
                    :site-language-label="labels.siteLanguageLabel"
                    :browser-language-label="labels.browserLanguageLabel"
                    :apply-label="labels.languageApplyLabel"
                    :placeholder="labels.languagePlaceholder"
                    @language-selected="confirmLanguage"
                />

            </header>

            <ChatMessageList
                :messages="messages"
                :user-label="labels.userLabel"
                :assistant-name="labels.assistantName"
                :number-of-loading-dots="numberOfLoadingDots"
            />

            <ChatStatus :status="statusMessage" />

            <ChatInput
                :placeholder="labels.inputPlaceholder"
                :submit-label="labels.submitLabel"
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
 * @property {string} requestToken Signed frontend request token.
 * @property {string|number} startTimestamp Session reset timestamp.
 * @property {string} settingsJson Serialized runtime settings.
 * @property {string} chatOptionsJson Serialized chat options.
 * @property {string} labelsJson Serialized translated labels.
 * @property {string} uiComponentsJson Serialized UI component definitions.
 * @property {string} errorHandling Frontend error handling mode.
 * @property {object} sanitizeOptions DOMPurify options passed directly as an object.
 * @property {string} autoQuery Optional query sent automatically after mount.
 * @property {number|string} initialMessageDelay Delay in milliseconds between initial-message updates.
 * @property {number|string} responseMessageDelay Delay in milliseconds between streamed response updates.
 * @property {boolean|string|number} requireConsent Whether consent is required. Accepts boolean values and 0/1 strings or numbers.
 * @property {boolean|string|number} plainLanguage Whether plain-language mode is enabled. Accepts boolean values and 0/1 strings or numbers.
 * @property {boolean|string|number} showLanguageSelector Whether the language selector is shown. Accepts boolean values and 0/1 strings or numbers.
 * @property {string} responseLanguage Configured response language name.
 * @property {string} languageCode Configured response language code.
 * @property {string} initialMessage Optional initial assistant message.
 * @property {string} privacyLink Optional privacy policy URL.
 * @property {string} assistantName Optional assistant name.
 * @property {string} introText Optional header text.
 */

/** @type {import('vue').DefineProps<AiAssistantChatProps>} */
const props = defineProps({
    // Request identity.
    endpoint: { type: String, default: '' },
    assistantProfile: { type: [String, Number], default: 0 },
    chatIdentifier: { type: String, default: '' },
    requestToken: { type: String, default: '' },
    startTimestamp: { type: [String, Number], default: 0 },

    // Runtime configuration.
    settingsJson: { type: String, default: '{}' },
    chatOptionsJson: { type: String, default: '{}' },
    labelsJson: { type: String, default: '{}' },
    uiComponentsJson: { type: String, default: '[]' },
    errorHandling: { type: String, default: 'default' },
    sanitizeOptions: { type: Object, default: () => ({}) },

    // Chat behavior.
    autoQuery: { type: String, default: '' },
    initialMessageDelay: { type: [String, Number], default: 22 },
    responseMessageDelay: { type: [String, Number], default: 14 },
    requireConsent: { type: [String, Number, Boolean], default: false },
    plainLanguage: { type: [String, Number, Boolean], default: false },
    numberOfLoadingDots: { type: [Number], default: 3 },

    // Language behavior.
    showLanguageSelector: { type: [String, Number, Boolean], default: false },
    responseLanguage: { type: String, default: '' },
    languageCode: { type: String, default: '' },

    // Messages & Text
    initialMessage: { type: String, default: '' },
    errorMessage: { type: String, default: '' },

    assistantName: { type: String, default: '' },
    privacyLink: { type: String, default: '' },
    introText: { type: String, default: '' },

});

const emit = defineEmits(['error']);

/** @type {Record<string, any>} Normalized frontend chat options. */
let chatOptions = {};
try {
    const parsedOptions = JSON.parse(props.chatOptionsJson || '{}');
    chatOptions = parsedOptions && typeof parsedOptions === 'object' ? parsedOptions : {};
} catch (error) {
    chatOptions = {};
}

const numberOfLoadingDots = Math.max(
    1,
    Math.min(10, Number(chatOptions.numberOfLoadingDots ?? props.numberOfLoadingDots) || 3),
);

/** @type {Record<string, string>} Translated labels provided by TYPO3. */
let labels = {};
try {
    const parsedLabels = JSON.parse(props.labelsJson || '{}');
    labels = parsedLabels && typeof parsedLabels === 'object' ? parsedLabels : {};
} catch (error) {
    labels = {};
}

/** @type {Array<Record<string, any>>} UI component definitions supplied by the host application. */
let uiComponents = [];
try {
    const parsedComponents = JSON.parse(props.uiComponentsJson || '[]');
    uiComponents = Array.isArray(parsedComponents) ? parsedComponents : Object.values(parsedComponents || {});
} catch (error) {
    uiComponents = [];
}

const componentDefinitions = Object.fromEntries(
    uiComponents
        .filter((definition) => definition && typeof definition.identifier === 'string')
        .map((definition) => [definition.identifier, definition]),
);

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
 * Renders Markdown links as their formatted text instead of active anchors.
 * Registered UI link components are rendered separately and are not affected.
 */
const markdownRenderer = new marked.Renderer();
markdownRenderer.link = ({ tokens }) => markdownRenderer.parser.parseInline(tokens);

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
    DOMPurify.sanitize(marked.parse(content || '', {
        gfm: true,
        breaks: true,
        renderer: markdownRenderer,
    }), sanitizeOptions),
);

/**
 * Escapes a value before it is inserted into a configured HTML template.
 *
 * @param {unknown} value Value to escape.
 * @return {string} Escaped text.
 */
const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

/**
 * Keeps only valid CSS class tokens from the trusted component configuration.
 *
 * @param {string} value CSS class list.
 * @return {string} Normalized class list.
 */
const normalizeCssClass = (value) => String(value || '')
    .split(/\s+/)
    .filter((className) => /^[A-Za-z_][A-Za-z0-9_-]*$/.test(className))
    .join(' ');

/**
 * Resolves a dot-separated value from component or context data.
 *
 * @param {Record<string, any>} values Values.
 * @param {string} path Value path.
 * @return {unknown} Resolved value.
 */
const resolveValue = (values, path) => path.split('.').reduce((value, segment) => (
    value && typeof value === 'object' ? value[segment] : undefined
), values);

/**
 * Interpolates a configured component template, including simple repeated blocks.
 *
 * @param {string} template Trusted host-provided template.
 * @param {Record<string, any>} values Component and runtime context values.
 * @param {string[]} allowedPlaceholders Allowed placeholder paths.
 * @return {string} Interpolated template.
 */
const interpolateTemplate = (template, values, allowedPlaceholders = []) => {
    let result = template.replace(/{{#([A-Za-z0-9_.-]+)}}([\s\S]*?){{\/\1}}/g, (match, path, body) => {
        if (allowedPlaceholders.length > 0 && !allowedPlaceholders.includes(path)) return '';
        const list = resolveValue(values, path);
        if (!Array.isArray(list)) {
            return list ? interpolateTemplate(body, values, allowedPlaceholders) : '';
        }
        return list.map((item) => interpolateTemplate(body, { ...values, ...(item || {}) }, allowedPlaceholders)).join('');
    });

    return result.replace(/{{\s*([A-Za-z0-9_.-]+)\s*}}/g, (match, path) => (
        allowedPlaceholders.length > 0 && !allowedPlaceholders.includes(path)
            ? match
            : escapeHtml(resolveValue(values, path))
    ));
};

/**
 * Sanitizes a configured component template without allowing the AI to expand the HTML vocabulary.
 *
 * @param {string} html Component HTML.
 * @return {string} Sanitized component HTML.
 */
const sanitizeComponentTemplate = (html) => {
    const template = document.createElement('template');
    template.innerHTML = html;
    const tags = new Set(defaultSanitizeOptions.ALLOWED_TAGS);
    const attributes = new Set(defaultSanitizeOptions.ALLOWED_ATTR);
    template.content.querySelectorAll('*').forEach((element) => {
        const tagName = element.tagName.toLowerCase();
        if (!['script', 'style', 'iframe', 'object', 'embed'].includes(tagName)) {
            tags.add(tagName);
        }
        element.getAttributeNames()
            .filter((attribute) => !attribute.toLowerCase().startsWith('on'))
            .forEach((attribute) => attributes.add(attribute));
    });

    return DOMPurify.sanitize(html, {
        ...sanitizeOptions,
        ALLOWED_TAGS: [...tags],
        ALLOWED_ATTR: [...attributes, 'data-ai-action', 'data-ai-value'],
    });
};

/**
 * Renders a complete UI block as configured HTML.
 *
 * @param {{identifier: string, id: string, data: Record<string, any>}} block UI block.
 * @return {string} Rendered HTML.
 */
const renderComponent = (block) => {
    const definition = componentDefinitions[block.identifier];
    if (!definition || typeof definition.template !== 'string') return '';

    const values = {
        ...block.data,
        id: block.id,
        action: block.data.action || (definition.actions?.length === 1 ? definition.actions[0].identifier : ''),
        actions: (definition.actions || []).map((action) => ({
            identifier: action.identifier,
            label: action.label || action.identifier,
            promptTemplate: action.promptTemplate,
            placeholders: action.placeholders || [],
            type: action.type || 'prompt',
            url: action.url || '',
        })),
    };
    const html = sanitizeComponentTemplate(interpolateTemplate(definition.template, values));
    const cssClass = normalizeCssClass(definition.cssClass);
    const tag = definition.renderMode === 'inline' ? 'span' : 'div';
    return `<${tag} class="ai-ui-component${cssClass ? ` ${escapeHtml(cssClass)}` : ''}" data-ai-component="${escapeHtml(block.identifier)}" data-ai-component-id="${escapeHtml(block.id)}" data-ai-component-data="${escapeHtml(JSON.stringify(values))}">${html}</${tag}>`;
};

/**
 * Parses the streamed assistant text into Markdown and self-contained UI JSON markers.
 *
 * @param {string} content Accumulated assistant response.
 * @return {string} Rendered response HTML.
 */
const renderResponse = (content) => {
    let html = '';
    let markdown = '';
    const inlineComponents = new Map();
    let offset = 0;
    const usedIds = new Set();
    let marker;
    const markerPattern = /@@UI_COMPONENT[ \t]*/g;

    const flushMarkdown = () => {
        if (markdown === '') return;
        let rendered = renderMarkdown(markdown);
        inlineComponents.forEach((component, token) => {
            rendered = rendered.replaceAll(token, component);
        });
        html += rendered;
        markdown = '';
        inlineComponents.clear();
    };

    while ((marker = markerPattern.exec(content)) !== null) {
        markdown += content.slice(offset, marker.index);
        const jsonStart = markerPattern.lastIndex;
        if (content[jsonStart] !== '{') {
            offset = content.length;
            break;
        }

        let depth = 0;
        let inString = false;
        let escaped = false;
        let jsonEnd = -1;
        for (let index = jsonStart; index < content.length; index += 1) {
            const character = content[index];
            if (inString) {
                if (escaped) escaped = false;
                else if (character === '\\') escaped = true;
                else if (character === '"') inString = false;
                continue;
            }
            if (character === '"') inString = true;
            else if (character === '{') depth += 1;
            else if (character === '}' && --depth === 0) { jsonEnd = index + 1; break; }
        }

        if (jsonEnd < 0) {
            offset = content.length;
            break;
        }

        let data;
        try { data = JSON.parse(content.slice(jsonStart, jsonEnd)); } catch (error) {
            markdown += content.slice(offset, jsonEnd);
            offset = jsonEnd;
            markerPattern.lastIndex = jsonEnd;
            continue;
        }
        if (!data || typeof data !== 'object' || Array.isArray(data)) {
            markdown += content.slice(offset, jsonEnd);
            offset = jsonEnd;
            markerPattern.lastIndex = jsonEnd;
            continue;
        }

        const componentIdentifier = typeof data.type === 'string' ? data.type : '';
        if (componentIdentifier === '') {
            markdown += content.slice(offset, jsonEnd);
            offset = jsonEnd;
            markerPattern.lastIndex = jsonEnd;
            continue;
        }
        const isInline = componentDefinitions[componentIdentifier]?.renderMode === 'inline';
        if (!isInline) flushMarkdown();
        const baseId = String(data.id || `${componentIdentifier}-${marker.index}`);
        let id = baseId;
        let suffix = 2;
        while (usedIds.has(id)) id = `${baseId}-${suffix++}`;
        usedIds.add(id);
        const componentData = data.data && typeof data.data === 'object' ? data.data : {};
        delete data.id;
        delete data.type;
        delete data.data;
        Object.assign(componentData, data);
        const component = renderComponent({ identifier: componentIdentifier, id, data: componentData });
        if (isInline) {
            const token = `AIUICOMPONENT${usedIds.size}TOKEN`;
            markdown += token;
            inlineComponents.set(token, component);
        } else {
            flushMarkdown();
            html += component;
        }
        offset = jsonEnd;
        markerPattern.lastIndex = jsonEnd;
    }
    markdown += content.slice(offset);
    flushMarkdown();
    return html;
};

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
    const initialContent = String(props.initialMessage || '').trim();
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
 * @param {{showUserMessage?: boolean, fallbackMessage?: string, source?: string}} options Request options.
 * @return {Promise<void>} Resolves when the stream is complete.
 */
const send = async (query, directInteraction = '', options = {}) => {
    if (loading.value) return;

    if (options.showUserMessage !== false) {
        messages.value.push({
            id: crypto.randomUUID(),
            role: 'user',
            source: options.source || undefined,
            html: renderMarkdown(query),
        });
    }
    loading.value = true;
    statusMessage.value = '';

    const formData = new FormData();
    formData.set(`${parameterPrefix}[query]`, query);
    formData.set(`${parameterPrefix}[assistantProfile]`, String(props.assistantProfile));
    formData.set(`${parameterPrefix}[chatIdentifier]`, props.chatIdentifier);
    formData.set(`${parameterPrefix}[requestToken]`, props.requestToken);
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
            messages.value[messageIndex].html = renderResponse(answer);
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
        emit('error', error);
        if (props.errorHandling === 'silent') {
            messages.value[messageIndex].html = '';
            statusMessage.value = '';
            return;
        }
        const errorMessage = error instanceof Error
            ? error.message
            : (props.errorMessage || labels.errorMessage || 'The answer could not be loaded.');
        if (options.showUserMessage === false && options.fallbackMessage) {
            messages.value[messageIndex].html = renderResponse(options.fallbackMessage);
        } else {
            messages.value[messageIndex].html = renderMarkdown(errorMessage);
            statusMessage.value = errorMessage;
        }
    } finally {
        abortController = null;
        loading.value = false;
    }
};

/**
 * Handles actions declared by a configured component template.
 *
 * The resulting value is sent through the same path as a manually entered
 * prompt. Component templates can therefore only provide an input shortcut;
 * they do not introduce a second request or execution mechanism.
 *
 * @param {MouseEvent} event Click event.
 * @return {void}
 */
const handleComponentClick = (event) => {
    const target = event.target instanceof Element ? event.target.closest('[data-ai-action]') : null;
    if (!target || loading.value) return;

    const container = target.closest('[data-ai-component]');
    if (!container) return;

    const definition = componentDefinitions[container.dataset.aiComponent];
    const actionIdentifier = target.dataset.aiAction || '';
    const action = definition?.actions?.find((item) => item.identifier === actionIdentifier);
    if (!action || typeof action.promptTemplate !== 'string') return;

    event.preventDefault();
    let values = {};
    try {
        values = JSON.parse(container.dataset.aiComponentData || '{}');
    } catch (error) {
        values = {};
    }
    values.value = target.dataset.aiValue || values.value || '';
    values.context = chatOptions.context || {};

    const prompt = interpolateTemplate(action.promptTemplate, values, action.placeholders || []).trim();
    if (prompt !== '') {
        void send(prompt, '', { source: 'ui' });
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
        fallbackMessage: (labels.languageConfirmationFallback).replace('%s', selection.name || selection.value),
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

.btn-primary {
    background-color: var(--chat-box-color-primary);
    border-color: var(--chat-box-color-primary);
}

.btn-primary:hover,
.btn-primary:active {
    background-color: var(--chat-box-color-secondary);
    border-color: var(--chat-box-color-secondary);
}

@layer default, instance;
@layer default {

    .chat-box {
        max-width: 700px;
        margin: 0 auto;
        --chat-box-color-primary: var(--bs-primary, #205eb5);
        --chat-box-color-secondary: var(--bs-secondary, #d6d6d6);

        &-consent,
        &-language {
            margin-bottom: 1rem;
        }
    }
}


</style>
