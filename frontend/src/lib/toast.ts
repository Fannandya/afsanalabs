import { writable } from 'svelte/store';

export type ToastKind = 'ok' | 'bad' | 'info';
export interface Toast {
	id: number;
	text: string;
	kind: ToastKind;
}

let seq = 1;
export const toasts = writable<Toast[]>([]);

export function toast(text: string, kind: ToastKind = 'info', ms = 3500): void {
	const id = seq++;
	toasts.update((list) => [...list, { id, text, kind }]);
	setTimeout(() => toasts.update((list) => list.filter((t) => t.id !== id)), ms);
}
