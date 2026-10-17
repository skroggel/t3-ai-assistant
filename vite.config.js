import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    define: {
        'process.env.NODE_ENV': JSON.stringify('production'),
    },
    build: {
        lib: {
            entry: 'Resources/Private/Frontend/Vue/ai-assistant-chat.js',
            formats: ['iife'],
            name: 'AiAssistantChatElement',
            fileName: () => 'JavaScript/ai-assistant-chat.js',
            cssFileName: 'Css/t3-ai-assistant-frontend',
        },
        // combine CSS to ONE file
        cssCodeSplit: false,
        outDir: 'Resources/Public',
        emptyOutDir: false,
    },
    plugins: [
        // treat as SingleFileComponent while compiling (only while compiling!)
        vue({
            customElement: false,
        }),
    ],
});
