import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";
import vue from "@vitejs/plugin-vue";
import tailwindcss from '@tailwindcss/vite'


export default defineConfig({
    plugins: [tailwindcss(), symfonyPlugin(), vue()],
    build: {
        emptyOutDir: true,
        assetsDir: "",
        manifest: true,
        rollupOptions: {
            input: {
                app: "./assets/app.js",
            },
        },
    },
});
