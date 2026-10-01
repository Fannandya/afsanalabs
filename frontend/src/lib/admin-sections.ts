import type { FieldDef } from '$lib/components/admin/fields';

export interface CrudConfig {
	kind: 'crud';
	title: string;
	endpoint: string;
	fields: FieldDef[];
	columns: string[];
}

export interface SingletonConfig {
	kind: 'singleton';
	title: string;
	endpoint: string;
	fields: FieldDef[];
}

export type SectionConfig = CrudConfig | SingletonConfig | { kind: 'special'; title: string };

const bool = (key: string, label: string): FieldDef => ({ key, label, type: 'checkbox' });
const sortActive: FieldDef[] = [
	{ key: 'sort_order', label: 'Urutan', type: 'number' },
	{ key: 'is_active', label: 'Aktif', type: 'checkbox' }
];

export const SECTIONS: Record<string, SectionConfig> = {
	hero: {
		kind: 'singleton',
		title: 'Hero',
		endpoint: '/admin/hero',
		fields: [
			{ key: 'eyebrow', label: 'Eyebrow', type: 'text' },
			{ key: 'heading', label: 'Heading', type: 'text', required: true },
			{ key: 'subheading', label: 'Subheading', type: 'textarea' },
			{ key: 'cta_label', label: 'Label CTA', type: 'text' },
			{ key: 'cta_target', label: 'Target CTA', type: 'text' },
			{ key: 'trust_badge_text', label: 'Trust badge', type: 'text' },
			{ key: 'image_url', label: 'Gambar', type: 'image', folder: 'hero' },
			{ key: 'text_color', label: 'Warna teks (#RRGGBB)', type: 'text' }
		]
	},
	'section-headers': { kind: 'special', title: 'Section Headers' },
	'value-props': {
		kind: 'crud',
		title: 'Value Props',
		endpoint: '/admin/value-props',
		fields: [
			{ key: 'title', label: 'Judul', type: 'text', required: true },
			{ key: 'description', label: 'Deskripsi', type: 'textarea', required: true },
			...sortActive
		],
		columns: ['title', 'sort_order', 'is_active']
	},
	'process-steps': {
		kind: 'crud',
		title: 'Process Steps',
		endpoint: '/admin/process-steps',
		fields: [
			{ key: 'step_number', label: 'Nomor langkah', type: 'number', required: true },
			{ key: 'icon', label: 'Ikon', type: 'text' },
			{ key: 'title', label: 'Judul', type: 'text', required: true },
			{ key: 'description', label: 'Deskripsi', type: 'textarea', required: true },
			...sortActive
		],
		columns: ['step_number', 'title', 'sort_order', 'is_active']
	},
	objection: { kind: 'special', title: 'Objection & Harga Referensi' },
	services: {
		kind: 'crud',
		title: 'Services',
		endpoint: '/admin/services',
		fields: [
			{ key: 'name', label: 'Nama', type: 'text', required: true },
			{ key: 'description', label: 'Deskripsi', type: 'textarea', required: true },
			{ key: 'icon', label: 'Ikon', type: 'text' },
			...sortActive
		],
		columns: ['name', 'sort_order', 'is_active']
	},
	portfolio: { kind: 'special', title: 'Portfolio & Kategori' },
	'about-timeline': {
		kind: 'crud',
		title: 'About Timeline',
		endpoint: '/admin/about-timeline',
		fields: [
			{ key: 'period', label: 'Periode', type: 'text', required: true },
			{ key: 'title', label: 'Judul', type: 'text', required: true },
			{ key: 'description', label: 'Deskripsi', type: 'textarea', required: true },
			{ key: 'image_url', label: 'Gambar', type: 'image', folder: 'about' },
			...sortActive
		],
		columns: ['period', 'title', 'sort_order', 'is_active']
	},
	team: {
		kind: 'crud',
		title: 'Team',
		endpoint: '/admin/team-members',
		fields: [
			{ key: 'name', label: 'Nama', type: 'text', required: true },
			{ key: 'role', label: 'Peran', type: 'text', required: true },
			{ key: 'photo_url', label: 'Foto', type: 'image', folder: 'team', required: true },
			{ key: 'twitter_url', label: 'Twitter URL', type: 'text' },
			{ key: 'facebook_url', label: 'Facebook URL', type: 'text' },
			{ key: 'linkedin_url', label: 'LinkedIn URL', type: 'text' },
			...sortActive
		],
		columns: ['name', 'role', 'sort_order', 'is_active']
	},
	clients: {
		kind: 'crud',
		title: 'Logo Klien',
		endpoint: '/admin/client-logos',
		fields: [
			{ key: 'name', label: 'Nama', type: 'text', required: true },
			{ key: 'logo_url', label: 'Logo', type: 'image', folder: 'clients', required: true },
			{ key: 'link_url', label: 'Link URL', type: 'text' },
			...sortActive
		],
		columns: ['name', 'sort_order', 'is_active']
	},
	pricing: { kind: 'special', title: 'Pricing & Paket' },
	'mockup-offer': {
		kind: 'singleton',
		title: 'Mockup Offer',
		endpoint: '/admin/mockup-offer',
		fields: [
			{ key: 'eyebrow', label: 'Eyebrow', type: 'text' },
			{ key: 'heading', label: 'Heading', type: 'text', required: true },
			{ key: 'description', label: 'Deskripsi', type: 'textarea', required: true },
			{ key: 'feature_bullets', label: 'Fitur', type: 'json' },
			{ key: 'price', label: 'Harga', type: 'number', required: true },
			{ key: 'cta_label', label: 'Label CTA', type: 'text', required: true },
			{ key: 'image_url', label: 'Gambar', type: 'image', folder: 'mockup' }
		]
	},
	faq: {
		kind: 'crud',
		title: 'FAQ',
		endpoint: '/admin/faqs',
		fields: [
			{ key: 'question', label: 'Pertanyaan', type: 'text', required: true },
			{ key: 'answer', label: 'Jawaban', type: 'textarea', required: true },
			...sortActive
		],
		columns: ['question', 'sort_order', 'is_active']
	}
};

export const HEADER_FIELDS: FieldDef[] = [
	{ key: 'eyebrow_text', label: 'Eyebrow', type: 'text' },
	{ key: 'heading', label: 'Heading', type: 'text', required: true },
	{ key: 'subtitle', label: 'Subtitle', type: 'text' },
	{ key: 'intro_text', label: 'Intro', type: 'textarea' },
	{ key: 'note_text', label: 'Catatan', type: 'textarea' }
];

export const PACKAGE_FIELDS: FieldDef[] = [
	{ key: 'name', label: 'Nama', type: 'text', required: true },
	{ key: 'tagline', label: 'Tagline', type: 'text' },
	{ key: 'price', label: 'Harga (kosong = tanpa angka)', type: 'number' },
	{ key: 'show_price', label: 'Tampilkan harga', type: 'checkbox' },
	{ key: 'features', label: 'Fitur', type: 'json' },
	{ key: 'is_recommended', label: 'Rekomendasi (hanya satu)', type: 'checkbox' },
	{ key: 'cta_label', label: 'Label CTA', type: 'text', required: true },
	{
		key: 'cta_action',
		label: 'Aksi CTA',
		type: 'select',
		options: [
			{ value: 'order', label: 'order → /pesan' },
			{ value: 'contact', label: 'contact → /konsultasi' }
		]
	},
	...sortActive
];
