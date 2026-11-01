<template>
    <!--
        This component owns the standard chat markup. The shared composable
        supplies state and actions, while this template decides how that state
        is represented in the DOM and which elements receive focus.

        A click listener is attached to the root so declarative actions emitted
        by configured UI components can be handled without adding listeners to
        every generated element.
    -->
    <section
        ref="chatRoot"
        :class="['aiassistant-chat', 'container', 'chat-box']"
        role="region"
        :aria-label="labels.chatLabel"
        @click="handleComponentClick"
    >
        <!--
            Consent deliberately replaces the complete chat UI until the user
            has accepted. This prevents messages, inputs and assistant content
            from being exposed before the configured consent decision.
        -->
        <div v-if="requireConsent && !consentGiven" ref="consentPanel" class="chat-box-consent alert alert-warning" role="note">
            <p>
                {{ labels.consentMessage }}
                <a v-if="privacyLink" :href="privacyLink" target="_blank" rel="noopener noreferrer">
                    {{ labels.privacyLinkLabel }}
                </a>
            </p>
            <button ref="consentButton" type="button" class="btn btn-primary" @click="acceptConsent">
                {{ labels.consentLabel }}
            </button>
        </div>

        <template v-else>
            <!-- Optional identity and introduction area above the conversation. -->
            <header v-if="assistantName || introText || privacyLink" class="chat-box-header">
                <div v-if="assistantName" class="chat-box-header-top">
                    <p class="chat-box-header-brand">{{ assistantName }}</p>
                </div>

                <p v-if="introText || privacyLink" class="chat-box-header-intro">
                    {{ introText }}
                    <a v-if="privacyLink" :href="privacyLink" target="_blank" rel="noopener noreferrer">
                        {{ labels.privacyLinkLabel }}
                    </a>
                </p>

                <!--
                    Language selection is rendered only when enabled by the
                    normalized options. The selector emits a semantic selection;
                    the composable turns it into the existing backend
                    language-confirmation interaction.
                -->
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

            <!--
                The message list is intentionally kept as a reusable child
                component. Customer-owned templates can use the same list with
                the refs and actions returned by the composable.
            -->
            <ChatMessageList
                :messages="messages"
                :user-label="labels.userLabel"
                :assistant-name="labels.assistantName"
                :number-of-loading-dots="numberOfLoadingDots"
                :loading-html="loadingHtml"
                :loading-messages="loadingMessages"
                :loading-messages-timeout="loadingMessagesTimeout"
            />

            <!-- Errors are exposed as an accessible live status by this child. -->
            <ChatStatus :status="statusMessage" />

            <!-- ChatInput emits a plain query; request handling stays centralized. -->
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
/**
 * Standard Vue presentation for the AI Assistant chat.
 *
 * This component intentionally owns only the DOM-facing concerns:
 *
 * - the standard chat layout;
 * - references to consent and chat elements;
 * - focus behavior after consent;
 * - composition of the reusable child components.
 *
 * Conversation state, transport selection, streaming, Markdown rendering,
 * consent state and direct interactions are provided by
 * `useAiAssistantChat`. The custom-element entry point wraps this component
 * instead of maintaining a second implementation.
 *
 * @module AiAssistantChat
 */
import { nextTick, ref } from 'vue';

// `chatProps` is shared with the custom-element adapter so both entry points
// accept exactly the same public configuration contract.
import { chatProps } from '../chatProps.js';
import useAiAssistantChat from '../composables/useAiAssistantChat.js';

// These children remain independent and reusable for customer-owned templates.
import ChatInput from './ChatInput.vue';
import ChatMessageList from './ChatMessageList.vue';
import ChatStatus from './ChatStatus.vue';
import LanguageSelector from './LanguageSelector.vue';

/**
 * Public properties received from TYPO3, the custom element or a Vue parent.
 *
 * @type {import('vue').DefineProps<AiAssistantChatProps>}
 */
const props = defineProps(chatProps);

/**
 * Emits transport errors without deciding how the host should display them.
 *
 * @type {import('vue').EmitFn<{error: (error: unknown) => void}>}
 */
const emit = defineEmits(['error']);

/**
 * Root element used for focus behavior that depends on the standard markup.
 *
 * The composable does not query the DOM because customer templates may use a
 * different input structure. This component is therefore the correct owner of
 * selectors and element references.
 *
 * @type {import('vue').Ref<HTMLElement|null>}
 */
const chatRoot = ref(null);

/** @type {import('vue').Ref<HTMLElement|null>} Consent button for initial focus. */
const consentButton = ref(null);

/** @type {import('vue').Ref<HTMLElement|null>} Consent panel retained for DOM integration. */
const consentPanel = ref(null);

// These values are presentation-only props. The composable receives the full
// props object above and owns all stateful behavior.
const { assistantName, introText, privacyLink } = props;

/**
 * Focuses the standard ChatInput control after consent is accepted.
 *
 * The selector is intentionally kept in this component because it describes
 * this component's markup, not a universal requirement of the composable.
 *
 * @return {void}
 */
const focusPrompt = () => chatRoot.value?.querySelector('.chat-box-prompt-input')?.focus();

/**
 * Restores focus to the consent control when the consent-gated view is shown.
 *
 * @return {Promise<void>} Resolves after the control has been focused.
 */
const focusConsent = async () => {
    await nextTick();
    consentButton.value?.focus();
};

/**
 * Moves focus to the input after the initial message has been prepared.
 *
 * @return {Promise<void>} Resolves after the input focus has been attempted.
 */
const onConsentAccepted = async () => {
    await nextTick();
    focusPrompt();
};

/**
 * Connect the shared behavior to the standard presentation.
 *
 * The callbacks are the boundary between framework-independent conversation
 * behavior and DOM-specific accessibility behavior. A customer template can
 * provide the same composable with its own focus and scrolling callbacks.
 */
const {
    messages,
    loading,
    consentGiven,
    statusMessage,
    requireConsent,
    showLanguageSelector,
    effectiveResponseLanguage,
    effectiveLanguageCode,
    labels,
    numberOfLoadingDots,
    loadingHtml,
    loadingMessages,
    loadingMessagesTimeout,
    send,
    acceptConsent,
    confirmLanguage,
    startDirectInteraction,
    handleComponentClick,
} = useAiAssistantChat(props, {
    emit,
    onConsentAccepted,
    onConsentRequired: focusConsent,
});

/**
 * Exposes the existing imperative interaction API to parent components and to
 * the custom-element adapter. The actual request still runs through the shared
 * composable; this component only makes the method reachable through its ref.
 */
defineExpose({ startDirectInteraction, consentButton });
</script>
