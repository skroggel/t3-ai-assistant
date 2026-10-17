document.addEventListener('DOMContentLoaded', () => {
    const escapeHtml = (value) => String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const promptSectionClassMap = {
        'Original User Query': 'query',
        'Current Query': 'query',
        'Retrieved Context': 'retrieval',
        'Answer Context': 'answer-context',
        'Answer Candidate': 'candidate',
    };

    document.querySelectorAll('[data-aiassistant-highlight-prompt-sections]').forEach((element) => {
        const source = element.textContent || '';
        const sectionPattern = /\[(Original User Query|Current Query|Retrieved Context|Answer Context|Answer Candidate)\]/g;
        let hasHighlightedStepTitle = false;

        element.innerHTML = source.split('\n').map((line) => {
            const highlightedLine = escapeHtml(line).replace(sectionPattern, (match, sectionName) => {
                const sectionClass = promptSectionClassMap[sectionName] || 'default';
                return `<span class="aiassistant-config__prompt-section aiassistant-config__prompt-section--${sectionClass}">${match}</span>`;
            });

            if (!hasHighlightedStepTitle && /^\s*"step_title"\s*:/.test(line)) {
                hasHighlightedStepTitle = true;
                return `<span class="aiassistant-config__prompt-step-title">${highlightedLine}</span>`;
            }

            return highlightedLine;
        }).join('\n');
    });

    const runDialog = document.getElementById('aiassistant-run-dialog');
    const runDialogLoadingLabel = runDialog?.dataset.aiassistantLoadingLabel || '';
    const runDialogLoadFailedLabel = runDialog?.dataset.aiassistantLoadFailedLabel || '';

    document.querySelectorAll('.aiassistant-config__run-details').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const url = btn.getAttribute('data-run-url');
            const dialog = runDialog;
            const content = dialog ? dialog.querySelector('[data-aiassistant-content]') : null;
            if (!dialog || !content || !url) {
                return;
            }
            content.textContent = runDialogLoadingLabel;
            try {
                const res = await fetch(url, { credentials: 'same-origin' });
                const data = await res.json();
                content.textContent = JSON.stringify(data, null, 2);
            } catch (e) {
                content.textContent = runDialogLoadFailedLabel;
            }
            dialog.showModal();
        });
    });

    document.querySelectorAll('[data-aiassistant-close]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const dialog = document.getElementById('aiassistant-run-dialog');
            if (dialog) {
                dialog.close();
            }
        });
    });

    document.querySelectorAll('.aiassistant-config__preview-details').forEach((btn) => {
        btn.addEventListener('click', () => {
            const dialog = document.getElementById('aiassistant-preview-dialog');
            const content = dialog ? dialog.querySelector('[data-aiassistant-preview-content]') : null;
            if (!dialog || !content) {
                return;
            }
            const source = btn.closest('.aiassistant-config__card')
                ? btn.closest('.aiassistant-config__card').querySelector('.aiassistant-config__preview-source')
                : null;
            content.textContent = source ? source.textContent.trim() : '';
            dialog.showModal();
        });
    });

    document.querySelectorAll('.aiassistant-config__error-details').forEach((btn) => {
        btn.addEventListener('click', () => {
            const dialog = document.getElementById('aiassistant-preview-dialog');
            const content = dialog ? dialog.querySelector('[data-aiassistant-preview-content]') : null;
            if (!dialog || !content) {
                return;
            }
            const source = btn.closest('tr')
                ? btn.closest('tr').querySelector('.aiassistant-config__error-source')
                : null;
            content.textContent = source ? source.textContent.trim() : '';
            dialog.showModal();
        });
    });

    document.querySelectorAll('.aiassistant-config__trace-details-button').forEach((btn) => {
        btn.addEventListener('click', () => {
            const dialog = document.getElementById('aiassistant-preview-dialog');
            const content = dialog ? dialog.querySelector('[data-aiassistant-preview-content]') : null;
            if (!dialog || !content) {
                return;
            }
            const source = btn.closest('tr')
                ? btn.closest('tr').querySelector('.aiassistant-config__trace-payload')
                : null;
            content.textContent = source ? source.textContent.trim() : '';
            dialog.showModal();
        });
    });

    document.querySelectorAll('.aiassistant-config__trace-pipeline-button').forEach((btn) => {
        btn.addEventListener('click', () => {
            const dialog = document.getElementById('aiassistant-preview-dialog');
            const content = dialog ? dialog.querySelector('[data-aiassistant-preview-content]') : null;
            if (!dialog || !content) {
                return;
            }
            const source = btn.closest('tr')
                ? btn.closest('tr').querySelector('.aiassistant-config__trace-pipeline')
                : null;
            content.textContent = source ? source.textContent.trim() : '';
            dialog.showModal();
        });
    });

    document.querySelectorAll('[data-aiassistant-preview-close]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const dialog = document.getElementById('aiassistant-preview-dialog');
            if (dialog) {
                dialog.close();
            }
        });
    });

    document.querySelectorAll('.aiassistant-config__indexer-message-details').forEach((btn) => {
        btn.addEventListener('click', () => {
            const dialog = document.getElementById('aiassistant-indexer-message-dialog');
            const content = dialog ? dialog.querySelector('[data-aiassistant-indexer-message-content]') : null;
            if (!dialog || !content) {
                return;
            }
            const source = btn.closest('td')
                ? btn.closest('td').querySelector('.aiassistant-config__preview-source')
                : null;
            content.innerHTML = source ? source.innerHTML.trim() : '';
            dialog.showModal();
        });
    });

    document.querySelectorAll('[data-aiassistant-indexer-message-close]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const dialog = document.getElementById('aiassistant-indexer-message-dialog');
            if (dialog) {
                dialog.close();
            }
        });
    });

    const indexerRunDialog = document.getElementById('aiassistant-indexer-run-dialog');
    const indexerRunTitle = indexerRunDialog?.querySelector('[data-aiassistant-indexer-run-title]') || null;
    const indexerRunHelp = indexerRunDialog?.querySelector('[data-aiassistant-indexer-run-help]') || null;
    const indexerRunUid = indexerRunDialog?.querySelector('[data-aiassistant-indexer-run-uid]') || null;
    const indexerRunType = indexerRunDialog?.querySelector('[data-aiassistant-indexer-run-type]') || null;
    const indexerRunDry = indexerRunDialog?.querySelector('[data-aiassistant-indexer-run-dry]') || null;
    const indexerRunDryToggle = indexerRunDialog?.querySelector('[data-aiassistant-indexer-run-dry-toggle]') || null;
    const indexerRunOnlyChanged = indexerRunDialog?.querySelector('[data-aiassistant-indexer-run-only-changed]') || null;
    const indexerRunSince = indexerRunDialog?.querySelector('[data-aiassistant-indexer-run-since]') || null;
    const indexerRunCollection = indexerRunDialog?.querySelector('[data-aiassistant-indexer-run-collection]') || null;
    const indexerRunLimit = indexerRunDialog?.querySelector('[data-aiassistant-indexer-run-limit]') || null;
    const indexerRunLimitHelp = indexerRunDialog?.querySelector('[data-aiassistant-indexer-run-limit-help]') || null;
    const indexerRunSubmit = indexerRunDialog?.querySelector('[data-aiassistant-indexer-run-submit]') || null;
    const indexerRunFields = indexerRunDialog ? Array.from(indexerRunDialog.querySelectorAll('[data-aiassistant-indexer-run-field]')) : [];
    const defaultIndexerRunLimit = indexerRunLimit ? indexerRunLimit.defaultValue || indexerRunLimit.value || '100' : '100';

    const indexerRunConfig = {
        file: {
            showFields: ['onlyChanged'],
            help: indexerRunDialog?.dataset.helpFile || '',
            limitHelp: indexerRunDialog?.dataset.limitHelpGeneric || '',
            defaultLimit: defaultIndexerRunLimit,
        },
        page: {
            showFields: ['onlyChanged'],
            help: indexerRunDialog?.dataset.helpPage || '',
            limitHelp: indexerRunDialog?.dataset.limitHelpGeneric || '',
            defaultLimit: defaultIndexerRunLimit,
        },
        external: {
            showFields: ['since', 'collection'],
            help: indexerRunDialog?.dataset.helpExternal || '',
            limitHelp: indexerRunDialog?.dataset.limitHelpExternal || '',
            defaultLimit: '200',
        },
    };

    const syncIndexerRunMode = () => {
        if (!indexerRunDry || !indexerRunSubmit || !indexerRunDryToggle) {
            return;
        }
        const isDryRun = !!indexerRunDryToggle.checked;
        indexerRunDry.value = isDryRun ? '1' : '0';
        indexerRunSubmit.textContent = isDryRun
            ? (indexerRunDialog?.dataset.submitDryLabel || 'Dry-run')
            : (indexerRunDialog?.dataset.submitLabel || 'Run');
        indexerRunSubmit.classList.toggle('aiassistant-config__button--dry-run', isDryRun);
    };

    const syncIndexerRunFields = (runType) => {
        const config = indexerRunConfig[runType] || indexerRunConfig.external;
        indexerRunFields.forEach((field) => {
            const fieldName = field.getAttribute('data-aiassistant-indexer-run-field');
            field.hidden = !config.showFields.includes(fieldName);
        });
        if (indexerRunHelp) {
            indexerRunHelp.textContent = config.help;
        }
        if (indexerRunLimitHelp) {
            indexerRunLimitHelp.textContent = config.limitHelp;
        }
        if (indexerRunLimit) {
            indexerRunLimit.value = config.defaultLimit;
            indexerRunLimit.max = runType === 'external' ? '500' : '1000';
        }
    };

    if (indexerRunDryToggle) {
        indexerRunDryToggle.addEventListener('change', syncIndexerRunMode);
    }

    document.querySelectorAll('[data-aiassistant-indexer-run]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!indexerRunDialog || !indexerRunUid || !indexerRunType || !indexerRunDry || !indexerRunSubmit) {
                return;
            }
            const runUid = button.getAttribute('data-run-uid') || '';
            const runType = button.getAttribute('data-run-type') || 'external';
            const runTitle = button.getAttribute('data-run-title') || '';
            const runModeLabel = button.getAttribute('data-run-mode-label') || '';

            indexerRunUid.value = runUid;
            indexerRunType.value = runType;
            if (indexerRunDryToggle) {
                indexerRunDryToggle.checked = false;
            }
            if (indexerRunOnlyChanged) {
                indexerRunOnlyChanged.checked = false;
            }
            if (indexerRunSince) {
                indexerRunSince.value = '';
            }
            if (indexerRunCollection) {
                indexerRunCollection.value = '';
            }
            syncIndexerRunFields(runType);
            if (indexerRunTitle) {
                indexerRunTitle.textContent = runTitle !== '' && runModeLabel !== ''
                    ? `${runModeLabel}: ${runTitle}`
                    : runModeLabel;
            }
            syncIndexerRunMode();
            indexerRunDialog.showModal();
        });
    });

    document.querySelectorAll('[data-aiassistant-indexer-run-close]').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (indexerRunDialog) {
                indexerRunDialog.close();
            }
        });
    });

    document.querySelectorAll('button[data-aiassistant-confirm]').forEach((button) => {
        button.addEventListener('click', (event) => {
            const message = button.getAttribute('data-aiassistant-confirm');
            if (message && !confirm(message)) {
                event.preventDefault();
                return;
            }
            document.querySelectorAll('button[data-aiassistant-confirm][data-aiassistant-confirm-active]').forEach((btn) => {
                btn.removeAttribute('data-aiassistant-confirm-active');
            });
            button.setAttribute('data-aiassistant-confirm-active', '1');
        });
    });

    document.querySelectorAll('form[data-aiassistant-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.getAttribute('data-aiassistant-confirm');
            if (message && !confirm(message)) {
                event.preventDefault();
            }
        });
    });

    const tabButtons = Array.from(document.querySelectorAll('.aiassistant-config__tab'));
    const tabPanels = Array.from(document.querySelectorAll('.aiassistant-config__tab-panel'));
    const tabStorageKey = 'aiassistant-active-tab';
    const diagnosticsTabButton = document.querySelector('.aiassistant-config__tab[data-tab="preview"]');
    const diagnosticsBadge = diagnosticsTabButton ? diagnosticsTabButton.querySelector('[data-aiassistant-diagnostics-badge]') : null;
    const errorsDot = document.querySelector('[data-aiassistant-subtab-dot="errors"]');
    const chatlogDot = document.querySelector('[data-aiassistant-subtab-dot="chatlog"]');
    const diagnosticsIssueKey = diagnosticsTabButton?.getAttribute('data-aiassistant-diagnostics-issue-key') || '';
    const diagnosticsSeenStorageKey = 'aiassistant-diagnostics-seen';
    const errorsSeenStorageKey = 'aiassistant-diagnostics-errors-seen';
    const chatlogSeenStorageKey = 'aiassistant-diagnostics-chatlog-seen';
    const searchSubtabs = Array.from(document.querySelectorAll('[data-aiassistant-subtabs] .aiassistant-config__subtab'));
    const searchPanels = Array.from(document.querySelectorAll('[data-subtab-panel]'));
    const searchTabStorageKey = 'aiassistant-active-search-tab';
    const settingsSubtabs = Array.from(document.querySelectorAll('[data-aiassistant-settings-tabs] .aiassistant-config__subtab'));
    const settingsRows = Array.from(document.querySelectorAll('[data-settings-group]'));
    const settingsPanels = Array.from(document.querySelectorAll('[data-settings-panel]'));
    const settingsTabStorageKey = 'aiassistant-active-settings-tab';
    const indexingSubtabs = Array.from(document.querySelectorAll('[data-aiassistant-indexing-tabs] .aiassistant-config__subtab'));
    const indexingPanels = Array.from(document.querySelectorAll('[data-indexing-panel]'));
    const indexingTabStorageKey = 'aiassistant-active-indexing-tab';

    const activateTab = (tabName) => {
        tabButtons.forEach((button) => {
            const isActive = button.dataset.tab === tabName;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });
        tabPanels.forEach((panel) => {
            const isActive = panel.dataset.tab === tabName;
            panel.classList.toggle('is-active', isActive);
            panel.hidden = !isActive;
        });
        if (tabName) {
            try {
                sessionStorage.setItem(tabStorageKey, tabName);
            } catch (e) {
                // ignore storage errors
            }
        }
    };

    if (tabButtons.length > 0 && tabPanels.length > 0) {
        tabButtons.forEach((button) => {
            button.addEventListener('click', () => {
                activateTab(button.dataset.tab);
            });
        });
        const autoPreview = document.querySelector('[data-aiassistant-preview-autoshow]');
        const autoSearch = document.querySelector('[data-aiassistant-search-autoshow]');
        let initialTab = tabButtons[0] ? tabButtons[0].dataset.tab : '';
        try {
            const storedTab = sessionStorage.getItem(tabStorageKey);
            if (storedTab) {
                initialTab = storedTab;
            }
        } catch (e) {
            // ignore storage errors
        }
        if (autoPreview) {
            initialTab = 'preview';
            try {
                sessionStorage.setItem(tabStorageKey, 'preview');
            } catch (e) {
                // ignore storage errors
            }
        }
        if (autoSearch) {
            initialTab = 'preview';
            try {
                sessionStorage.setItem(tabStorageKey, 'preview');
            } catch (e) {
                // ignore storage errors
            }
        }
        activateTab(initialTab);
    }

    const getActiveMainTab = () => {
        const activeButton = document.querySelector('.aiassistant-config__tab.is-active');
        return activeButton ? activeButton.dataset.tab || '' : '';
    };

    const activateSearchTab = (tabName) => {
        searchSubtabs.forEach((button) => {
            const isActive = button.dataset.subtab === tabName;
            button.classList.toggle('is-active', isActive);
        });
        searchPanels.forEach((panel) => {
            const isActive = panel.dataset.subtabPanel === tabName;
            panel.classList.toggle('is-active', isActive);
            panel.hidden = !isActive;
        });
        if (tabName) {
            try {
                sessionStorage.setItem(searchTabStorageKey, tabName);
            } catch (e) {
                // ignore storage errors
            }
        }
    };

    const getSeenKey = (storageKey) => {
        try {
            return sessionStorage.getItem(storageKey) || '';
        } catch (e) {
            return '';
        }
    };

    const setSeenKey = (storageKey, issueKey) => {
        if (!issueKey) {
            return;
        }
        try {
            sessionStorage.setItem(storageKey, issueKey);
        } catch (e) {
            // ignore storage errors
        }
    };

    const syncSubtabDot = (dot, storageKey) => {
        if (!dot) {
            return;
        }
        const issueKey = dot.getAttribute('data-aiassistant-issue-key') || '';
        const seenKey = getSeenKey(storageKey);
        dot.hidden = !issueKey || seenKey === issueKey;
    };

    const syncDiagnosticsBadge = () => {
        if (!diagnosticsBadge) {
            return;
        }
        const errorsVisible = !!(errorsDot && !errorsDot.hidden);
        const chatlogVisible = !!(chatlogDot && !chatlogDot.hidden);
        diagnosticsBadge.hidden = !(errorsVisible || chatlogVisible);
    };

    const markSubtabAsSeen = (subtabName) => {
        if (subtabName === 'errors' && errorsDot) {
            setSeenKey(errorsSeenStorageKey, errorsDot.getAttribute('data-aiassistant-issue-key') || '');
            syncSubtabDot(errorsDot, errorsSeenStorageKey);
        }
        if (subtabName === 'chatlog' && chatlogDot) {
            setSeenKey(chatlogSeenStorageKey, chatlogDot.getAttribute('data-aiassistant-issue-key') || '');
            syncSubtabDot(chatlogDot, chatlogSeenStorageKey);
        }
        syncDiagnosticsBadge();
    };

    if (searchSubtabs.length > 0 && searchPanels.length > 0) {
        searchSubtabs.forEach((button) => {
            button.addEventListener('click', () => {
                activateSearchTab(button.dataset.subtab);
                if (
                    getActiveMainTab() === 'preview' &&
                    (button.dataset.subtab === 'errors' || button.dataset.subtab === 'chatlog')
                ) {
                    markSubtabAsSeen(button.dataset.subtab);
                }
            });
        });
        let initialSearchTab = searchSubtabs[0] ? searchSubtabs[0].dataset.subtab : '';
        try {
            const storedSearchTab = sessionStorage.getItem(searchTabStorageKey);
            if (storedSearchTab) {
                initialSearchTab = storedSearchTab;
            }
        } catch (e) {
            // ignore storage errors
        }
        if (document.querySelector('[data-aiassistant-search-autoshow]')) {
            initialSearchTab = 'health';
        }
        if (document.querySelector('[data-subtab-panel="health"]') && document.querySelector('[data-subtab-panel="health"] .aiassistant-config__notice')) {
            initialSearchTab = 'health';
        }
        if (document.querySelector('[data-aiassistant-search-results]')) {
            initialSearchTab = 'search';
        }
        activateSearchTab(initialSearchTab);
        syncSubtabDot(errorsDot, errorsSeenStorageKey);
        syncSubtabDot(chatlogDot, chatlogSeenStorageKey);
        if (getActiveMainTab() === 'preview' && (initialSearchTab === 'errors' || initialSearchTab === 'chatlog')) {
            markSubtabAsSeen(initialSearchTab);
        }
        syncDiagnosticsBadge();
    }

    const activateSettingsTab = (group) => {
        settingsSubtabs.forEach((button) => {
            const isActive = button.dataset.subtab === group;
            button.classList.toggle('is-active', isActive);
        });
        settingsRows.forEach((row) => {
            const isActive = row.dataset.settingsGroup === group;
            row.hidden = !isActive;
        });
        settingsPanels.forEach((panel) => {
            const isActive = panel.dataset.settingsPanel === group;
            panel.hidden = !isActive;
            panel.classList.toggle('is-active', isActive);
        });
        if (group) {
            try {
                sessionStorage.setItem(settingsTabStorageKey, group);
            } catch (e) {
                // ignore storage errors
            }
        }
    };

    if (settingsSubtabs.length > 0 && settingsPanels.length > 0) {
        settingsSubtabs.forEach((button) => {
            button.addEventListener('click', () => {
                activateSettingsTab(button.dataset.subtab);
            });
        });
        let initialSettingsTab = settingsSubtabs[0] ? settingsSubtabs[0].dataset.subtab : '';
        try {
            const storedSettingsTab = sessionStorage.getItem(settingsTabStorageKey);
            if (storedSettingsTab) {
                initialSettingsTab = storedSettingsTab;
            }
        } catch (e) {
            // ignore storage errors
        }
        if (document.querySelector('[data-settings-panel="health"]') && document.querySelector('.aiassistant-config__card--health-check .aiassistant-config__notice')) {
            initialSettingsTab = 'health';
        }
        if (!settingsSubtabs.some((button) => button.dataset.subtab === initialSettingsTab)) {
            initialSettingsTab = settingsSubtabs[0] ? settingsSubtabs[0].dataset.subtab : '';
        }
        activateSettingsTab(initialSettingsTab);
    }

    const activateIndexingTab = (tabName) => {
        indexingSubtabs.forEach((button) => {
            const isActive = button.dataset.subtab === tabName;
            button.classList.toggle('is-active', isActive);
        });
        indexingPanels.forEach((panel) => {
            const isActive = panel.dataset.indexingPanel === tabName;
            panel.hidden = !isActive;
            panel.classList.toggle('is-active', isActive);
        });
        if (tabName) {
            try {
                sessionStorage.setItem(indexingTabStorageKey, tabName);
            } catch (e) {
                // ignore storage errors
            }
        }
    };

    if (indexingSubtabs.length > 0 && indexingPanels.length > 0) {
        indexingSubtabs.forEach((button) => {
            button.addEventListener('click', () => {
                activateIndexingTab(button.dataset.subtab);
            });
        });
        let initialIndexingTab = indexingSubtabs[0] ? indexingSubtabs[0].dataset.subtab : '';
        try {
            const storedIndexingTab = sessionStorage.getItem(indexingTabStorageKey);
            if (storedIndexingTab) {
                initialIndexingTab = storedIndexingTab;
            }
        } catch (e) {
            // ignore storage errors
        }
        activateIndexingTab(initialIndexingTab);
    }

    const autoPreview = document.querySelector('[data-aiassistant-preview-autoshow]');
    if (autoPreview) {
        const dialog = document.getElementById('aiassistant-preview-dialog');
        const content = dialog ? dialog.querySelector('[data-aiassistant-preview-content]') : null;
        const source = document.querySelector('.aiassistant-config__preview-source');
        if (dialog && content && source) {
            content.textContent = source.textContent.trim();
            dialog.showModal();
        }
    }
});
