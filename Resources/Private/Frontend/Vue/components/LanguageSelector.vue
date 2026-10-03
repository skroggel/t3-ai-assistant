<template>
    <div v-if="visible" class="chat-box-language">
        <button :id="`${inputId}-toggle`" type="button" class="btn btn-outline-secondary chat-box-language-toggle" :aria-expanded="String(open)" :aria-controls="panelId" @click="togglePanel">
            <span aria-hidden="true">&#127760;</span> {{ languageLabel }}<span v-if="selectedName">: {{ selectedName }}</span>
        </button>
        <div v-if="open" :id="panelId" ref="panel" class="chat-box-language-options">
            <button type="button" class="btn btn-outline-secondary" @click="select('')">{{ siteLanguageLabel }}: {{ siteLanguageName }}</button>
            <button v-if="browserLanguageName" type="button" class="btn btn-outline-secondary" @click="select(browserLanguage)">{{ browserLanguageLabel }}: {{ browserLanguageName }}</button>
            <div class="chat-box-language-custom">
                <label class="aiassistant-visually-hidden" :for="inputId">{{ languageLabel }}</label>
                <input :id="inputId" ref="input" v-model="draft" class="form-control" :list="optionsId" :placeholder="placeholder" @keydown.enter.prevent="apply" @keydown.esc="closePanel(true)">
                <button type="button" class="btn btn-primary" :disabled="!draft.trim()" @click="apply">{{ applyLabel }}</button>
            </div>
            <datalist :id="optionsId">
                <option v-for="option in languageOptions" :key="option" :value="option"></option>
            </datalist>
        </div>
    </div>
</template>

<script setup>
import { computed, nextTick, ref } from 'vue';

/**
 * @typedef {Object} LanguageSelectorProps
 * @property {boolean} visible Whether the selector is rendered.
 * @property {string} siteLanguage Current website language name.
 * @property {string} languageCode Current website language code.
 * @property {string} languageLabel Accessible label for the selector.
 * @property {string} siteLanguageLabel Label for the website-language shortcut.
 * @property {string} browserLanguageLabel Label for the browser-language shortcut.
 * @property {string} applyLabel Label for the apply button.
 * @property {string} placeholder Language input placeholder.
 */

/** @type {import('vue').DefineProps<LanguageSelectorProps>} */
const props = defineProps({
    visible: { type: Boolean, default: false },
    siteLanguage: { type: String, default: '' },
    languageCode: { type: String, default: '' },
    languageLabel: { type: String, default: 'Response language' },
    siteLanguageLabel: { type: String, default: 'Use website language' },
    browserLanguageLabel: { type: String, default: 'Use browser language' },
    applyLabel: { type: String, default: 'Use selected language' },
    placeholder: { type: String, default: 'Language' },
});

/**
 * Emits the selected language as a value, display name and optional code.
 *
 * @type {import('vue').EmitFn<{ 'language-selected': (selection: {value: string, name: string, code: string}) => void }>}
 */
const emit = defineEmits(['language-selected']);

/** @type {import('vue').Ref<boolean>} Whether the language panel is open. */
const open = ref(false);
/** @type {import('vue').Ref<string>} Current uncommitted input value. */
const draft = ref('');
/** @type {import('vue').Ref<string>} Last language submitted by the user. */
const applied = ref('');
/** @type {string} Unique input identifier for labels and accessibility. */
const inputId = `aiassistant-language-${Math.random().toString(36).slice(2)}`;
/** @type {string} Unique language-panel identifier. */
const panelId = `${inputId}-panel`;
/** @type {string} Unique datalist identifier. */
const optionsId = `${inputId}-options`;
/** @type {import('vue').Ref<HTMLInputElement|null>} Language input element. */
const input = ref(null);
/** @type {import('vue').Ref<HTMLElement|null>} Language panel element. */
const panel = ref(null);
/** @type {string} Browser language reported by the current user agent. */
const browserLanguage = typeof navigator !== 'undefined' ? String(navigator.languages?.[0] || navigator.language || '') : '';
/** @type {string[]} Common language suggestions shown by the native datalist. */
const languageOptions = [
    'Deutsch (de)',
    'English (en)',
    'Español (es)',
    'Français (fr)',
    'Italiano (it)',
    'Polski (pl)',
    'Português (pt)',
    'Türkçe (tr)',
    'العربية (ar)',
    'فارسی (fa)',
    'اردو (ur)',
    'Русский (ru)',
    'Українська (uk)',
    'हिन्दी (hi)',
    'বাংলা (bn)',
    '中文 (zh)',
    '日本語 (ja)',
    '한국어 (ko)',
];

/**
 * Resolves a human-readable language name from a language code or labelled value.
 *
 * @param {string} value Language name, code or labelled code.
 * @return {string} Localized language name or the original value.
 */
const languageName = (value) => {
    const match = String(value || '').match(/(?:^|\()([a-zA-Z]{2,3}(?:-[a-zA-Z0-9]{2,8})*)\)?$/);
    if (!match) return String(value || '');
    try { return new Intl.DisplayNames([match[1]], { type: 'language' }).of(match[1]) || value; } catch (error) { return value; }
};

/** @type {import('vue').ComputedRef<string>} Selected language shown in the toggle. */
const selectedName = computed(() => languageName(applied.value || props.siteLanguage));

/** @type {import('vue').ComputedRef<string>} Website language shown in the shortcut. */
const siteLanguageName = computed(() => languageName(props.siteLanguage));

/** @type {import('vue').ComputedRef<string>} Browser language when it differs from the website language. */
const browserLanguageName = computed(() => browserLanguage && browserLanguage.split('-')[0] !== props.languageCode.split('-')[0] ? languageName(browserLanguage) : '');

/**
 * Extracts a BCP 47 language code from a selected language value.
 *
 * @param {string} value Language name, code or labelled code.
 * @return {string} Extracted language code or an empty string.
 */
const resolveLanguageCode = (value) => {
    const match = String(value || '').match(/(?:^|\()([a-zA-Z]{2,3}(?:-[a-zA-Z0-9]{2,8})*)\)?$/);
    return match ? match[1] : (/^[a-zA-Z]{2,3}(?:-[a-zA-Z0-9]{2,8})*$/.test(value) ? value : '');
};

/**
 * Closes the panel and optionally restores focus to its toggle.
 *
 * @param {boolean} restoreFocus Whether focus should return to the toggle.
 * @return {void}
 */
const closePanel = (restoreFocus = false) => {
    open.value = false;
    if (restoreFocus) document.getElementById(`${inputId}-toggle`)?.focus();
};

/**
 * Toggles the panel and focuses the input when it opens.
 *
 * @return {Promise<void>} Resolves after the panel has been rendered.
 */
const togglePanel = async () => {
    open.value = !open.value;
    if (open.value) {
        await nextTick();
        input.value?.focus();
    }
};

/**
 * Applies a language shortcut or selected language value.
 *
 * @param {string} value Selected language value.
 * @return {void}
 */
const select = (value) => {
    applied.value = value;
    draft.value = value;
    closePanel(true);
    emit('language-selected', { value, name: languageName(value), code: resolveLanguageCode(value) });
};

/**
 * Applies the current free-text language input when it is not empty.
 *
 * @return {void}
 */
const apply = () => { if (draft.value.trim()) select(draft.value.trim()); };
</script>
