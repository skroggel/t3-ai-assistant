/**
 * @typedef {Object} AiAssistantChatProps
 * @property {string} endpoint Chat endpoint.
 * @property {boolean|string|number} streamResponse Whether to stream the response.
 * @property {string} parameterPrefix Extbase request parameter namespace.
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
 * @property {number|string} renderLagCharacters Number of streamed characters kept ahead of the visual renderer.
 * @property {number|string} renderChunkCharacters Number of characters rendered per visual catch-up step.
 * @property {string} loadingHtml Optional HTML shown while the assistant is typing.
 * @property {Array|string} loadingMessages Optional rotating loading messages.
 * @property {number|string} loadingMessagesTimeout Delay between loading messages in milliseconds.
 * @property {boolean|string|number} requireConsent Whether consent is required.
 * @property {boolean|string|number} plainLanguage Whether plain-language mode is enabled.
 * @property {number|string} numberOfLoadingDots Number of dots shown while the assistant is generating a response.
 * @property {boolean|string|number} showLanguageSelector Whether the language selector is shown.
 * @property {string} responseLanguage Configured response language name.
 * @property {string} languageCode Configured response language code.
 * @property {string} initialMessage Optional initial assistant message.
 * @property {string} privacyLink Optional privacy policy URL.
 * @property {string} assistantName Optional assistant name.
 * @property {string} introText Optional header text.
 */

/** Shared prop contract for the framework-independent and Vue chat entries. */
export const chatProps = {
    // Request identity.
    endpoint: { type: String, default: '' },
    streamResponse: { type: [String, Number, Boolean], default: true },
    parameterPrefix: { type: String, default: 'tx_aiassistant_chat' },
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
    renderLagCharacters: { type: [String, Number], default: 300 },
    renderChunkCharacters: { type: [String, Number], default: 15 },
    loadingHtml: { type: String, default: '' },
    loadingMessages: { type: [Array, String], default: () => [] },
    loadingMessagesTimeout: { type: [String, Number], default: 3000 },
    requireConsent: { type: [String, Number, Boolean], default: false },
    plainLanguage: { type: [String, Number, Boolean], default: false },
    numberOfLoadingDots: { type: Number, default: 3 },

    // Language behavior.
    showLanguageSelector: { type: [String, Number, Boolean], default: false },
    responseLanguage: { type: String, default: '' },
    languageCode: { type: String, default: '' },

    // Messages and text.
    initialMessage: { type: String, default: '' },
    errorMessage: { type: String, default: '' },
    assistantName: { type: String, default: '' },
    privacyLink: { type: String, default: '' },
    introText: { type: String, default: '' },
};
