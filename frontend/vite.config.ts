import adapter from '@sveltejs/adapter-static';
import { sveltekit } from '@sveltejs/kit/vite';
import { defineConfig } from 'vite';

export default defineConfig({
	plugins: [
		sveltekit({
			compilerOptions: {
				runes: ({ filename }) =>
					filename.split(/[/\\]/).includes('node_modules') ? undefined : true
			},
			adapter: adapter({ fallback: 'index.html' })
		})
	],
	server: {
		// Dev lokal: teruskan API/storage/auth ke backend :8000 agar base
		// relatif (/api/v1) tetap jalan tanpa .env. Produksi via nginx.
		proxy: {
			'/api': { target: 'http://localhost:8000', changeOrigin: true },
			'/sanctum': { target: 'http://localhost:8000', changeOrigin: true },
			'/storage': { target: 'http://localhost:8000', changeOrigin: true }
		}
	}
});
