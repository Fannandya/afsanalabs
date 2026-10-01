<script lang="ts">
	import { api } from '$lib/api';
	import { toast } from '$lib/toast';

	let prefs = $state<Record<string, boolean>>({
		notify_new_order: true,
		notify_contact_message: true,
		notify_consultation: true,
		notify_mockup_request: true
	});

	$effect(() => {
		(async () => {
			try {
				const d = await api.get<Record<string, boolean> | null>('/admin/notifications/preferences');
				if (d) prefs = { ...prefs, ...d };
			} catch (e) {
				toast(e instanceof Error ? e.message : 'Gagal.', 'bad');
			}
		})();
	});

	async function save(): Promise<void> {
		try {
			await api.patch('/admin/notifications/preferences', prefs);
			toast('Preferensi tersimpan.', 'ok');
		} catch (e) {
			toast(e instanceof Error ? e.message : 'Gagal.', 'bad');
		}
	}
</script>

<h1>Preferensi Notifikasi Email</h1>
{#each Object.keys(prefs) as k (k)}
	<label class="f" style="display:flex;flex-direction:row;align-items:center;gap:8px">
		<input type="checkbox" style="width:auto" bind:checked={prefs[k]} /> {k}
	</label>
{/each}
<button class="btn small" onclick={save}>Simpan</button>
