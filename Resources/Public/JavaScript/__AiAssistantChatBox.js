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
 * @property {string} userOrigin User message origin value.
 * @property {string} botOrigin Assistant message origin value.
 * @property {string} defaultEventName Default server-sent event name.
 * @property {string} doneEventName Server-sent event name that finishes the stream.
 * @property {ScrollIntoViewOptions} scrollOptions Scroll options for new messages.
 * @property {string} linkTarget Target attribute for rendered links.
 * @property {string} linkRel Rel attribute for rendered links.
 */

/**
 * Frontend chat box handler for the AI Assistant plugin.
 *
 * The handler initializes itself on forms using the configured form selector,
 * serializes the complete form payload and sends it to the form action.
 */
class __AiAssistantChatBox {
    /**
     * Default chat box options.
     *
     * @type {AiAssistantChatBoxOptions}
     */
    static defaults = {
        selectors: {
            container: '.chat-box',
            form: '.js-ai-assistant-form',
            messages: '.js-ai-assistant-messages',
            input: '.js-ai-assistant-input',
            typing: '.js-ai-assistant-indicator',
            userMessageTemplate: '.js-ai-assistant-user-message-template',
            botMessageTemplate: '.js-ai-assistant-bot-message-template',
            typingTemplate: '.js-ai-assistant-typing-template',
            messageContent: '.js-ai-assistant-message-content',
        },
        classes: {
            message: 'chat-box-message',
            fromPrefix: 'from-',
            typing: 'is-typing',
            hidden: 'visually-hidden',
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
        userOrigin: 'user',
        botOrigin: 'bot',
        defaultEventName: 'message',
        doneEventName: 'done',
        scrollOptions: {
            behavior: 'smooth',
            block: 'end',
        },
        linkTarget: '_blank',
        linkRel: 'noopener noreferrer',
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
        this.options = __AiAssistantChatBox.mergeOptions(__AiAssistantChatBox.defaults, options);

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
         * Message output container.
         *
         * @type {Element|null}
         */
        this.messagesContainer = this.container.querySelector(this.options.selectors.messages);

        /**
         * Message input field.
         *
         * @type {HTMLInputElement|HTMLTextAreaElement|null}
         */
        this.input = this.form.querySelector(this.options.selectors.input);

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

        this.renderInitialMessage();
        this.form.addEventListener('submit', (event) => this.handleSubmit(event));
    }

    /**
     * Initializes all assistant frontend forms.
     *
     * @param {ParentNode} root Root node used for form lookup.
     * @param {Partial<AiAssistantChatBoxOptions>} options Optional configuration overrides.
     * @return {Array<__AiAssistantChatBox>}
     */
    static init(root = document, options = {}) {
        const mergedOptions = __AiAssistantChatBox.mergeOptions(__AiAssistantChatBox.defaults, options);
        const instances = [];

        root.querySelectorAll(mergedOptions.selectors.form).forEach((form) => {
            if (form instanceof HTMLFormElement) {
                instances.push(new __AiAssistantChatBox(form, mergedOptions));
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
                merged[key] = __AiAssistantChatBox.mergeOptions(defaults[key] || {}, value);
                return;
            }

            merged[key] = value;
        });

        return merged;
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

        this.typeMessage(message, this.initialMessage, this.options.delays.initialMessage);
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
            message.scrollIntoView(this.options.scrollOptions);

            if (index >= content.length) {
                message.classList.remove(this.options.classes.typing);

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
        message.scrollIntoView(this.options.scrollOptions);

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

        return message;
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

        message.replaceChildren(fragment);
        message.classList.add(this.options.classes.typing);
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
     * Formats message content for frontend output.
     *
     * @param {string} content Raw message content.
     * @return {string}
     */
    formatMessageContent(content) {
        const escaped = this.escapeHtml(content);
        const withLinks = escaped.replace(
            /(https?:\/\/[^\s<]+)/g,
            `<a href="$1" class="${this.options.classes.link}" target="${this.options.linkTarget}" rel="${this.options.linkRel}">$1</a>`
        );

        return withLinks.replace(/\n/g, '<br>');
    }

    /**
     * Escapes HTML entities in message content.
     *
     * @param {string} content Raw message content.
     * @return {string}
     */
    escapeHtml(content) {
        return String(content)
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

        const formData = new FormData(this.form);

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
                this.renderMessageContent(message, content);
                message.classList.add(this.options.classes.typing);
                message.scrollIntoView(this.options.scrollOptions);
            });

            this.removeTypingIndicator(message);
            this.renderMessageContent(message, botBuffer);
        } catch (error) {
            this.removeTypingIndicator(message);
            this.renderMessageContent(message, this.errorMessage || message.textContent);
        }
    }
}

