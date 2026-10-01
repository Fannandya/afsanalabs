<script lang="ts">
	import { page } from '$app/stores';
	import { SECTIONS, HEADER_FIELDS, PACKAGE_FIELDS } from '$lib/admin-sections';
	import CrudPage from '$lib/components/admin/CrudPage.svelte';
	import SingletonPage from '$lib/components/admin/SingletonPage.svelte';
	import HeaderEditor from '$lib/components/admin/HeaderEditor.svelte';
	import { api, ApiError } from '$lib/api';
	import { toast } from '$lib/toast';
	import type { FieldDef } from '$lib/components/admin/fields';

	const section = $derived($page.params.section ?? '');
	const cfg = $derived(SECTIONS[section]);

	// Special: section-headers editor
	let headers = $state<Record<string, unknown>[]>([]);
	let editingKey = $state<string | null>(null);
	let hform = $state<Record<string, unknown>>({});
	let herrors = $state<Record<string, string[]>>({});

	async function loadHeaders(): Promise<void> {
		try {
			headers = await api.get<Record<string, unknown>[]>('/admin/section-headers');
		} catch (e) {
			toast(e instanceof Error ? e.message : 'Gagal.', 'bad');
		}
	}

	function editHeader(row: Record<string, unknown>): void {
		editingKey = String(row['section_key']);
		hform = { ...row };
		herrors = {};
	}

	async function saveHeader(e: SubmitEvent): Promise<void> {
		e.preventDefault();
		herrors = {};
		try {
			const updated = await api.patch<Record<string, unknown>>(`/admin/section-headers/${editingKey}`, hform);
			headers = headers.map((h) => (h['section_key'] === editingKey ? updated : h));
			toast('Header tersimpan.', 'ok');
			editingKey = null;
		} catch (err) {
			if (err instanceof ApiError && err.fields) herrors = err.fields;
			toast(err instanceof Error ? err.message : 'Gagal.', 'bad');
		}
	}

	$effect(() => {
		if (cfg?.kind === 'special' && section === 'section-headers') void loadHeaders();
	});
</script>

{#if !cfg}
	<h1>Section tidak dikenal</h1>
{:else if cfg.kind === 'crud'}
	<CrudPage title={cfg.title} endpoint={cfg.endpoint} fields={cfg.fields} columns={cfg.columns} />
{:else if cfg.kind === 'singleton'}
	<SingletonPage title={cfg.title} endpoint={cfg.endpoint} fields={cfg.fields} />
{:else if section === 'section-headers'}
	<h1>Section Headers</h1>
	<table class="tbl">
		<thead><tr><th>Key</th><th>Heading</th><th>Aksi</th></tr></thead>
		<tbody>
			{#each headers as h (h['section_key'])}
				<tr><td>{String(h['section_key'])}</td><td>{String(h['heading'])}</td><td><button class="btn ghost small" onclick={() => editHeader(h)}>Ubah</button></td></tr>
			{/each}
		</tbody>
	</table>
	{#if editingKey}
		<div class="card" style="margin-top:16px">
			<h3 style="margin-top:0">Ubah: {editingKey}</h3>
			<form class="form" onsubmit={saveHeader}>
				{#each HEADER_FIELDS as f (f.key)}
					{#if f.type === 'textarea'}
						<label class="f">{f.label}<textarea bind:value={hform[f.key] as string}></textarea></label>
					{:else}
						<label class="f">{f.label}<input bind:value={hform[f.key] as string} /></label>
					{/if}
					{#if herrors[f.key]}<span class="ferr">{herrors[f.key][0]}</span>{/if}
				{/each}
				<div style="display:flex;gap:10px">
					<button class="btn small" type="submit">Simpan</button>
					<button class="btn ghost small" type="button" onclick={() => (editingKey = null)}>Batal</button>
				</div>
			</form>
		</div>
	{/if}
{:else if section === 'objection'}
	<h1>Objection & Harga Referensi</h1>
	<HeaderEditor sectionKey="objection" title="Header section" />
	<h2 style="margin-top:24px">Pertanyaan</h2>
	<CrudPage
		title="Objection Questions"
		endpoint="/admin/objection-questions"
		fields={[
			{ key: 'question', label: 'Pertanyaan', type: 'text', required: true },
			{ key: 'answer', label: 'Jawaban', type: 'textarea', required: true },
			{ key: 'sort_order', label: 'Urutan', type: 'number' },
			{ key: 'is_active', label: 'Aktif', type: 'checkbox' }
		]}
		columns={['question', 'sort_order', 'is_active']}
	 />
	<h2 style="margin-top:24px">Kartu harga referensi</h2>
	<CrudPage
		title="Reference Price Cards"
		endpoint="/admin/reference-price-cards"
		fields={[
			{ key: 'label', label: 'Label', type: 'text', required: true },
			{ key: 'price_value', label: 'Nilai harga', type: 'text', required: true },
			{ key: 'price_note', label: 'Catatan', type: 'text' },
			{ key: 'sort_order', label: 'Urutan', type: 'number' },
			{ key: 'is_active', label: 'Aktif', type: 'checkbox' }
		]}
		columns={['label', 'price_value', 'sort_order', 'is_active']}
	 />
{:else if section === 'portfolio'}
	<h1>Portfolio & Kategori</h1>
	<HeaderEditor sectionKey="portfolio" title="Header section" />
	<h2 style="margin-top:24px">Kategori</h2>
	<CrudPage
		title="Categories"
		endpoint="/admin/categories"
		fields={[
			{ key: 'name', label: 'Nama', type: 'text', required: true },
			{ key: 'slug', label: 'Slug', type: 'text', required: true },
			{ key: 'sort_order', label: 'Urutan', type: 'number' }
		]}
		columns={['name', 'slug', 'sort_order']}
	 />
	<h2 style="margin-top:24px">Karya</h2>
	<CrudPage
		title="Portfolios"
		endpoint="/admin/portfolios"
		fields={[
			{ key: 'category_id', label: 'ID Kategori', type: 'number' },
			{ key: 'title', label: 'Judul', type: 'text', required: true },
			{ key: 'image_url', label: 'Gambar', type: 'image', folder: 'portfolio', required: true },
			{ key: 'description', label: 'Deskripsi', type: 'textarea' },
			{ key: 'client_name', label: 'Klien', type: 'text' },
			{ key: 'project_url', label: 'URL Proyek', type: 'text' },
			{ key: 'is_featured', label: 'Tampilkan di Beranda', type: 'checkbox' },
			{ key: 'is_active', label: 'Aktif', type: 'checkbox' },
			{ key: 'sort_order', label: 'Urutan', type: 'number' }
		]}
		columns={['title', 'is_featured', 'is_active', 'sort_order']}
	 />
{:else if section === 'pricing'}
	<h1>Pricing & Paket</h1>
	<HeaderEditor sectionKey="pricing" title="Header section" />
	<h2 style="margin-top:24px">Paket</h2>
	<CrudPage title="Price Packages" endpoint="/admin/packages" fields={PACKAGE_FIELDS} columns={['name', 'price', 'is_recommended', 'cta_action', 'is_active']} />
{/if}
