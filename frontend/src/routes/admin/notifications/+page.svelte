<script lang="ts">
	import { api } from '$lib/api';
	import { toast } from '$lib/toast';

	let rows = $state<Record<string, unknown>[]>([]);
	let onlyUnread = $state(false);

	async function load(): Promise<void> {
		try {
			rows = await api.get<Record<string, unknown>[]>(
				`/admin/notifications${onlyUnread ? '?unread=true' : ''}`
			);
		} catch (e) {
			toast(e instanceof Error ? e.message : 'Gagal.', 'bad');
		}
	}

	async function mark(id: number | string): Promise<void> {
		try {
			await api.patch(`/admin/notifications/${id}/read`, {});
			rows = rows.map((r) => (r['id'] === id ? { ...r, is_read: true } : r));
		} catch (e) {
			toast(e instanceof Error ? e.message : 'Gagal.', 'bad');
		}
	}

	$effect(() => {
		void load();
	});
</script>

<h1>Notifikasi</h1>
<label class="f" style="display:flex;flex-direction:row;align-items:center;gap:8px;max-width:280px">
	<input type="checkbox" style="width:auto" bind:checked={onlyUnread} onchange={load} /> Hanya belum dibaca
</label>
<table class="tbl" style="margin-top:12px">
	<thead><tr><th>Tipe</th><th>Pesan</th><th>Status</th><th>Aksi</th></tr></thead>
	<tbody>
		{#each rows as n (n['id'])}
			<tr>
				<td><span class="badge">{String(n['type'])}</span></td>
				<td>{String(n['message'])}</td>
				<td>{n['is_read'] ? 'dibaca' : 'baru'}</td>
				<td>{#if !n['is_read']}<button class="btn ghost small" onclick={() => mark(n['id'] as string)}>Tandai dibaca</button>{/if}</td>
			</tr>
		{/each}
	</tbody>
</table>
