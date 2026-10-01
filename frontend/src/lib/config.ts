const ENV_BASE = (import.meta.env.VITE_API_BASE as string | undefined) || '';

// Default relatif agar deploy satu domain (nginx sama) jalan tanpa config.
// Isi VITE_API_BASE saat build hanya bila API beda origin (mis. dev :5173 → :8000).
export const API_BASE = ENV_BASE || '/api/v1';

function origin(): string {
	if (typeof window !== 'undefined' && window.location?.origin) return window.location.origin;
	return 'http://localhost:8000';
}

export const BACKEND_ORIGIN = (() => {
	try {
		return new URL(API_BASE, origin()).origin;
	} catch {
		return origin();
	}
})();

export function publicAsset(url: string | null | undefined): string {
	if (!url) return '';
	if (/^https?:\/\//.test(url)) return url;
	if (url.startsWith('/storage/')) return `${BACKEND_ORIGIN}${url}`;
	return url;
}
