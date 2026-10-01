<script lang="ts">
	import { api, ApiError } from '$lib/api';
	import { toast } from '$lib/toast';

	let rows = $state<Record<string, unknown>[]>([]);
	let page = $state(1);
	let limit = 20;
	let statusDraft = $state<Record<string | number, string>>({});

	const STATUSES = ['menunggu_konfirmasi', 'menunggu_pembayaran', 'dalam_pengerjaan', 'selesai', 'dibatalkan'];

	async function load(p = 1): Promise<void> {
		page = p;
		try {
			const r = await api.get<{ data: Record<string, unknown>[]; page: number; limit: number }>(
				`/admin/orders?page=${p}&limit=${limit}`
			);
			rows = r.data;
			page = r.page;
		} catch (e) {
			toast(e instanceof Error ? e.message : 'Gagal.', 'bad');
		}
	}

	async function setStatus(id: number | string): Promise<void> {
		const status = statusDraft[id];
		if (!status) return;
		try {
			const updated = await api.patch<Record<string, unknown>>(`/admin/orders/${id}/status`, { status });
			rows = rows.map((r) => (r['id'] === id ? updated : r));
			toast('Status diperbarui.', 'ok');
		} catch (err) {
			toast(err instanceof ApiError ? err.message : 'Gagal.', 'bad');
		}
	}

	function badge(t: unknown): string {
		return t === 'mockup' ? 'badge amber' : 'badge';
	}

	$effect(() => {
		void load(1);
	});
</script>

<h1>Pesanan</h1>
<div style="overflow-x:auto">
<table class="tbl">
	<thead><tr><th>Kode</th><th>Tipe</th><th>Nama</th><th>Status</th><th>Ubah</th></tr></thead>
	<tbody>
		{#each rows as o (o['id'])}
			<tr>
				<td>{String(o['tracking_code'])}</td>
				<td><span class={badge(o['order_type'])}>{String(o['order_type'])}</span></td>
				<td>{String(o['name'])}</td>
				<td>{String(o['status'])}</td>
				<td>
					<select bind:value={statusDraft[o['id'] as string]}>
						<option value="">—</option>
						{#each STATUSES as s}<option value={s}>{s}</option>{/each}
					</select>
					<button class="btn ghost small" onclick={() => setStatus(o['id'] as string)}>OK</button>
				</td>
			</tr>
		{/each}
	</tbody>
</table>
</div>
<div class="toolbar">
	<button class="btn ghost small" disabled={page <= 1} onclick={() => load(page - 1)}>← Prev</button>
	<span class="small muted">Hal {page}</span>
	<button class="btn ghost small" onclick={() => load(page + 1)}>Next →</button>
</div>
