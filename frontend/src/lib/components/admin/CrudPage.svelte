<script lang="ts">
	import { api, ApiError } from '$lib/api';
	import { toast } from '$lib/toast';
	import ConfirmDialog from '$lib/components/ui/ConfirmDialog.svelte';
	import ImageUploadField from '$lib/components/ui/ImageUploadField.svelte';
	import type { FieldDef } from './fields';

	let { title, endpoint, fields, columns }: {
		title: string;
		endpoint: string;
		fields: FieldDef[];
		columns: string[];
	} = $props();

	let rows = $state<Record<string, unknown>[]>([]);
	let loading = $state(true);
	let editing = $state<Record<string, unknown> | null>(null);
	let form = $state<Record<string, unknown>>({});
	let errors = $state<Record<string, string[]>>({});
	let confirm: { open: () => void } | undefined = $state();
	let pendingDelete: number | string | null = $state(null);

	async function load(): Promise<void> {
		loading = true;
		try {
			rows = await api.get<Record<string, unknown>[]>(endpoint);
		} catch (e) {
			toast(e instanceof Error ? e.message : 'Gagal memuat.', 'bad');
		} finally {
			loading = false;
		}
	}

	function blank(): Record<string, unknown> {
		const o: Record<string, unknown> = {};
		for (const f of fields) o[f.key] = f.type === 'checkbox' ? false : '';
		return o;
	}

	function startCreate(): void {
		editing = null;
		form = blank();
		errors = {};
	}

	function startEdit(row: Record<string, unknown>): void {
		editing = row;
		form = { ...row };
		errors = {};
	}

	function cell(row: Record<string, unknown>, key: string): string {
		const v = row[key];
		if (typeof v === 'boolean') return v ? 'Ya' : '—';
		if (Array.isArray(v)) return v.join(', ');
		if (v === null || v === undefined || v === '') return '—';
		const s = String(v);
		return s.length > 60 ? s.slice(0, 60) + '…' : s;
	}

	async function save(e: SubmitEvent): Promise<void> {
		e.preventDefault();
		errors = {};
		try {
			if (editing) {
				const id = (editing as { id: number | string }).id;
				const updated = await api.patch<Record<string, unknown>>(`${endpoint}/${id}`, form);
				rows = rows.map((r) => ((r as { id: unknown }).id === id ? updated : r));
				toast('Perubahan tersimpan.', 'ok');
			} else {
				const created = await api.post<Record<string, unknown>>(endpoint, form);
				rows = [...rows, created];
				toast('Data ditambahkan.', 'ok');
			}
			editing = null;
			form = blank();
		} catch (err) {
			if (err instanceof ApiError && err.fields) errors = err.fields;
			toast(err instanceof Error ? err.message : 'Simpan gagal.', 'bad');
		}
	}

	function askDelete(row: Record<string, unknown>): void {
		pendingDelete = (row as { id: number | string }).id;
		confirm?.open();
	}

	async function doDelete(): Promise<void> {
		if (pendingDelete === null) return;
		try {
			await api.del(`${endpoint}/${pendingDelete}`);
			rows = rows.filter((r) => (r as { id: unknown }).id !== pendingDelete);
			toast('Data dihapus.', 'ok');
		} catch (e) {
			toast(e instanceof Error ? e.message : 'Hapus gagal.', 'bad');
		}
	}

	$effect(() => {
		void load();
	});
</script>

<h1>{title}</h1>
<div class="toolbar">
	<button class="btn small" onclick={startCreate}>+ Tambah</button>
	<span class="small muted">{rows.length} data</span>
</div>

{#if loading}
	<p class="muted">Memuat…</p>
{:else if rows.length === 0}
	<p class="muted">Belum ada data.</p>
{:else}
	<div style="overflow-x:auto">
		<table class="tbl">
			<thead><tr>{#each columns as c}<th>{c}</th>{/each}<th>Aksi</th></tr></thead>
			<tbody>
				{#each rows as row (row.id)}
					<tr>
						{#each columns as c}<td>{cell(row, c)}</td>{/each}
						<td style="white-space:nowrap">
							<button class="btn ghost small" onclick={() => startEdit(row)}>Ubah</button>
							<button class="btn danger small" onclick={() => askDelete(row)}>Hapus</button>
						</td>
					</tr>
				{/each}
			</tbody>
		</table>
	</div>
{/if}

{#if editing !== null || Object.values(form).some((v) => v !== '' && v !== false)}
	<div class="card" style="margin-top:18px">
		<h3 style="margin-top:0">{editing ? 'Ubah data' : 'Tambah data'}</h3>
		<form class="form" onsubmit={save}>
			{#each fields as f}
				{#if f.type === 'textarea'}
					<label class="f">{f.label}
						<textarea bind:value={form[f.key] as string}></textarea>
					</label>
				{:else if f.type === 'checkbox'}
					<label class="f" style="display:flex;flex-direction:row;align-items:center;gap:8px">
						<input type="checkbox" style="width:auto" bind:checked={form[f.key] as boolean} /> {f.label}
					</label>
				{:else if f.type === 'select'}
					<label class="f">{f.label}
						<select bind:value={form[f.key] as string}>
							{#each f.options ?? [] as o}<option value={o.value}>{o.label}</option>{/each}
						</select>
					</label>
				{:else if f.type === 'image'}
					<ImageUploadField bind:value={form[f.key] as string} folder={f.folder ?? 'misc'} label={f.label} />
				{:else if f.type === 'json'}
					<label class="f">{f.label} (satu per baris)
						<textarea
							value={Array.isArray(form[f.key]) ? (form[f.key] as string[]).join('\n') : (form[f.key] as string) ?? ''}
							oninput={(e) => {
								form[f.key] = (e.target as HTMLTextAreaElement).value.split('\n').map((s) => s.trim()).filter(Boolean);
							}}
						></textarea>
					</label>
				{:else}
					<label class="f">{f.label}
						<input type={f.type === 'number' ? 'number' : 'text'} bind:value={form[f.key] as string} />
					</label>
				{/if}
				{#if errors[f.key]}<span class="ferr">{errors[f.key][0]}</span>{/if}
			{/each}
			<div style="display:flex;gap:10px">
				<button class="btn small" type="submit">Simpan</button>
				<button class="btn ghost small" type="button" onclick={() => { editing = null; form = blank(); }}>Batal</button>
			</div>
		</form>
	</div>
{/if}

<ConfirmDialog bind:this={confirm} title="Hapus data ini?" onConfirm={doDelete} />
