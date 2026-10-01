<script lang="ts">
	import { api } from '$lib/api';
	import { publicAsset } from '$lib/config';
	import { toast } from '$lib/toast';

	let { value = $bindable(''), folder = 'misc', label = 'Gambar' }: {
		value: string;
		folder?: string;
		label?: string;
	} = $props();

	let busy = $state(false);

	async function pick(e: Event): Promise<void> {
		const input = e.target as HTMLInputElement;
		const file = input.files?.[0];
		if (!file) return;
		busy = true;
		try {
			const res = await api.upload(folder, file);
			value = res.url;
			toast('Gambar terunggah.', 'ok');
		} catch (err) {
			toast(err instanceof Error ? err.message : 'Unggah gagal.', 'bad');
		} finally {
			busy = false;
		}
	}
</script>

<label class="f">{label}
	<input type="text" bind:value placeholder="/storage/..." />
</label>
{#if value}
	<img class="cover" style="max-width:280px" src={publicAsset(value)} alt="pratinjau" />
	<div style="display:flex;gap:8px;margin:6px 0">
		<button type="button" class="btn danger small" onclick={() => { value = ''; toast('Gambar dihapus dari form — klik Simpan untuk menerapkan.', 'ok'); }}>
			Hapus gambar
		</button>
	</div>
{/if}
<input type="file" accept=".jpg,.jpeg,.png,.webp" onchange={pick} disabled={busy} />
{#if busy}<p class="small muted">Mengunggah…</p>{/if}
