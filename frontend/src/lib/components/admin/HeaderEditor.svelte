<script lang="ts">
	import { api, ApiError } from '$lib/api';
	import { toast } from '$lib/toast';
	import { HEADER_FIELDS } from '$lib/admin-sections';

	let { sectionKey, title }: { sectionKey: string; title: string } = $props();

	let form = $state<Record<string, unknown>>({});
	let errors = $state<Record<string, string[]>>({});
	let loading = $state(true);

	async function load(): Promise<void> {
		loading = true;
		try {
			const all = await api.get<Record<string, unknown>[]>('/admin/section-headers');
			const row = all.find((h) => h['section_key'] === sectionKey) ?? {};
			form = { ...row };
		} catch (e) {
			toast(e instanceof Error ? e.message : 'Gagal.', 'bad');
		} finally {
			loading = false;
		}
	}

	async function save(e: SubmitEvent): Promise<void> {
		e.preventDefault();
		errors = {};
		try {
			const updated = await api.patch<Record<string, unknown>>(`/admin/section-headers/${sectionKey}`, {
				heading: form['heading'],
				subtitle: form['subtitle'] ?? null,
				intro_text: form['intro_text'] ?? null,
				note_text: form['note_text'] ?? null
			});
			form = { ...updated };
			toast('Header tersimpan.', 'ok');
		} catch (err) {
			if (err instanceof ApiError && err.fields) errors = err.fields;
			toast(err instanceof Error ? err.message : 'Gagal.', 'bad');
		}
	}

	$effect(() => {
		void load();
	});
</script>

<h2>{title}</h2>
{#if loading}
	<p class="muted">Memuat…</p>
{:else}
	<form class="form" onsubmit={save}>
		{#each HEADER_FIELDS as f (f.key)}
			{#if f.type === 'textarea'}
				<label class="f">{f.label}<textarea bind:value={form[f.key] as string}></textarea></label>
			{:else}
				<label class="f">{f.label}<input bind:value={form[f.key] as string} /></label>
			{/if}
			{#if errors[f.key]}<span class="ferr">{errors[f.key][0]}</span>{/if}
		{/each}
		<button class="btn small" type="submit">Simpan Header</button>
	</form>
{/if}
