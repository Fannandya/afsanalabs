export const API_BASE =
	(import.meta.env.VITE_API_BASE as string | undefined) ?? 'http://localhost:8000/api/v1';

export const BACKEND_ORIGIN = (() => {
	try {
		return new URL(API_BASE).origin;
	} catch {
		return 'http://localhost:8000';
	}
})();

export function publicAsset(url: string | null | undefined): string {
	if (!url) return '';
	if (/^https?:\/\//.test(url)) return url;
	if (url.startsWith('/storage/')) return `${BACKEND_ORIGIN}${url}`;
	return url;
}
