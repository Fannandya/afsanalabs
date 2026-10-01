<script lang="ts">
	import { api } from '$lib/api';

	let code = $state('');
	let result = $state<Record<string, unknown> | null>(null);
	let notfound = $state(false);

	async function track(e: SubmitEvent): Promise<void> {
		e.preventDefault();
		notfound = false;
		result = null;
		try {
			const r = await api.get<{ tracking_code: string; order_type: string; status: string; created_at: string }>(
				`/orders/track/${encodeURIComponent(code.trim())}`
			);
			result = r as unknown as Record<string, unknown>;
		} catch {
			notfound = true;
		}
	}
</script>

<div class="wrap section">
	<h1>Lacak Pesanan</h1>
	<form class="form" onsubmit={track}>
		<label class="f">Kode booking<input bind:value={code} placeholder="ORD-XXXXXX" /></label>
		<button class="btn" type="submit">Lacak</button>
	</form>
	{#if result}
		<div class="card" style="margin-top:14px;max-width:560px">
			<p><strong>{String(result['tracking_code'])}</strong></p>
			<p><span class="badge">{String(result['order_type'])}</span> <span class="badge green">{String(result['status'])}</span></p>
			<p class="small muted">{String(result['created_at'])}</p>
		</div>
	{/if}
	{#if notfound}<div class="alert bad" style="margin-top:14px;max-width:560px">Kode tidak ditemukan.</div>{/if}
</div>
