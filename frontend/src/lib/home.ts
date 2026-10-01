import { writable } from 'svelte/store';
import { api } from './api';

export interface HomeData {
	businessSettings: Record<string, unknown> | null;
	navLinks: { topnav: Record<string, unknown>[]; footer: Record<string, unknown>[] };
	footerLegalLinks: Record<string, unknown>[];
	hero: Record<string, unknown> | null;
	sectionHeaders: Record<string, Record<string, unknown>>;
	valueProps: Record<string, unknown>[];
	processSteps: Record<string, unknown>[];
	objectionQuestions: Record<string, unknown>[];
	referencePriceCards: Record<string, unknown>[];
	services: Record<string, unknown>[];
	featuredPortfolios: Record<string, unknown>[];
	pricePackages: Record<string, unknown>[];
	mockupOffer: Record<string, unknown> | null;
	faqs: Record<string, unknown>[];
	aboutTimeline: Record<string, unknown>[];
	teamMembers: Record<string, unknown>[];
	clientLogos: Record<string, unknown>[];
	seoSettings: Record<string, unknown> | null;
}

export const home = writable<HomeData | null>(null);
export const homeError = writable<string | null>(null);

let inflight: Promise<HomeData> | null = null;

export function loadHome(): Promise<HomeData> {
	if (!inflight) {
		inflight = api
			.get<HomeData>('/home')
			.then((d) => {
				home.set(d);
				return d;
			})
			.catch((e: unknown) => {
				homeError.set(e instanceof Error ? e.message : 'Gagal memuat.');
				inflight = null;
				throw e;
			});
	}
	return inflight;
}

export function headerOf(d: HomeData | null, key: string): Record<string, unknown> {
	return d?.sectionHeaders?.[key] ?? {};
}

export function str(v: unknown): string {
	return typeof v === 'string' ? v : (v ?? '').toString();
}
