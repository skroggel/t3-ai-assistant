/**
 * Shared AJAX and SSE transport for assistant frontend integrations.
 */
class AiAssistantTransport {
    /**
     * Default transport options.
     *
     * @type {{method: string, jsonAccept: string, streamAccept: string, credentials: RequestCredentials, defaultEventName: string, doneEventName: string}}
     */
    static defaults = {
        method: 'POST',
        jsonAccept: 'application/json',
        streamAccept: 'text/event-stream',
        credentials: 'same-origin',
        defaultEventName: 'message',
        doneEventName: 'done',
    };

    /**
     * Deep-merges option objects.
     *
     * @param {Object} defaults Default options.
     * @param {Object} overrides Override options.
     * @return {Object}
     */
    static mergeOptions(defaults, overrides) {
        const merged = { ...(defaults || {}) };

        Object.entries(overrides || {}).forEach(([key, value]) => {
            if (
                value
                && typeof value === 'object'
                && !Array.isArray(value)
                && !(value instanceof Element)
            ) {
                merged[key] = AiAssistantTransport.mergeOptions((defaults || {})[key] || {}, value);
                return;
            }

            merged[key] = value;
        });

        return merged;
    }

    /**
     * Constructor.
     *
     * @param {Object} options Optional transport options.
     */
    constructor(options = {}) {
        this.options = AiAssistantTransport.mergeOptions(AiAssistantTransport.defaults, options || {});
    }

    /**
     * Executes a JSON request.
     *
     * @param {string} url Request URL.
     * @param {FormData} formData Request payload.
     * @param {Object} options Optional request options.
     * @return {Promise<Object>}
     */
    async requestJson(url, formData, options = {}) {
        const response = await fetch(url, {
            method: options.method || this.options.method,
            body: formData,
            credentials: options.credentials || this.options.credentials,
            headers: {
                Accept: options.accept || this.options.jsonAccept,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        let payload = null;

        try {
            payload = await response.json();
        } catch (error) {
            payload = null;
        }

        if (!response.ok) {
            throw new Error(payload?.error || payload?.message || `Request failed with status ${response.status}`);
        }

        return payload || {};
    }

    /**
     * Executes a server-sent event stream request.
     *
     * @param {string} url Request URL.
     * @param {FormData} formData Request payload.
     * @param {Function|null} onMessage Callback for live message updates.
     * @param {Object} options Optional request options.
     * @return {Promise<string>}
     */
    async streamSse(url, formData, onMessage = null, options = {}) {
        const response = await fetch(url, {
            method: options.method || this.options.method,
            body: formData,
            credentials: options.credentials || this.options.credentials,
            headers: {
                Accept: options.accept || this.options.streamAccept,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok || !response.body) {
            throw new Error(`Stream request failed with status ${response.status}`);
        }

        return this.readEventStream(response.body, onMessage);
    }

    /**
     * Reads an SSE response stream and returns the collected message payload.
     *
     * @param {ReadableStream<Uint8Array>} body Response body.
     * @param {Function|null} onMessage Callback for live message updates.
     * @return {Promise<string>}
     */
    async readEventStream(body, onMessage = null) {
        const reader = body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';
        let message = '';
        let done = false;

        const handleEvent = (rawEvent) => {
            const event = this.parseServerSentEvent(rawEvent);

            if (event.event === this.options.doneEventName) {
                done = true;
                return;
            }

            if (event.event === 'error') {
                throw new Error(event.data || 'The answer could not be loaded.');
            }

            if (event.data !== '') {
                message += event.data;

                if (typeof onMessage === 'function') {
                    onMessage(message, event);
                }
            }
        };

        while (!done) {
            const chunk = await reader.read();
            done = chunk.done;

            buffer += decoder.decode(chunk.value || new Uint8Array(), { stream: !done });

            const parts = buffer.split(/\r?\n\r?\n/);
            buffer = parts.pop() || '';

            parts.forEach((part) => {
                if (part !== '') {
                    handleEvent(part);
                }
            });
        }

        if (buffer !== '') {
            handleEvent(buffer);
        }

        return message;
    }

    /**
     * Parses a single server-sent event block.
     *
     * @param {string} rawEvent Raw event block.
     * @return {{event: string, data: string}}
     */
    parseServerSentEvent(rawEvent) {
        const lines = rawEvent.split(/\r?\n/);
        const event = {
            event: this.options.defaultEventName,
            data: '',
        };
        const dataLines = [];

        lines.forEach((line) => {
            if (line.startsWith('event:')) {
                event.event = line.slice(6).trim();
                return;
            }

            if (line.startsWith('data:')) {
                let value = line.slice(5);

                if (value.startsWith(' ')) {
                    value = value.slice(1);
                }

                dataLines.push(value);
            }
        });

        event.data = dataLines.join('\n');

        return event;
    }
}
