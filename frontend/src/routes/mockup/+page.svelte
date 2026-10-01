<script lang="ts">
	import { api, ApiError } from '$lib/api';
	import { toast } from '$lib/toast';

	let form = $state({ name: '', email: '', phone: '', notes: '' });
	let errors = $state<Record<string, string[]>>({});
	let result = $state<{ trackingCode: string; mockupFee: string; paymentInstructions: string | null } | null>(null);

	async function submit(e: SubmitEvent): Promise<void> {
		e.preventDefault();
		errors = {};
		try {
			result = await api.post('/mockup-requests', form);
			toast('Permintaan mockup tercatat.', 'ok');
		} catch (err) {
			if (err instanceof ApiError && err.fields) errors = err.fields;
			toast(err instanceof Error ? err.message : 'Gagal.', 'bad');
		}
	}
</script>

<div class="wrap section">
	<h1>Minta Mockup</h1>
	<p class="muted small">Berbayar di muka — dapat instruksi transfer setelah mengisi form.</p>
	{#if result}
		<div class="alert ok">
			Kode booking: <strong>{result.trackingCode}</strong><br />
			Biaya mockup: <strong>Rp {Number(result.mockupFee).toLocaleString('id-ID')}</strong><br />
			{#if result.paymentInstructions}<p>{result.paymentInstructions}</p>{/if}
		</div>
	{:else}
		<form class="form" onsubmit={submit}>
			<label class="f">Nama<input bind:value={form.name} /></label>
			{#if errors.name}<span class="ferr">{errors.name[0]}</span>{/if}
			<label class="f">Email<input type="email" bind:value={form.email} /></label>
			{#if errors.email}<span class="ferr">{errors.email[0]}</span>{/if}
			<label class="f">No. HP<input bind:value={form.phone} /></label>
			{#if errors.phone}<span class="ferr">{errors.phone[0]}</span>{/if}
			<label class="f">Catatan<textarea bind:value={form.notes}></textarea></label>
			<button class="btn" type="submit">Minta Mockup</button>
		</form>
	{/if}
</div>
