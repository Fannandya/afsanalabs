<script lang="ts">
	import { api } from '$lib/api';
	import { toast } from '$lib/toast';

	let { kind, title }: { kind: 'contact-messages' | 'consultations'; title: string } = $props();

	let rows = $state<Record<string, unknown>[]>([]);
	let pageNo = $state(1);
	let statusDraft = $state<Record<string | number, string>>({});
	const STATUSES = ['baru', 'dibaca', 'dibalas'];

	async function load(p = 1): Promise<void> {
		pageNo = p;
		try {
			const r = await api.get<{ data: Record<string, unknown>[]; page: number }>(`/admin/${kind}?page=${p}&limit=20`);
			rows = r.data;
			pageNo = r.page;
		} catch (e) {
			toast(e instanceof Error ? e.message : 'Gagal.', 'bad');
		}
	}

	async function setStatus(id: number | string): Promise<void> {
		const status = statusDraft[id];
		if (!status) return;
		try {
			await api.patch(`/admin/${kind}/${id}/status`, { status });
			rows = rows.map((r) => (r['id'] === id ? { ...r, status } : r));
			toast('Status diperbarui.', 'ok');
		} catch (e) {
			toast(e instanceof Error ? e.message : 'Gagal.', 'bad');
		}
	}

	$effect(() => {
		void load(1);
	});
</script>

<h1>{title}</h1>
<div style="overflow-x:auto">
<table class="tbl">
	<thead><tr><th>Nama</th><th>Email</th><th>Pesan</th><th>Status</th><th>Ubah</th></tr></thead>
	<tbody>
		{#each rows as m (m['id'])}
			<tr>
				<td>{String(m['name'])}</td>
				<td>{String(m['email'])}</td>
				<td>{String(m['message']).slice(0, 80)}</td>
				<td>{String(m['status'])}</td>
				<td>
					<select bind:value={statusDraft[m['id'] as string]}>
						<option value="">—</option>
						{#each STATUSES as s}<option value={s}>{s}</option>{/each}
					</select>
					<button class="btn ghost small" onclick={() => setStatus(m['id'] as string)}>OK</button>
				</td>
			</tr>
		{/each}
	</tbody>
</table>
</div>
<div class="toolbar">
	<button class="btn ghost small" disabled={pageNo <= 1} onclick={() => load(pageNo - 1)}>← Prev</button>
	<span class="small muted">Hal {pageNo}</span>
	<button class="btn ghost small" onclick={() => load(pageNo + 1)}>Next →</button>
</div>
