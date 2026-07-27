document.addEventListener('DOMContentLoaded', () => {
    if (typeof AiAssistantChatBox !== 'undefined') {
        AiAssistantChatBox.init(document, window.aiAssistantChatBoxOptions || {});
    }
});
