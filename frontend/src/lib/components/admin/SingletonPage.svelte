<script lang="ts">
	import { api, ApiError } from '$lib/api';
	import { toast } from '$lib/toast';
	import ImageUploadField from '$lib/components/ui/ImageUploadField.svelte';
	import type { FieldDef } from './fields';

	let { title, endpoint, fields }: { title: string; endpoint: string; fields: FieldDef[] } = $props();

	let form = $state<Record<string, unknown>>({});
	let errors = $state<Record<string, string[]>>({});
	let loading = $state(true);

	async function load(): Promise<void> {
		loading = true;
		try {
			const data = await api.get<Record<string, unknown> | null>(endpoint);
			form = { ...(data ?? {}) };
		} catch (e) {
			toast(e instanceof Error ? e.message : 'Gagal memuat.', 'bad');
		} finally {
			loading = false;
		}
	}

	async function save(e: SubmitEvent): Promise<void> {
		e.preventDefault();
		errors = {};
		try {
			const updated = await api.patch<Record<string, unknown>>(endpoint, form);
			form = { ...updated };
			toast('Perubahan tersimpan.', 'ok');
		} catch (err) {
			if (err instanceof ApiError && err.fields) errors = err.fields;
			toast(err instanceof Error ? err.message : 'Simpan gagal.', 'bad');
		}
	}

	$effect(() => {
		void load();
	});
</script>

<h1>{title}</h1>
{#if loading}
	<p class="muted">Memuat…</p>
{:else}
	<form class="form" onsubmit={save}>
		{#each fields as f}
			{#if f.type === 'textarea'}
				<label class="f">{f.label}<textarea bind:value={form[f.key] as string}></textarea></label>
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
						value={Array.isArray(form[f.key]) ? (form[f.key] as string[]).join('\n') : ''}
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
		<button class="btn small" type="submit">Simpan</button>
	</form>
{/if}
