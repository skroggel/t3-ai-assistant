/**
 * Shared state and behavior for every AI Assistant chat presentation.
 *
 * This module deliberately contains no chat markup. The regular Vue component,
 * the framework-independent custom element and customer-owned Vue templates all
 * use this composable as their single source of truth for conversation state,
 * request handling and response rendering.
 *
 * @module useAiAssistantChat
 */
import DOMPurify from 'dompurify';
import { marked } from 'marked';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

/**
 * Secure defaults for assistant-generated Markdown.
 *
 * The list intentionally contains only the elements required by the chat
 * renderer. Host-provided options may extend or override these defaults, but
 * the response still passes through DOMPurify before it reaches the DOM.
 *
 * @type {import('dompurify').Config}
 */
const defaultSanitizeOptions = {
    ALLOWED_TAGS: [
        'a', 'blockquote', 'br', 'code', 'del', 'em', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'hr', 'li', 'ol', 'p', 'pre', 'strong', 'table', 'tbody', 'td', 'th', 'thead', 'tr', 'ul',
    ],
    ALLOWED_ATTR: ['href', 'title'],
};

/**
 * Parses a serialized object while keeping malformed frontend configuration harmless.
 *
 * @param {string} value Serialized object.
 * @param {Record<string, any>} fallback Fallback object.
 * @return {Record<string, any>} Parsed object or fallback.
 */
const parseObject = (value, fallback = {}) => {
    try {
        const parsed = JSON.parse(value || '{}');
        return parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? parsed : fallback;
    } catch (error) {
        return fallback;
    }
};

/**
 * Parses serialized UI component definitions.
 *
 * @param {string} value Serialized array or object.
 * @return {Array<Record<string, any>>} Parsed definitions.
 */
const parseArray = (value) => {
    try {
        const parsed = JSON.parse(value || '[]');
        return Array.isArray(parsed) ? parsed : Object.values(parsed || {});
    } catch (error) {
        return [];
    }
};

/**
 * Provides the complete assistant conversation state and transport behavior.
 * Rendering components only need to provide markup and optional DOM callbacks.
 *
 * @param {Record<string, any>} props Chat props.
 * @param {{emit?: Function, onConsentAccepted?: Function, onConsentRequired?: Function, onMessagesUpdated?: Function}} options Integration callbacks.
 * @return {Record<string, any>} Chat state and actions.
 */
export const useAiAssistantChat = (props, options = {}) => {
    // `emit` is optional because a customer-owned composable consumer may not
    // be a Vue component with an error event. The built-in component supplies it.
    const emit = options.emit || (() => {});

    // TYPO3 serializes the runtime configuration into HTML attributes. Parse
    // each payload once so all later actions work on the same normalized data.
    /** @type {Record<string, any>} Normalized frontend chat options. */
    const chatOptions = parseObject(props.chatOptionsJson);

    /** @type {Record<string, string>} Translated labels provided by TYPO3. */
    const labels = parseObject(props.labelsJson);

    /** @type {Array<Record<string, any>>} UI component definitions supplied by the host application. */
    const uiComponents = parseArray(props.uiComponentsJson)
        .filter((definition) => definition && typeof definition === 'object');

    /**
     * Build lookup objects with identifiers and types as keys.
     *
     * AI markers may refer to a UI definition by its explicit identifier or by
     * its type. Keeping both indexes avoids making the transport or renderer
     * aware of the definition format.
     */
    const componentDefinitions = Object.fromEntries(
        uiComponents
            .filter((definition) => typeof definition.identifier === 'string')
            .map((definition) => [definition.identifier, definition]),
    );
    const componentDefinitionsByType = Object.fromEntries(
        uiComponents
            .filter((definition) => typeof definition.type === 'string')
            .map((definition) => [definition.type, definition]),
    );

    /** @type {Record<string, any>} Request options for the shared transport. */
    const requestOptions = chatOptions.request || {};

    /** @type {Record<string, any>} Accessibility options from runtime settings. */
    const accessibilityOptions = chatOptions.accessibility || {};

    /** @type {string} Extbase plugin parameter namespace. */
    const parameterPrefix = props.parameterPrefix || 'tx_aiassistant_chat';

    /** @type {string} Effective response language name. */
    const effectiveResponseLanguage = props.responseLanguage || chatOptions.language?.responseLanguage || '';

    /** @type {string} Effective response language code. */
    const effectiveLanguageCode = props.languageCode || chatOptions.language?.languageCode || '';

    /**
     * Converts HTML/Fluid boolean values into real booleans.
     *
     * @param {unknown} value Value received from Vue or an HTML attribute.
     * @return {boolean} Normalized boolean value.
     */
    const toBoolean = (value) => {
        if (typeof value === 'string') {
            return value.trim().toLowerCase() === 'true'
                || value.trim() === '1'
                || value.trim() === '';
        }

        return value === true || value === 1;
    };


    /** @type {number} number of loading dots */
    const configuredLoadingDots = Number(chatOptions.numberOfLoadingDots ?? props.numberOfLoadingDots);
    const numberOfLoadingDots = Math.max(
        0,
        Math.min(10, Number.isFinite(configuredLoadingDots) ? configuredLoadingDots : 3),
    );

    /** @type {number} number of initialMessageDelay */
    const initialMessageDelay = Math.max(
        0,
        Math.min(500, Number(chatOptions.initialMessageDelay ?? props.initialMessageDelay) || 0),
    );

    /** @type {number} number of responseMessageDelay */
    const responseMessageDelay = Math.max(
        0,
        Math.min(500, Number(chatOptions.responseMessageDelay ?? props.responseMessageDelay) || 0),
    );

    /** @type {number} number of renderLagCharacters */
    const renderLagCharacters = Math.max(
        0,
        Number(chatOptions.renderLagCharacters ?? props.renderLagCharacters) || 0,
    );

    /** @type {number} number of renderChunkCharacters */
    const renderChunkCharacters = Math.max(
        1,
        Math.min(500, Number(chatOptions.renderChunkCharacters ?? props.renderChunkCharacters) || 15),
    );

    // Computed values intentionally read both explicit props and nested runtime
    // settings. This keeps values generated by Fluid and values passed by Vue
    // consumers interchangeable.
    /** @type {import('vue').ComputedRef<boolean>} */
    const requireConsent = computed(() => toBoolean(props.requireConsent));

    /** @type {import('vue').ComputedRef<boolean>} */
    const showLanguageSelector = computed(() => toBoolean(props.showLanguageSelector));

    /** @type {import('vue').ComputedRef<boolean>} */
    const plainLanguage = computed(() => (
        toBoolean(props.plainLanguage) || toBoolean(accessibilityOptions.plainLanguage)
    ));

    /** @type {import('vue').ComputedRef<boolean>} */
    const streamResponse = computed(() => toBoolean(props.streamResponse));

    /** @type {import('vue').ComputedRef<string>} */
    const loadingHtml = computed(() => props.loadingHtml || chatOptions.loadingHtml || '');

    /** @type {import('vue').ComputedRef<Array|string>} */
    const loadingMessages = computed(() => {
        if (Array.isArray(props.loadingMessages) && props.loadingMessages.length > 0) {
            return props.loadingMessages;
        }
        if (typeof props.loadingMessages === 'string' && props.loadingMessages.trim() !== '') {
            return props.loadingMessages;
        }
        return chatOptions.loadingMessages || [];
    });

    /** @type {import('vue').ComputedRef<number|string>} */
    const loadingMessagesTimeout = computed(() => (
        chatOptions.loadingMessagesTimeout ?? props.loadingMessagesTimeout
    ));

    // The following refs are the public conversation state. Presentational
    // components can render them directly without knowing anything about
    // transport details or serialized TYPO3 configuration.
    /** @type {import('vue').Ref<Array<Record<string, any>>>} Chat messages. */
    const messages = ref([]);

    /** @type {import('vue').Ref<boolean>} Whether a request or initial message is active. */
    const loading = ref(false);

    /** @type {import('vue').Ref<boolean>} Whether the user has accepted consent. */
    const consentGiven = ref(!requireConsent.value);

    /** @type {import('vue').Ref<string>} Accessible status message. */
    const statusMessage = ref('');

    /** @type {import('vue').Ref<string>} Selected response language code. */
    const selectedLanguageCode = ref('');

    /** @type {AbortController|null} Active request cancellation handle. */
    let abortController = null;

    /** @type {boolean} Prevents the initial assistant message from being added twice. */
    let initialMessageAdded = false;
    /** @type {boolean} Prevents an automatic query from being sent twice. */
    let autoQueryStarted = false;

    // Sanitization is composed in this order so the default security policy is
    // always present, runtime configuration can extend it, and explicit Vue
    // props have the final say for an embedding application.
    /** @type {import('dompurify').Config} */
    const sanitizeOptions = {
        ...defaultSanitizeOptions,
        ...(chatOptions.sanitizeOptions && typeof chatOptions.sanitizeOptions === 'object'
            ? chatOptions.sanitizeOptions
            : {}),
        ...props.sanitizeOptions,
    };

    /** Renderer instance reused for all messages to keep Markdown output stable. */
    const markdownRenderer = new marked.Renderer();


    /**
     * Waits for the next browser paint.
     *
     * @return {Promise<void>} Resolves after the next animation frame.
     */
    const waitForPaint = () => new Promise((resolve) => {
        // Streaming updates are rendered in small visual steps. Waiting for a
        // paint prevents a long response from being committed in one frame.
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
    const wait = (milliseconds) => new Promise((resolve) => setTimeout(resolve, milliseconds));


    /**
     * Gives a custom renderer an opportunity to update focus or scrolling after a message update.
     *
     * @return {Promise<void>} Resolves after the Vue update.
     */
    const notifyMessagesUpdated = async () => {
        await nextTick();
        // Focus and scroll behavior depends on the host markup. The composable
        // therefore exposes a callback instead of querying a fixed DOM shape.
        if (typeof options.onMessagesUpdated === 'function') {
            await options.onMessagesUpdated(messages.value);
        }
    };


    /**
     * Adds the legacy external-link behavior after sanitization.
     *
     * @param {string} html Sanitized HTML.
     * @return {string} Enhanced HTML.
     */
    const enhanceLinks = (html) => {
        // DOMPurify removes unsafe markup, but it does not add the legacy
        // external-link behavior required by the existing chat UI.
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
        // Markdown is converted and sanitized together before the result is
        // assigned to `v-html` by the message-list component.
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
     * Keeps only valid CSS class tokens from trusted component configuration.
     *
     * @param {string} value CSS class list.
     * @return {string} Normalized class list.
     */
    const normalizeCssClass = (value) => String(value || '')
        .split(/\s+/)
        .filter((className) => /^[A-Za-z_][A-Za-z0-9_-]*$/.test(className))
        .join(' ');


    /**
     * Validates a configured custom element name.
     *
     * @param {unknown} value Configured element name.
     * @return {string} Valid custom element name or an empty string.
     */
    const normalizeCustomElementName = (value) => {
        const elementName = String(value || '').trim().toLowerCase();
        return /^[a-z][a-z0-9]*-[a-z0-9-]+$/.test(elementName) ? elementName : '';
    };


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
        // Component definitions support simple repeated blocks first, followed
        // by scalar placeholders. The allow-list prevents an action from
        // reading values that were not explicitly declared by the definition.
        const result = template.replace(/{{#([A-Za-z0-9_.-]+)}}([\s\S]*?){{\/\1}}/g, (match, path, body) => {
            if (allowedPlaceholders.length > 0 && !allowedPlaceholders.includes(path)) return '';
            const list = resolveValue(values, path);
            if (!Array.isArray(list)) {
                return list ? interpolateTemplate(body, values, allowedPlaceholders) : '';
            }
            return list.map((item) => interpolateTemplate(
                body,
                { ...values, ...(item || {}) },
                allowedPlaceholders,
            )).join('');
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
        // UI component templates are trusted configuration, but their attribute
        // vocabulary is discovered and sanitized here so event-handler
        // attributes and active-content elements cannot enter the response.
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
     * @return {string} Rendered UI component.
     */
    const renderComponent = (block) => {
        const definition = componentDefinitions[block.identifier]
            || componentDefinitionsByType[block.type];
        if (!definition || typeof definition.template !== 'string') return '';

        // Add action metadata to the values available to the host template.
        // Actions remain declarative and are executed through `send()` below.
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
        const customElement = definition.renderMode === 'customElement'
            ? normalizeCustomElementName(definition.elementName)
            : '';
        const tag = customElement || (definition.renderMode === 'inline' ? 'span' : 'div');
        // Store the original block data on the wrapper. Click handling can then
        // reconstruct the action context without introducing another transport.
        return `<${tag} class="ai-ui-component${cssClass ? ` ${escapeHtml(cssClass)}` : ''}" data-ai-component="${escapeHtml(block.identifier)}" data-ai-component-id="${escapeHtml(block.id)}" data-ai-component-data="${escapeHtml(JSON.stringify(values))}">${html}</${tag}>`;
    };


    /**
     * Parses the streamed assistant text into Markdown and self-contained UI JSON markers.
     *
     * @param {string} content Accumulated assistant response.
     * @return {string} Rendered response HTML.
     */
    const renderResponse = (content) => {
        // Markdown and UI markers are accumulated separately. This allows an
        // inline component to be inserted into already-rendered Markdown while
        // block components stay outside the surrounding paragraph structure.
        let html = '';
        let markdown = '';
        const inlineComponents = new Map();
        let offset = 0;
        const usedIds = new Set();
        let marker;
        const markerPattern = /@@UI_COMPONENT[ \t]*/g;

        const flushMarkdown = () => {
            if (markdown === '') return;
            // Render only the Markdown collected since the previous marker.
            // Inline component tokens are replaced after sanitization, so their
            // configured HTML is not interpreted as assistant Markdown.
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
                // A partial marker can occur during streaming. Leave it in the
                // Markdown buffer until the next forced render pass.
                offset = content.length;
                break;
            }

            // Find the matching closing brace without relying on a greedy
            // regular expression; UI data may contain nested objects or braces
            // inside quoted strings.
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
                else if (character === '}' && --depth === 0) {
                    jsonEnd = index + 1;
                    break;
                }
            }
            if (jsonEnd < 0) {
                // The marker is incomplete and will be completed by a later SSE
                // snapshot. Do not discard the unfinished response.
                offset = content.length;
                break;
            }

            let data;
            try {
                data = JSON.parse(content.slice(jsonStart, jsonEnd));
            } catch (error) {
                // Invalid marker JSON is treated as literal assistant text rather
                // than making the complete response unusable.
                markdown += content.slice(offset, jsonEnd);
                offset = jsonEnd;
                markerPattern.lastIndex = jsonEnd;
                continue;
            }
            if (!data || typeof data !== 'object' || Array.isArray(data)) {
                // Only object markers can describe UI components. Preserve any
                // other JSON-shaped content as ordinary Markdown.
                markdown += content.slice(offset, jsonEnd);
                offset = jsonEnd;
                markerPattern.lastIndex = jsonEnd;
                continue;
            }

            const componentType = typeof data.type === 'string' ? data.type : '';
            const componentIdentifier = typeof data.identifier === 'string' ? data.identifier : componentType;
            if (componentType === '' || componentIdentifier === '') {
                markdown += content.slice(offset, jsonEnd);
                offset = jsonEnd;
                markerPattern.lastIndex = jsonEnd;
                continue;
            }
            const definition = componentDefinitions[componentIdentifier]
                || componentDefinitionsByType[componentType];
            const isInline = definition?.renderMode === 'inline';
            if (!isInline) flushMarkdown();

            // IDs are supplied by the assistant when possible. Duplicate IDs in
            // one answer receive a deterministic suffix for stable Vue keys and
            // action lookup.
            const baseId = String(data.id || `${componentIdentifier}-${marker.index}`);
            let id = baseId;
            let suffix = 2;
            while (usedIds.has(id)) id = `${baseId}-${suffix++}`;
            usedIds.add(id);
            const componentData = data.data && typeof data.data === 'object' ? data.data : {};
            // The marker envelope is metadata, not component data. Move any
            // remaining marker fields into the data object before rendering.
            delete data.id;
            delete data.type;
            delete data.identifier;
            delete data.data;
            Object.assign(componentData, data);
            const component = renderComponent({
                type: componentType,
                identifier: componentIdentifier,
                id,
                data: componentData,
            });
            if (isInline) {
                // Inline components are temporarily represented by a token so
                // Markdown can place them at the requested position safely.
                const token = `AIUICOMPONENT${usedIds.size}TOKEN`;
                markdown += token;
                inlineComponents.set(token, component);
            } else {
                // Block components terminate the current Markdown fragment and
                // are appended as their own top-level HTML block.
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
     * Adds the configured initial assistant message once and pseudo-streams it.
     *
     * @return {Promise<void>} Resolves when the initial message is complete.
     */
    const addInitialMessage = async () => {
        const initialContent = String(props.initialMessage || '').trim();
        if (initialMessageAdded || initialContent === '') return;
        initialMessageAdded = true;

        // The initial message follows the same message shape as a streamed
        // assistant answer, which lets every renderer use one message list.
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
            // Pseudo-stream the configured initial text so it has the same
            // visual behavior as a server-streamed answer.
            content += part;
            messages.value[messageIndex].html = renderMarkdown(content);
            await wait(initialMessageDelay);
            await notifyMessagesUpdated();
            await waitForPaint();
        }

        loading.value = false;
        messages.value[messageIndex].typing = false;
        await notifyMessagesUpdated();
    };


    /**
     * Accepts consent, starts the initial message and delegates focus to the renderer.
     *
     * @return {Promise<void>} Resolves after the chat becomes visible.
     */
    const acceptConsent = async () => {
        // Consent changes the visible branch in the host component. Wait for
        // that branch to mount before adding the initial message or focusing it.
        consentGiven.value = true;
        await nextTick();
        await addInitialMessage();
        if (typeof options.onConsentAccepted === 'function') {
            await options.onConsentAccepted();
        }
    };


    /**
     * Sends one user message and streams the assistant response.
     *
     * @param {string} query User query or direct-interaction input.
     * @param {string} directInteraction Optional direct-interaction identifier.
     * @param {{showUserMessage?: boolean, fallbackMessage?: string, source?: string}} sendOptions Request options.
     * @return {Promise<void>} Resolves when the stream is complete.
     */
    const send = async (query, directInteraction = '', sendOptions = {}) => {
        if (loading.value) return;

        // UI actions and language confirmation can intentionally suppress the
        // visible user message while still using this same request pipeline.
        if (sendOptions.showUserMessage !== false) {
            messages.value.push({
                id: crypto.randomUUID(),
                role: 'user',
                source: sendOptions.source || undefined,
                html: renderMarkdown(query),
            });
        }
        loading.value = true;
        statusMessage.value = '';

        // The backend expects all chat fields under one Extbase namespace. The
        // namespace is shared by SSE and JSON requests so the response mode is
        // the only transport difference.
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
            let renderedAnswer = '';
            const transport = new Transport();
            abortController = new AbortController();
            const renderSnapshot = async (force = false) => {
                // Keep a configurable character lag between the received answer
                // and the visible answer. This prevents every tiny SSE update
                // from forcing an expensive Markdown render.
                const availableLength = force
                    ? answer.length
                    : Math.max(0, answer.length - renderLagCharacters);
                if (availableLength <= renderedAnswer.length) return;
                while (renderedAnswer.length < availableLength) {
                    const nextLength = Math.min(
                        availableLength,
                        renderedAnswer.length + renderChunkCharacters,
                    );
                    renderedAnswer = answer.slice(0, nextLength);
                    messages.value[messageIndex].html = renderResponse(renderedAnswer);
                    await notifyMessagesUpdated();
                    await waitForPaint();
                    if (renderedAnswer.length < availableLength) await wait(responseMessageDelay);
                }
            };

            const transportOptions = {
                signal: abortController.signal,
                method: requestOptions.method || 'POST',
                accept: requestOptions.accept || (streamResponse.value ? 'text/event-stream' : 'application/json'),
                credentials: requestOptions.credentials || 'same-origin',
            };
            if (streamResponse.value) {

                // SSE callbacks receive the complete accumulated answer. Each
                // callback advances the visual renderer only as far as allowed
                // by the configured lag and chunk size.
                await transport.streamSse(props.endpoint, formData, async (content) => {
                    answer = content;
                    await renderSnapshot();
                }, transportOptions);
            } else {

                // JSON mode waits for one complete answer and renders it once.
                const payload = await transport.requestJson(props.endpoint, formData, transportOptions);
                answer = typeof payload.answer === 'string' ? payload.answer : '';
                renderedAnswer = answer;
                // Always keep a readable Markdown fallback. A malformed or
                // unavailable configured UI component must not hide the actual
                // assistant answer.
                messages.value[messageIndex].html = renderMarkdown(answer);
                try {
                    const renderedResponse = renderResponse(answer);
                    if (renderedResponse !== '') {
                        messages.value[messageIndex].html = renderedResponse;
                    }
                } catch (renderError) {
                    emit('error', renderError);
                }
                await notifyMessagesUpdated();
            }
            if (streamResponse.value) await renderSnapshot(true);
            messages.value[messageIndex].typing = false;
            await notifyMessagesUpdated();

        } catch (error) {
            // Errors remain part of the current assistant message unless silent
            // handling was explicitly requested. The event allows a host to add
            // logging or custom error UI without changing this fallback.
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
            if (sendOptions.showUserMessage === false && sendOptions.fallbackMessage) {
                messages.value[messageIndex].html = renderResponse(sendOptions.fallbackMessage);
            } else {
                messages.value[messageIndex].html = renderMarkdown(errorMessage);
                statusMessage.value = errorMessage;
            }
            await notifyMessagesUpdated();
        } finally {
            // Clear both references even after aborts or exceptions so a later
            // interaction can start a fresh request.
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
        const definition = componentDefinitions[container.dataset.aiComponent]
            || componentDefinitionsByType[container.dataset.aiComponent];
        const action = definition?.actions?.find((item) => item.identifier === target.dataset.aiAction);
        if (!action || typeof action.promptTemplate !== 'string') return;
        event.preventDefault();

        // Action values are serialized on the component wrapper so the click
        // handler can remain independent from the component's custom markup.
        let values = {};
        try {
            values = JSON.parse(container.dataset.aiComponentData || '{}');
        } catch (error) {
            values = {};
        }
        values.value = target.dataset.aiValue || values.value || '';
        values.context = chatOptions.context || {};
        const prompt = interpolateTemplate(action.promptTemplate, values, action.placeholders || []).trim();

        // All configured actions resolve to a normal prompt and use `send()`;
        // there is deliberately no second interaction/execution mechanism.
        if (prompt !== '') void send(prompt, '', { source: 'ui' });
    };


    /**
     * Sends the existing language confirmation interaction.
     *
     * @param {{value: string, name: string}} selection Selected language.
     * @return {Promise<void>} Resolves after the confirmation response.
     */
    const confirmLanguage = async (selection) => {
        // Language selection is a direct backend interaction, not a regular
        // visible user prompt. The selected code remains on the next request.
        selectedLanguageCode.value = selection.code || '';
        await send(selection.name || selection.value, 'language_confirmation', {
            showUserMessage: false,
            fallbackMessage: (labels.languageConfirmationFallback || '').replace('%s', selection.name || selection.value),
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
     * Initializes the chat after the component is mounted.
     *
     * @return {Promise<void>} Resolves after initial content is prepared.
     */
    const initialize = async () => {
        // Consent gates both the initial message and automatic queries. Without
        // consent, the host renderer is asked to focus its consent control.
        if (!requireConsent.value) await addInitialMessage();
        if (requireConsent.value) {
            if (typeof options.onConsentRequired === 'function') await options.onConsentRequired();
        } else if (props.autoQuery.trim() !== '') {
            autoQueryStarted = true;
            await send(props.autoQuery);
        }
    };

    // Integration consumers can provide `autoQuery` after the component has
    // mounted, for example after a search-result payload has been normalized.
    // Mount-only initialization would miss that update and leave the chat
    // visible without ever sending the initial request.
    watch(() => props.autoQuery, async (query) => {
        if (
            requireConsent.value
            || autoQueryStarted
            || loading.value
            || typeof query !== 'string'
            || query.trim() === ''
        ) return;

        autoQueryStarted = true;
        await send(query);
    });

    // Register lifecycle hooks here so every presentation receives identical
    // initialization and cleanup behavior.
    onMounted(initialize);
    onBeforeUnmount(() => abortController?.abort());

    /**
     * Public contract consumed by the built-in component and customer templates.
     * Keep presentation-specific DOM refs out of this object; those belong to
     * the component that owns the markup.
     */
    return {
        messages,
        loading,
        consentGiven,
        statusMessage,
        selectedLanguageCode,
        requireConsent,
        showLanguageSelector,
        plainLanguage,
        streamResponse,
        effectiveResponseLanguage,
        effectiveLanguageCode,
        labels,
        numberOfLoadingDots,
        loadingHtml,
        loadingMessages,
        loadingMessagesTimeout,
        componentDefinitions,
        renderMarkdown,
        renderResponse,
        send,
        acceptConsent,
        confirmLanguage,
        startDirectInteraction,
        handleComponentClick,
        initialize,
    };
};

export default useAiAssistantChat;
