/**
 * @typedef {Object} AiAssistantChatBoxSelectors
 * @property {string} container Root container selector.
 * @property {string} form Form selector used for auto initialization.
 * @property {string} messages Message output container selector.
 * @property {string} input Message input selector.
 * @property {string} typing Typing indicator selector.
 * @property {string} userMessageTemplate User message template selector.
 * @property {string} botMessageTemplate Assistant message template selector.
 * @property {string} typingTemplate Typing indicator template selector.
 * @property {string} messageContent Message content selector inside the template.
 * @property {string} consent Consent message selector.
 * @property {string} chatContent Chat content selector.
 * @property {string} initButton Init button selector.
 * @property {string} status Screen-reader status selector.
 * @property {string} languageToggle Response-language status and toggle button selector.
 * @property {string} languagePanel Collapsible language controls selector.
 * @property {string} languageStatus Current response-language label selector.
 * @property {string} languageInput Optional user language input selector.
 * @property {string} languageApplyButton Free language selection apply button selector.
 * @property {string} browserLanguageButton Browser-language suggestion button selector.
 * @property {string} siteLanguageButton Website-language option button selector.
 * @property {string} directInteractionInput Direct interaction form field selector.
 */

/**
 * @typedef {Object} AiAssistantChatBoxClasses
 * @property {string} message Message element class.
 * @property {string} fromPrefix Message origin class prefix.
 * @property {string} typing Typing state class.
 * @property {string} hidden Hidden state class.
 * @property {string} link Link class.
 */

/**
 * @typedef {Object} AiAssistantChatBoxDatasetKeys
 * @property {string} initialMessage Dataset key for the initial assistant message.
 * @property {string} errorMessage Dataset key for the generic error message.
 */

/**
 * @typedef {Object} AiAssistantChatBoxRequestOptions
 * @property {string} method HTTP method used for assistant requests.
 * @property {string} accept Accept header used for assistant requests.
 */

/**
 * @typedef {Object} AiAssistantChatBoxDelays
 * @property {number} initialMessage Typing delay for the initial assistant message.
 * @property {number} responseMessage Typing delay for assistant responses.
 */

/**
 * @typedef {Object} AiAssistantChatBoxOptions
 * @property {AiAssistantChatBoxSelectors} selectors Selector configuration.
 * @property {AiAssistantChatBoxClasses} classes CSS class configuration.
 * @property {AiAssistantChatBoxDatasetKeys} datasetKeys Dataset key configuration.
 * @property {AiAssistantChatBoxRequestOptions} request Request configuration.
 * @property {AiAssistantChatBoxDelays} delays Typing delay configuration.
 * @property {Object} language Language configuration.
 * @property {string} language.responseLanguage Resolved fallback response language.
 * @property {string} language.languageCode Resolved fallback BCP 47 language tag.
 * @property {Object} accessibility Accessibility configuration.
 * @property {boolean} accessibility.plainLanguage Whether plain language is requested.
 * @property {string} userOrigin User message origin value.
 * @property {string} botOrigin Assistant message origin value.
 * @property {string} defaultEventName Default server-sent event name.
 * @property {string} doneEventName Server-sent event name that finishes the stream.
 * @property {boolean} autoScroll Whether new message output scrolls into view.
 * @property {ScrollIntoViewOptions} scrollOptions Scroll options for new messages.
 * @property {string} linkTarget Target attribute for rendered links.
 * @property {string} linkRel Rel attribute for rendered links.
 * @property {Object|Function|null} markdownParser Markdown parser used for rendering assistant output.
 * @property {Object|null} sanitizer HTML sanitizer used after markdown rendering.
 * @property {Object} markdownOptions Markdown parser options.
 * @property {Object} sanitizeOptions HTML sanitizer options.
 */

/**
 * Frontend chat box handler for the AI Assistant plugin.
 *
 * The handler initializes itself on forms using the configured form selector,
 * serializes the complete form payload and sends it to the form action.
 */
class AiAssistantChatBox {
    /**
     * Default chat box options.
     *
     * @type {AiAssistantChatBoxOptions}
     */
    static defaults = {
        selectors: {
            container: '.js-aiassistant-container',
            form: '.js-aiassistant-form',
            messages: '.js-aiassistant-messages',
            input: '.js-aiassistant-input',
            typing: '.js-aiassistant-indicator',
            userMessageTemplate: '.js-aiassistant-user-message-template',
            botMessageTemplate: '.js-aiassistant-bot-message-template',
            typingTemplate: '.js-aiassistant-typing-template',
            messageContent: '.js-aiassistant-message-content',
            consent: '.js-aiassistant-consent',
            chatContent: '.js-aiassistant-chat-content',
            initButton: '.js-aiassistant-init',
            status: '.js-aiassistant-status',
            languageToggle: '.js-aiassistant-language-toggle',
            languagePanel: '.js-aiassistant-language-panel',
            languageStatus: '.js-aiassistant-language-status',
            languageInput: '.js-aiassistant-language',
            languageApplyButton: '.js-aiassistant-language-apply',
            browserLanguageButton: '.js-aiassistant-browser-language',
            siteLanguageButton: '.js-aiassistant-site-language',
            directInteractionInput: '.js-aiassistant-direct-interaction',
        },
        classes: {
            message: 'chat-box-message',
            fromPrefix: 'from-',
            typing: 'is-typing',
            hidden: 'aiassistant-visually-hidden',
            link: 'chat-box-link',
        },
        datasetKeys: {
            initialMessage: 'initialMessage',
            errorMessage: 'errorMessage',
        },
        request: {
            method: 'POST',
            accept: 'text/event-stream',
            credentials: 'same-origin',
        },
        delays: {
            initialMessage: 22,
            responseMessage: 14,
        },
        language: {
            responseLanguage: '',
            languageCode: '',
        },
        accessibility: {
            plainLanguage: false,
        },
        userOrigin: 'user',
        botOrigin: 'bot',
        defaultEventName: 'message',
        doneEventName: 'done',
        autoScroll: true,
        scrollOptions: {
            behavior: 'smooth',
            block: 'end',
        },
        linkTarget: '_blank',
        linkRel: 'noopener noreferrer',
        markdownParser: null,
        sanitizer: null,
        markdownOptions: {
            gfm: true,
            breaks: true,
        },
        sanitizeOptions: {
            ALLOWED_TAGS: [
                'a',
                'blockquote',
                'br',
                'code',
                'del',
                'em',
                'h1',
                'h2',
                'h3',
                'h4',
                'h5',
                'h6',
                'hr',
                'li',
                'ol',
                'p',
                'pre',
                'strong',
                'table',
                'tbody',
                'td',
                'th',
                'thead',
                'tr',
                'ul',
            ],
            ALLOWED_ATTR: [
                'href',
                'title',
            ],
        },
    };

    /**
     * Creates a new chat box instance.
     *
     * @param {HTMLFormElement} form Form element used as assistant request source.
     * @param {Partial<AiAssistantChatBoxOptions>} options Optional configuration overrides.
     */
    constructor(form, options = {}) {
        /**
         * Runtime options.
         *
         * @type {AiAssistantChatBoxOptions}
         */
        const formOptions = ___AiAssistantChatBox.parseOptions(form?.dataset?.chatOptions || '');
        this.options = ___AiAssistantChatBox.mergeOptions(
            ___AiAssistantChatBox.defaults,
            ___AiAssistantChatBox.mergeOptions(options, formOptions),
        );

        /**
         * Form element used for requests.
         *
         * @type {HTMLFormElement|null}
         */
        this.form = form;

        if (!this.form) {
            return;
        }

        /**
         * Root chat box container.
         *
         * @type {Element|HTMLFormElement}
         */
        this.container = this.form.closest(this.options.selectors.container) || this.form;

        /**
         * Optional consent message shown before the chat is initialized.
         *
         * @type {Element|null}
         */
        this.consent = this.container.querySelector(this.options.selectors.consent);

        /**
         * Chat content hidden until optional consent is given.
         *
         * @type {Element|null}
         */
        this.chatContent = this.container.querySelector(this.options.selectors.chatContent);

        /**
         * Optional button used to initialize the chat after consent.
         *
         * @type {Element|null}
         */
        this.initButton = this.container.querySelector(this.options.selectors.initButton);

        /**
         * Message output container.
         *
         * @type {Element|null}
         */
        this.messagesContainer = this.container.querySelector(this.options.selectors.messages);

        /**
         * Polite live region used for controlled screen-reader announcements.
         *
         * @type {HTMLElement|null}
         */
        const status = this.container.querySelector(this.options.selectors.status);
        this.status = status instanceof HTMLElement ? status : null;

        /**
         * Message input field.
         *
         * @type {HTMLInputElement|HTMLTextAreaElement|null}
         */
        this.input = this.form.querySelector(this.options.selectors.input);

        /** @type {HTMLButtonElement|null} */
        const languageToggle = this.container.querySelector(this.options.selectors.languageToggle);
        this.languageToggle = languageToggle instanceof HTMLButtonElement ? languageToggle : null;

        /** @type {HTMLElement|null} */
        const languagePanel = this.form.querySelector(this.options.selectors.languagePanel);
        this.languagePanel = languagePanel instanceof HTMLElement ? languagePanel : null;

        /** @type {HTMLElement|null} */
        const languageStatus = this.container.querySelector(this.options.selectors.languageStatus);
        this.languageStatus = languageStatus instanceof HTMLElement ? languageStatus : null;

        /**
         * Optional free-text response language selector.
         *
         * @type {HTMLInputElement|null}
         */
        const languageInput = this.form.querySelector(this.options.selectors.languageInput);
        this.languageInput = languageInput instanceof HTMLInputElement ? languageInput : null;

        /** @type {string} */
        this.appliedLanguage = this.languageInput?.value.trim() || '';

        /** @type {HTMLButtonElement|null} */
        const languageApplyButton = this.form.querySelector(this.options.selectors.languageApplyButton);
        this.languageApplyButton = languageApplyButton instanceof HTMLButtonElement
            ? languageApplyButton
            : null;

        /** @type {HTMLButtonElement|null} */
        const browserLanguageButton = this.form.querySelector(this.options.selectors.browserLanguageButton);
        this.browserLanguageButton = browserLanguageButton instanceof HTMLButtonElement
            ? browserLanguageButton
            : null;

        /** @type {string} */
        this.browserLanguageCode = '';

        /** @type {HTMLButtonElement|null} */
        const siteLanguageButton = this.form.querySelector(this.options.selectors.siteLanguageButton);
        this.siteLanguageButton = siteLanguageButton instanceof HTMLButtonElement
            ? siteLanguageButton
            : null;

        /** @type {HTMLInputElement|null} */
        const directInteractionInput = this.form.querySelector(this.options.selectors.directInteractionInput);
        this.directInteractionInput = directInteractionInput instanceof HTMLInputElement
            ? directInteractionInput
            : null;

        /**
         * Typing indicator element.
         *
         * @type {Element|null}
         */
        this.typing = this.container.querySelector(this.options.selectors.typing);

        /**
         * User message template.
         *
         * @type {HTMLTemplateElement|null}
         */
        const userMessageTemplate = this.container.querySelector(this.options.selectors.userMessageTemplate);
        this.userMessageTemplate = userMessageTemplate instanceof HTMLTemplateElement ? userMessageTemplate : null;

        /**
         * Assistant message template.
         *
         * @type {HTMLTemplateElement|null}
         */
        const botMessageTemplate = this.container.querySelector(this.options.selectors.botMessageTemplate);
        this.botMessageTemplate = botMessageTemplate instanceof HTMLTemplateElement ? botMessageTemplate : null;

        /**
         * Typing indicator template.
         *
         * @type {HTMLTemplateElement|null}
         */
        const typingTemplate = this.container.querySelector(this.options.selectors.typingTemplate);
        this.typingTemplate = typingTemplate instanceof HTMLTemplateElement ? typingTemplate : null;

        /**
         * Request endpoint.
         *
         * @type {string}
         */
        this.streamUrl = this.form.getAttribute('action') || '';

        /**
         * Generic frontend error message.
         *
         * @type {string}
         */
        this.errorMessage = this.getDatasetValue(this.options.datasetKeys.errorMessage);

        /**
         * Optional initial assistant message.
         *
         * @type {string}
         */
        this.initialMessage = this.getDatasetValue(this.options.datasetKeys.initialMessage).trim();

        this.initializeLanguageSelector();
        this.initializeBrowserLanguageSuggestion();

        if (this.initButton) {
            this.initButton.addEventListener('click', () => this.initializeChat(true));
            return;
        }

        this.initializeChat();
    }

    /**
     * Initializes all assistant frontend forms.
     *
     * @param {ParentNode} root Root node used for form lookup.
     * @param {Partial<AiAssistantChatBoxOptions>} options Optional configuration overrides.
     * @return {Array<___AiAssistantChatBox>}
     */
    static init(root = document, options = {}) {
        const mergedOptions = ___AiAssistantChatBox.mergeOptions(___AiAssistantChatBox.defaults, options);
        const instances = [];

        root.querySelectorAll(mergedOptions.selectors.form).forEach((form) => {
            if (form instanceof HTMLFormElement) {
                instances.push(new ___AiAssistantChatBox(form, mergedOptions));
            }
        });

        return instances;
    }

    /**
     * Deep-merges option objects.
     *
     * @param {Object} defaults Default options.
     * @param {Object} overrides Override options.
     * @return {Object}
     */
    static mergeOptions(defaults, overrides) {
        const merged = { ...defaults };

        Object.entries(overrides || {}).forEach(([key, value]) => {
            if (
                value
                && typeof value === 'object'
                && !Array.isArray(value)
                && !(value instanceof Element)
            ) {
                merged[key] = ___AiAssistantChatBox.mergeOptions(defaults[key] || {}, value);
                return;
            }

            merged[key] = value;
        });

        return merged;
    }

    /**
     * Parses per-form JSON options.
     *
     * @param {string} value Serialized options.
     * @return {Object}
     */
    static parseOptions(value) {
        if (!value) {
            return {};
        }

        try {
            const options = JSON.parse(value);

            return options && typeof options === 'object' && !Array.isArray(options) ? options : {};
        } catch (error) {
            return {};
        }
    }

    /**
     * Returns a configured dataset value.
     *
     * @param {string} key Dataset key.
     * @return {string}
     */
    getDatasetValue(key) {
        return this.form?.dataset?.[key] || '';
    }

    /**
     * Initializes the chat after optional consent has been given.
     *
     * @param {boolean} focusInput Whether focus should move from a dismissed consent prompt.
     * @return {void}
     */
    initializeChat(focusInput = false) {
        if (this.initialized) {
            return;
        }

        this.initialized = true;
        if (this.consent instanceof HTMLElement) {
            this.consent.hidden = true;
        }
        if (this.chatContent instanceof HTMLElement) {
            this.chatContent.hidden = false;
        }
        this.renderInitialMessage();
        this.form.addEventListener('submit', (event) => this.handleSubmit(event));
        if (focusInput) {
            this.input?.focus();
        }
    }

    /**
     * Scrolls a message into view when automatic scrolling is enabled.
     *
     * @param {HTMLElement} message Message element.
     * @return {void}
     */
    scrollMessageIntoView(message) {
        if (!this.options.autoScroll) {
            return;
        }

        message.scrollIntoView(this.options.scrollOptions);
    }

    /**
     * Renders the initial assistant message once.
     *
     * @return {void}
     */
    renderInitialMessage() {
        if (!this.initialMessage || !this.messagesContainer) {
            return;
        }

        if (this.messagesContainer.children.length > 0) {
            return;
        }

        const message = this.addMessage('', this.options.botOrigin);
        if (!message) {
            return;
        }

        this.typeMessage(
            message,
            this.initialMessage,
            this.options.delays.initialMessage,
            () => this.announceMessage(message),
        );
    }

    /**
     * Types a message into an element.
     *
     * @param {HTMLElement} message Message element.
     * @param {string} content Message content.
     * @param {number} delay Typing delay in milliseconds.
     * @param {Function|null} onDone Callback after typing has finished.
     * @return {void}
     */
    typeMessage(message, content, delay = this.options.delays.initialMessage, onDone = null) {
        message.classList.add(this.options.classes.typing);

        let index = 0;

        const tick = () => {
            message.textContent = content.slice(0, index);
            this.scrollMessageIntoView(message);

            if (index >= content.length) {
                this.renderMessageContent(message, content);

                if (typeof onDone === 'function') {
                    onDone();
                }

                return;
            }

            index += 1;
            window.setTimeout(tick, delay);
        };

        tick();
    }

    /**
     * Adds a chat message to the output container.
     *
     * @param {string} content Message content.
     * @param {string} from Message origin.
     * @return {HTMLElement|null}
     */
    addMessage(content, from = this.options.userOrigin) {
        if (!this.messagesContainer) {
            return null;
        }

        const message = this.createMessageElement(from);

        if (!message) {
            return null;
        }

        const contentElement = this.getMessageContentElement(message);

        this.renderMessageContent(contentElement, content);
        this.messagesContainer.appendChild(message);
        this.scrollMessageIntoView(message);

        return contentElement;
    }

    /**
     * Creates a message element using the configured template.
     *
     * @param {string} from Message origin.
     * @return {HTMLElement|null}
     */
    createMessageElement(from) {
        const template = from === this.options.userOrigin
            ? this.userMessageTemplate
            : this.botMessageTemplate;

        if (!template) {
            return null;
        }

        const fragment = template.content.cloneNode(true);
        const message = fragment.firstElementChild;

        if (!(message instanceof HTMLElement)) {
            return null;
        }

        message.classList.add(`${this.options.classes.fromPrefix}${from}`);

        if (from === this.options.botOrigin) {
            const languageCode = this.resolveLanguageCode();
            if (languageCode !== '') {
                message.setAttribute('lang', languageCode);
            }
        }

        return message;
    }

    /**
     * Resolves a BCP 47 tag from the user selection or configured fallback.
     *
     * @return {string}
     */
    resolveLanguageCode() {
        const selectedLanguage = this.appliedLanguage;
        const match = selectedLanguage.match(/\(([a-zA-Z]{2,3}(?:-[a-zA-Z0-9]{2,8})*)\)$/);

        if (match) {
            return match[1];
        }
        if (/^[a-zA-Z]{2,3}(?:-[a-zA-Z0-9]{2,8})*$/.test(selectedLanguage)) {
            return selectedLanguage;
        }
        if (selectedLanguage !== '') {
            return '';
        }

        return String(this.options.language?.languageCode || '');
    }

    /**
     * Initializes the persistent response-language status and its collapsible controls.
     *
     * @return {void}
     */
    initializeLanguageSelector() {
        if (!this.languageToggle || !this.languagePanel || !this.languageInput) {
            return;
        }

        this.updateLanguageStatus();
        this.updateLanguageApplyState();
        this.initializeSiteLanguageOption();
        this.languageToggle.addEventListener('click', () => {
            this.setLanguagePanel(this.languagePanel?.hidden ?? true);
        });
        this.languageInput.addEventListener('input', () => {
            this.updateLanguageApplyState();
        });
        this.languageInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                this.applyLanguageInput();
            }
        });
        this.languageApplyButton?.addEventListener('click', () => this.applyLanguageInput());
        this.languagePanel.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                this.setLanguagePanel(false, true);
            }
        });
    }

    /**
     * Opens or closes the response-language controls.
     *
     * @param {boolean} open Whether the controls should be visible.
     * @param {boolean} restoreFocus Whether focus should return to the toggle.
     * @return {void}
     */
    setLanguagePanel(open, restoreFocus = false) {
        if (!this.languageToggle || !this.languagePanel) {
            return;
        }

        this.languagePanel.hidden = !open;
        this.languageToggle.setAttribute('aria-expanded', String(open));

        if (open) {
            const target = this.browserLanguageButton && !this.browserLanguageButton.hidden
                ? this.browserLanguageButton
                : this.languageInput;
            target?.focus();
        } else if (restoreFocus) {
            this.languageToggle.focus();
        }
    }

    /**
     * Updates the persistent status with the effective response language.
     *
     * @return {void}
     */
    updateLanguageStatus() {
        if (!this.languageStatus || !this.languageToggle) {
            return;
        }

        const language = this.appliedLanguage
            || String(this.options.language?.responseLanguage || '').trim();
        const languageName = this.resolveLanguageName(language);
        const label = this.languageToggle.dataset.label || '';
        const status = [label, languageName].filter(Boolean).join(': ');

        this.languageStatus.textContent = languageName;
        this.languageToggle.setAttribute('aria-label', status);
    }

    /**
     * Enables the apply action only when a free language value is available.
     *
     * @return {void}
     */
    updateLanguageApplyState() {
        if (this.languageApplyButton) {
            const draftLanguage = this.languageInput?.value.trim() || '';
            this.languageApplyButton.disabled = draftLanguage === '' || draftLanguage === this.appliedLanguage;
        }
    }

    /**
     * Applies a free language selection and requests a localized confirmation.
     *
     * @return {void}
     */
    applyLanguageInput() {
        const language = this.languageInput?.value.trim() || '';
        if (language === '') {
            return;
        }

        this.appliedLanguage = language;
        this.updateLanguageStatus();
        this.updateBrowserLanguageOption();
        this.setLanguagePanel(false, true);
        this.startDirectInteraction('language_confirmation', this.resolveLanguageName(language));
    }

    /**
     * Resolves a readable language name for a configured name or BCP 47 tag.
     *
     * @param {string} language Language name or language tag.
     * @return {string}
     */
    resolveLanguageName(language) {
        const value = String(language || '').trim();
        const codeMatch = value.match(/(?:^|\()([a-zA-Z]{2,3}(?:-[a-zA-Z0-9]{2,8})*)\)?$/);
        if (!codeMatch) {
            return value;
        }

        try {
            const languageCode = new Intl.Locale(codeMatch[1]).language;

            return new Intl.DisplayNames([languageCode], {
                type: 'language',
                languageDisplay: 'standard',
            }).of(languageCode) || value;
        } catch (error) {
            return value;
        }
    }

    /**
     * Initializes the explicit option for returning to the TYPO3 site language.
     *
     * @return {void}
     */
    initializeSiteLanguageOption() {
        if (!this.siteLanguageButton || !this.languageInput) {
            return;
        }

        const siteLanguage = String(this.options.language?.responseLanguage || '').trim();
        const languageName = this.resolveLanguageName(siteLanguage);
        const accessibleLabel = this.siteLanguageButton.dataset.label || '';

        this.siteLanguageButton.textContent = `🏠 ${languageName}`;
        this.siteLanguageButton.setAttribute(
            'aria-label',
            `${accessibleLabel}: ${languageName}`.replace(/^:\s*/, ''),
        );
        this.siteLanguageButton.addEventListener('click', () => {
            const languageChanged = this.appliedLanguage !== '';
            this.appliedLanguage = '';
            this.languageInput.value = '';
            this.updateLanguageStatus();
            this.updateLanguageApplyState();
            this.updateBrowserLanguageOption();
            this.setLanguagePanel(false, true);
            if (languageChanged) {
                this.startDirectInteraction('language_confirmation', languageName);
            }
        });
    }

    /**
     * Offers the primary browser language when it differs from the site language.
     *
     * @return {void}
     */
    initializeBrowserLanguageSuggestion() {
        if (!this.languageInput || !this.browserLanguageButton || typeof navigator === 'undefined') {
            return;
        }

        const browserLanguage = String(navigator.languages?.[0] || navigator.language || '').trim();
        const siteLanguage = String(this.options.language?.languageCode || '').trim();
        if (
            browserLanguage === ''
            || browserLanguage.split('-')[0].toLowerCase() === siteLanguage.split('-')[0].toLowerCase()
        ) {
            return;
        }

        const languageName = this.resolveLanguageName(browserLanguage);
        const languageValue = `${languageName} (${browserLanguage})`;
        const accessibleLabel = this.browserLanguageButton.dataset.label || '';

        this.browserLanguageCode = browserLanguage;
        this.browserLanguageButton.textContent = `🔄 ${languageName}`;
        this.browserLanguageButton.setAttribute(
            'aria-label',
            `${accessibleLabel}: ${languageName}`.replace(/^:\s*/, ''),
        );
        this.updateBrowserLanguageOption();
        this.browserLanguageButton.addEventListener('click', () => {
            this.appliedLanguage = languageValue;
            this.languageInput.value = languageValue;
            this.updateLanguageStatus();
            this.updateLanguageApplyState();
            this.updateBrowserLanguageOption();
            this.setLanguagePanel(false, true);
            this.startDirectInteraction('language_confirmation', languageName);
        });
    }

    /**
     * Shows the browser language unless it is the currently selected language.
     *
     * @return {void}
     */
    updateBrowserLanguageOption() {
        if (!this.browserLanguageButton || this.browserLanguageCode === '') {
            return;
        }

        const selectedLanguage = this.appliedLanguage;
        const selectedCode = this.resolveLanguageCode().split('-')[0].toLowerCase();
        const browserCode = this.browserLanguageCode.split('-')[0].toLowerCase();

        this.browserLanguageButton.hidden = selectedLanguage !== '' && selectedCode === browserCode;
    }

    /**
     * Resolves the content element inside a message element.
     *
     * @param {HTMLElement} message Message element.
     * @return {HTMLElement}
     */
    getMessageContentElement(message) {
        const contentElement = message.querySelector(this.options.selectors.messageContent);

        if (contentElement instanceof HTMLElement) {
            return contentElement;
        }

        return message;
    }

    /**
     * Renders formatted content into a message element.
     *
     * @param {HTMLElement|null} message Message element.
     * @param {string} content Message content.
     * @return {void}
     */
    renderMessageContent(message, content) {
        if (!message) {
            return;
        }

        message.innerHTML = this.formatMessageContent(content);
        message.classList.remove(this.options.classes.typing);
    }

    /**
     * Renders the typing indicator inside a message content element.
     *
     * @param {HTMLElement|null} message Message content element.
     * @return {void}
     */
    renderTypingIndicator(message) {
        if (!message || !this.typingTemplate) {
            return;
        }

        const fragment = this.typingTemplate.content.cloneNode(true);
        const indicator = fragment.firstElementChild;

        message.replaceChildren(fragment);
        message.classList.add(this.options.classes.typing);

        if (indicator instanceof HTMLElement) {
            this.announceStatus(indicator.getAttribute('aria-label') || '');
        }
    }

    /**
     * Announces one status update without exposing streaming fragments.
     *
     * @param {string} content Status text.
     * @return {void}
     */
    announceStatus(content) {
        if (!this.status) {
            return;
        }

        this.status.textContent = '';
        window.requestAnimationFrame(() => {
            if (this.status) {
                this.status.textContent = String(content || '').trim();
            }
        });
    }

    /**
     * Announces the rendered text of a completed assistant message.
     *
     * @param {HTMLElement|null} message Message content element.
     * @return {void}
     */
    announceMessage(message) {
        const speaker = message
            ?.closest(`.${this.options.classes.message}`)
            ?.querySelector(`.${this.options.classes.hidden}`)
            ?.textContent || '';

        this.announceStatus(`${speaker} ${message?.textContent || ''}`);
    }

    /**
     * Removes the typing indicator state from a message content element.
     *
     * @param {HTMLElement|null} message Message content element.
     * @return {void}
     */
    removeTypingIndicator(message) {
        if (!message) {
            return;
        }

        message.classList.remove(this.options.classes.typing);
    }

    /**
     * Renders plain content into a message element.
     *
     * @param {HTMLElement|null} message Message element.
     * @param {string} content Message content.
     * @return {void}
     */
    renderPlainMessageContent(message, content) {
        if (!message) {
            return;
        }

        message.textContent = String(content ?? '');
    }

    /**
     * Formats message content for frontend output.
     *
     * @param {string} content Raw message content.
     * @return {string}
     */
    formatMessageContent(content) {
        const markdownHtml = this.parseMarkdownContent(content);
        const sanitizedHtml = this.sanitizeHtml(markdownHtml);

        return this.enhanceLinks(sanitizedHtml);
    }

    /**
     * Parses raw message content with the configured markdown parser.
     *
     * @param {string} content Raw message content.
     * @return {string}
     */
    parseMarkdownContent(content) {
        const normalizedContent = String(content ?? '');
        const parser = this.getMarkdownParser();

        if (!parser) {
            return this.createPlainTextHtml(normalizedContent);
        }

        try {
            if (typeof parser.parse === 'function') {
                return parser.parse(normalizedContent, this.options.markdownOptions || {});
            }

            if (typeof parser.render === 'function') {
                return parser.render(normalizedContent, this.options.markdownOptions || {});
            }

            if (typeof parser === 'function') {
                return parser(normalizedContent, this.options.markdownOptions || {});
            }
        } catch (error) {
            return this.createPlainTextHtml(normalizedContent);
        }

        return this.createPlainTextHtml(normalizedContent);
    }

    /**
     * Resolves the configured markdown parser.
     *
     * @return {Object|Function|null}
     */
    getMarkdownParser() {
        if (this.options.markdownParser) {
            return this.options.markdownParser;
        }

        if (typeof window === 'undefined') {
            return null;
        }

        if (window.marked) {
            return window.marked;
        }

        if (typeof window.markdownit === 'function') {
            return window.markdownit(this.options.markdownOptions || {});
        }

        if (window.markdownit) {
            return window.markdownit;
        }

        return null;
    }

    /**
     * Sanitizes rendered markdown HTML.
     *
     * @param {string} html Rendered HTML.
     * @return {string}
     */
    sanitizeHtml(html) {
        const sanitizer = this.getHtmlSanitizer();

        if (sanitizer && typeof sanitizer.sanitize === 'function') {
            return sanitizer.sanitize(String(html ?? ''), this.options.sanitizeOptions || {});
        }

        return this.sanitizeHtmlWithAllowList(html);
    }

    /**
     * Resolves the configured HTML sanitizer.
     *
     * @return {Object|null}
     */
    getHtmlSanitizer() {
        if (this.options.sanitizer) {
            return this.options.sanitizer;
        }

        if (typeof window !== 'undefined' && window.DOMPurify) {
            return window.DOMPurify;
        }

        return null;
    }

    /**
     * Sanitizes rendered markdown HTML with a small allow-list fallback.
     *
     * @param {string} html Rendered HTML.
     * @return {string}
     */
    sanitizeHtmlWithAllowList(html) {
        const template = document.createElement('template');

        template.innerHTML = String(html ?? '');

        this.sanitizeChildNodes(template.content);

        return template.innerHTML;
    }

    /**
     * Sanitizes all child nodes of a parent node.
     *
     * @param {ParentNode} parent Parent node.
     * @return {void}
     */
    sanitizeChildNodes(parent) {
        Array.from(parent.childNodes).forEach((node) => {
            this.sanitizeNode(node);
        });
    }

    /**
     * Sanitizes a single DOM node.
     *
     * @param {ChildNode} node DOM node.
     * @return {void}
     */
    sanitizeNode(node) {
        if (node.nodeType !== Node.ELEMENT_NODE) {
            return;
        }

        const element = node;
        const tagName = element.tagName.toLowerCase();
        const allowedTags = new Set(this.options.sanitizeOptions?.ALLOWED_TAGS || []);
        const blockedContentTags = new Set([
            'base',
            'embed',
            'iframe',
            'link',
            'meta',
            'object',
            'script',
            'style',
        ]);

        if (blockedContentTags.has(tagName)) {
            element.remove();
            return;
        }

        this.sanitizeChildNodes(element);

        if (!allowedTags.has(tagName)) {
            element.replaceWith(...Array.from(element.childNodes));
            return;
        }

        this.sanitizeElementAttributes(element);
    }

    /**
     * Sanitizes all attributes of an element.
     *
     * @param {Element} element DOM element.
     * @return {void}
     */
    sanitizeElementAttributes(element) {
        const allowedAttributes = new Set(this.options.sanitizeOptions?.ALLOWED_ATTR || []);

        Array.from(element.attributes).forEach((attribute) => {
            const attributeName = attribute.name.toLowerCase();

            if (attributeName.startsWith('on') || !allowedAttributes.has(attributeName)) {
                element.removeAttribute(attribute.name);
                return;
            }

            if (attributeName === 'href' && !this.isAllowedLinkHref(attribute.value)) {
                element.removeAttribute(attribute.name);
            }
        });
    }

    /**
     * Adds frontend link attributes to rendered markdown links.
     *
     * @param {string} html Rendered and sanitized HTML.
     * @return {string}
     */
    enhanceLinks(html) {
        const template = document.createElement('template');

        template.innerHTML = String(html ?? '');

        template.content.querySelectorAll('a[href]').forEach((link) => {
            const href = link.getAttribute('href') || '';

            if (!this.isAllowedLinkHref(href)) {
                link.removeAttribute('href');
                return;
            }

            link.classList.add(this.options.classes.link);
            link.setAttribute('target', this.options.linkTarget);
            link.setAttribute('rel', this.options.linkRel);
        });

        return template.innerHTML;
    }

    /**
     * Checks whether a link href is safe for frontend output.
     *
     * @param {string} href Link href.
     * @return {boolean}
     */
    isAllowedLinkHref(href) {
        const value = String(href || '').trim();

        if (value === '') {
            return false;
        }

        if (value.startsWith('#') || value.startsWith('/')) {
            return true;
        }

        try {
            const url = new URL(value, window.location.href);

            return [
                'http:',
                'https:',
                'mailto:',
                'tel:',
            ].includes(url.protocol);
        } catch (error) {
            return false;
        }
    }

    /**
     * Creates escaped HTML from plain text content.
     *
     * @param {string} content Plain text content.
     * @return {string}
     */
    createPlainTextHtml(content) {
        return this.escapeHtml(content).replace(/\n/g, '<br>');
    }

    /**
     * Escapes HTML entities in message content.
     *
     * @param {string} content Raw message content.
     * @return {string}
     */
    escapeHtml(content) {
        return String(content ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /**
     * Toggles the typing indicator.
     *
     * @param {boolean} show Whether the indicator should be shown.
     * @return {void}
     */
    showTyping(show = true) {
        this.typing?.classList.toggle(this.options.classes.hidden, !show);
    }

    /**
     * Creates request data with the last confirmed response language.
     *
     * @return {FormData}
     */
    createFormData() {
        const formData = new FormData(this.form);
        if (this.languageInput?.name) {
            formData.set(this.languageInput.name, this.appliedLanguage);
        }

        return formData;
    }

    /**
     * Sends an explicit direct interaction without adding an artificial user message.
     *
     * @param {string} interaction Direct interaction identifier.
     * @param {string} fallbackLanguageName Native language name used when the request fails.
     * @return {Promise<void>}
     */
    async startDirectInteraction(interaction, fallbackLanguageName) {
        if (!this.streamUrl || !this.form || !this.directInteractionInput) {
            return;
        }

        this.directInteractionInput.value = interaction;
        const formData = this.createFormData();
        this.directInteractionInput.value = '';

        const message = this.addMessage('', this.options.botOrigin);
        if (!message) {
            return;
        }

        this.renderTypingIndicator(message);

        try {
            const transport = new AiAssistantTransport({
                method: this.options.request.method,
                streamAccept: this.options.request.accept,
                credentials: this.options.request.credentials,
                defaultEventName: this.options.defaultEventName,
                doneEventName: this.options.doneEventName,
            });
            const response = await transport.streamSse(this.streamUrl, formData, (content) => {
                this.renderPlainMessageContent(message, content);
                message.classList.add(this.options.classes.typing);
                this.scrollMessageIntoView(message);
            });

            this.removeTypingIndicator(message);
            this.renderMessageContent(message, response);
            this.announceMessage(message);
        } catch (error) {
            this.removeTypingIndicator(message);
            this.renderMessageContent(message, `✓ ${fallbackLanguageName}`);
            this.announceMessage(message);
        }
    }

    /**
     * Handles form submit.
     *
     * @param {SubmitEvent} event Submit event.
     * @return {void}
     */
    handleSubmit(event) {
        event.preventDefault();

        const prompt = this.input?.value.trim() || '';

        if (prompt === '') {
            return;
        }

        this.setLanguagePanel(false);
        this.startStream(prompt);
    }

    /**
     * Starts the streamed assistant request.
     *
     * @param {string} prompt User prompt.
     * @return {Promise<void>}
     */
    async startStream(prompt) {
        if (!this.streamUrl || !this.input || !this.form) {
            return;
        }

        const formData = this.createFormData();

        this.addMessage(prompt, this.options.userOrigin);
        this.input.value = '';

        const message = this.addMessage('', this.options.botOrigin);
        if (!message) {
            return;
        }

        this.renderTypingIndicator(message);

        try {
            const transport = new AiAssistantTransport({
                method: this.options.request.method,
                streamAccept: this.options.request.accept,
                credentials: this.options.request.credentials,
                defaultEventName: this.options.defaultEventName,
                doneEventName: this.options.doneEventName,
            });

            const botBuffer = await transport.streamSse(this.streamUrl, formData, (content) => {
                this.renderPlainMessageContent(message, content);
                message.classList.add(this.options.classes.typing);
                this.scrollMessageIntoView(message);
            });

            this.removeTypingIndicator(message);
            this.renderMessageContent(message, botBuffer);
            this.announceMessage(message);
        } catch (error) {
            this.removeTypingIndicator(message);
            this.renderMessageContent(message, this.errorMessage || message.textContent);
            this.announceMessage(message);
        }
    }
}
