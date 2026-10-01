<script lang="ts">
	import { api } from '$lib/api';

	let stats = $state({ orders: 0, unread: 0 });
	let recent = $state<Record<string, unknown>[]>([]);

	$effect(() => {
		(async () => {
			try {
				const [o, n] = await Promise.all([
					api.get<{ data: Record<string, unknown>[]; page: number; limit: number }>('/admin/orders?limit=5'),
					api.get<Record<string, unknown>[]>('/admin/notifications?unread=true')
				]);
				recent = o.data;
				stats = { orders: o.data.length, unread: n.length };
			} catch {
				// belum login — api.ts sudah redirect ke /admin/login
			}
		})();
	});
</script>

<h1>Dashboard</h1>
<div class="grid c3">
	<div class="card"><h3>Pesanan terbaru</h3><p><strong>{stats.orders}</strong> (5 terakhir)</p><a class="small" href="/admin/orders">Kelola →</a></div>
	<div class="card"><h3>Belum dibaca</h3><p><strong>{stats.unread}</strong> notifikasi</p><a class="small" href="/admin/notifications">Lihat →</a></div>
	<div class="card"><h3>Konten</h3><p class="small muted">Kelola homepage per section.</p><a class="small" href="/admin/content/hero">Mulai →</a></div>
</div>
<h2 style="margin-top:24px">Pesanan terbaru</h2>
<table class="tbl">
	<thead><tr><th>Kode</th><th>Tipe</th><th>Status</th></tr></thead>
	<tbody>
		{#each recent as o (o['id'])}
			<tr><td>{String(o['tracking_code'])}</td><td>{String(o['order_type'])}</td><td>{String(o['status'])}</td></tr>
		{/each}
	</tbody>
</table>
