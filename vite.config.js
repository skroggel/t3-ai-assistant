import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [vue()],
    define: {
        'process.env.NODE_ENV': JSON.stringify('production'),
    },
    build: {
        lib: {
            entry: 'Resources/Private/Frontend/Vue/ai-assistant-chat.js',
            formats: ['iife'],
            name: 'AiAssistantChatElement',
            fileName: () => 'ai-assistant-chat.js',
        },
        outDir: 'Resources/Public/JavaScript',
        emptyOutDir: false,
    },
});
