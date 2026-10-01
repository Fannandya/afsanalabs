import { goto } from '$app/navigation';
import { API_BASE, BACKEND_ORIGIN } from './config';

export class ApiError extends Error {
	status: number;
	fields?: Record<string, string[]>;
	code?: string;
	constructor(status: number, message: string, fields?: Record<string, string[]>, code?: string) {
		super(message);
		this.status = status;
		this.fields = fields;
		this.code = code;
	}
}

async function parseBody(res: Response): Promise<unknown> {
	const text = await res.text();
	if (!text) return undefined;
	try {
		return JSON.parse(text);
	} catch {
		return text;
	}
}

function errorFrom(status: number, body: unknown): ApiError {
	const err =
		typeof body === 'object' && body !== null && 'error' in body
			? (body as { error: { message?: string; code?: string; fields?: Record<string, string[]> } }).error
			: null;
	return new ApiError(status, err?.message ?? `Request gagal (${status}).`, err?.fields, err?.code);
}

export async function ensureCsrf(): Promise<void> {
	await fetch(`${BACKEND_ORIGIN}/sanctum/csrf-cookie`, { credentials: 'include' });
}

function xsrfToken(): string | null {
	if (typeof document === 'undefined') return null;
	const found = document.cookie
		.split(';')
		.map((c) => c.trim())
		.find((c) => c.startsWith('XSRF-TOKEN='));
	if (!found) return null;
	try {
		return decodeURIComponent(found.slice('XSRF-TOKEN='.length));
	} catch {
		return found.slice('XSRF-TOKEN='.length);
	}
}

export async function request<T>(path: string, init: RequestInit = {}): Promise<T> {
	const headers = new Headers(init.headers);
	const isForm = init.body instanceof FormData;
	if (!isForm && !headers.has('Content-Type')) headers.set('Content-Type', 'application/json');
	if (!headers.has('Accept')) headers.set('Accept', 'application/json');
	const method = (init.method ?? 'GET').toUpperCase();
	if (method !== 'GET' && method !== 'HEAD' && !headers.has('X-XSRF-TOKEN')) {
		const token = xsrfToken();
		if (token) headers.set('X-XSRF-TOKEN', token);
	}

	const res = await fetch(`${API_BASE}${path}`, { ...init, headers, credentials: 'include' });
	if (res.status === 204) return undefined as T;
	const body = await parseBody(res);
	if (!res.ok) {
		if (res.status === 401 && typeof window !== 'undefined' && window.location.pathname.startsWith('/admin')) {
			const p = window.location.pathname;
			if (p !== '/admin/login') goto('/admin/login');
		}
		throw errorFrom(res.status, body);
	}
	// Unwrap envelope {data} — KECUALI respons paginasi {data,page,limit}
	// yang dibutuhkan utuh oleh halaman admin orders/messages.
	if (
		typeof body === 'object' &&
		body !== null &&
		'data' in body &&
		!('page' in (body as Record<string, unknown>))
	) {
		return (body as { data: T }).data as T;
	}
	return body as T;
}

export const api = {
	get: <T>(path: string) => request<T>(path),
	post: <T>(path: string, payload?: unknown) =>
		request<T>(path, { method: 'POST', body: payload === undefined ? undefined : JSON.stringify(payload) }),
	patch: <T>(path: string, payload?: unknown) =>
		request<T>(path, { method: 'PATCH', body: payload === undefined ? undefined : JSON.stringify(payload) }),
	del: (path: string) => request<void>(path, { method: 'DELETE' }),
	upload: (folder: string, file: File) => {
		const fd = new FormData();
		fd.append('file', file);
		fd.append('folder', folder);
		return request<{ url: string }>('/admin/uploads', { method: 'POST', body: fd });
	}
};
