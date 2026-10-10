<template>
    <section
        :class="['aiassistant-chat', 'chat-box', 'container', { 'aiassistant-chat--default-styles': defaultStylesEnabled }]"
        role="region"
        :aria-label="labels.chatLabel || chatLabel"
        @click="handleComponentClick"
    >
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
 * @property {string} requestToken Signed frontend request token.
 * @property {string|number} startTimestamp Session reset timestamp.
 * @property {string} settingsJson Serialized runtime settings.
 * @property {string} chatOptionsJson Serialized chat options.
 * @property {string} labelsJson Serialized translated labels.
 * @property {string} uiComponentsJson Serialized UI component definitions.
 * @property {string} errorHandling Frontend error handling mode.
 * @property {boolean|string|number} includeDefaultStyles Whether built-in component styles apply to this instance.
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
    requestToken: { type: String, default: '' },
    startTimestamp: { type: [String, Number], default: 0 },

    // Runtime configuration.
    settingsJson: { type: String, default: '{}' },
    chatOptionsJson: { type: String, default: '{}' },
    labelsJson: { type: String, default: '{}' },
    uiComponentsJson: { type: String, default: '[]' },
    errorHandling: { type: String, default: 'default' },
    includeDefaultStyles: { type: [String, Number, Boolean], default: true },
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

const emit = defineEmits(['error']);

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

/** @type {import('vue').ComputedRef<boolean>} */
const defaultStylesEnabled = computed(() => toBoolean(props.includeDefaultStyles));

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
    const tag = definition.inline ? 'span' : 'div';
    return `<${tag} class="ai-ui-component${cssClass ? ` ${escapeHtml(cssClass)}` : ''}" data-ai-component="${escapeHtml(block.identifier)}" data-ai-component-id="${escapeHtml(block.id)}" data-ai-component-data="${escapeHtml(JSON.stringify(values))}">${html}</${tag}>`;
};

/**
 * Renders flat inline UI components, primarily inline links.
 *
 * @param {string} content Markdown content.
 * @param {Set<string>} usedIds Used component IDs.
 * @return {string} Rendered content.
 */
const renderInlineComponents = (content, usedIds) => {
    content = content.replace(
        /\[[^\]]*\]\(\s*:::ui[ \t]+link[ \t]*(\{[\s\S]*?\})[ \t]*\)/g,
        ':::ui link $1',
    );
    content = content.replace(
        /\[[^\]]*\]\(\s*(:::ui[ \t]+[A-Za-z0-9._-]+[ \t]*(\{"[^\n{}]*"\})(?:(?::){1,3})?[ \t]*>?)\s*\)/g,
        '$1',
    );
    const pattern = /:::ui[ \t]+([A-Za-z0-9._-]+)[ \t]*(\{"[^\n{}]*"\})(?:(?::){1,3})?[ \t]*>?/g;
    const replacements = [];
    let markdown = content;
    let match;
    while ((match = pattern.exec(content)) !== null) {
        try {
            const data = JSON.parse(match[2]);
            const baseId = String(data.id || `${match[1]}-${match.index}`);
            let id = baseId;
            let suffix = 2;
            while (usedIds.has(id)) id = `${baseId}-${suffix++}`;
            usedIds.add(id);
            delete data.id;
            const definition = componentDefinitions[match[1]];
            replacements.push({
                token: `AIUIINLINE${replacements.length}TOKEN`,
                html: renderComponent({ identifier: match[1], id, data }),
                raw: match[0],
                inline: definition?.inline === true,
            });
        } catch (error) {
            replacements.push({ token: '', html: '', raw: match[0] });
        }
    }
    for (const replacement of replacements) {
        if (replacement.token !== '') {
            const token = replacement.inline
                ? replacement.token
                : `\n\n${replacement.token}\n\n`;
            markdown = markdown.replace(replacement.raw, token);
        }
    }
    let html = renderMarkdown(markdown);
    for (const replacement of replacements) {
        if (replacement.token !== '') {
            if (replacement.inline) {
                html = html.replaceAll(replacement.token, replacement.html);
            } else {
                html = html.replaceAll(`<p>${replacement.token}</p>`, replacement.html);
                html = html.replaceAll(replacement.token, replacement.html);
            }
        }
    }
    return html;
};

/**
 * Parses the streamed assistant text into Markdown and UI blocks.
 *
 * @param {string} content Accumulated assistant response.
 * @return {string} Rendered response HTML.
 */
const renderResponse = (content) => {
    const pattern = /^:::ui[ \t]+([A-Za-z0-9._-]+)[ \t]*\n([\s\S]*?)^:::[ \t]*(?:\n|$)/gm;
    let html = '';
    let offset = 0;
    const usedIds = new Set();
    let match;
    while ((match = pattern.exec(content)) !== null) {
        html += renderInlineComponents(content.slice(offset, match.index), usedIds);
        try {
            const data = JSON.parse(match[2].trim());
            if (data && typeof data === 'object') {
                const baseId = String(data.id || `${match[1]}-${match.index}`);
                let id = baseId;
                let suffix = 2;
                while (usedIds.has(id)) id = `${baseId}-${suffix++}`;
                usedIds.add(id);
                delete data.id;
                html += renderComponent({ identifier: match[1], id, data });
            }
        } catch (error) {
            html += renderInlineComponents(match[0], usedIds);
        }
        offset = match.index + match[0].length;
    }
    html += renderInlineComponents(content.slice(offset).replace(/^:::ui[ \t]+[A-Za-z0-9._-]+[ \t]*\n[\s\S]*$/m, ''), usedIds);
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
.aiassistant-chat--default-styles {
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
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-top: 0.75rem;
        padding: 0.75rem;
        border: 1px solid rgba(0, 0, 0, 0.15);
        border-radius: 0.5rem;

        > button,
        .chat-box-language-custom,
        .chat-box-language-custom input,
        .chat-box-language-custom button {
            width: 100%;
        }

        .chat-box-language-custom {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
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
        align-items: flex-end;
        margin-top: 1rem;
        border: 1px solid var(--chat-box-color-secondary);
        border-radius: 0.5rem;

        textarea,
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

</style>
