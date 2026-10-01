<script lang="ts">
	import { page } from '$app/stores';
	import { api, ApiError } from '$lib/api';
	import { toast } from '$lib/toast';

	const preset = $derived($page.url.searchParams.get('package') ?? '');
	let form = $state({ package_id: '', name: '', email: '', phone: '', notes: '', related_mockup_order_id: '' });
	let errors = $state<Record<string, string[]>>({});
	let result = $state<{ trackingCode: string; whatsappUrl: string | null } | null>(null);

	$effect(() => {
		if (preset) form.package_id = preset;
	});

	async function submit(e: SubmitEvent): Promise<void> {
		e.preventDefault();
		errors = {};
		try {
			const payload: Record<string, unknown> = {
				package_id: Number(form.package_id),
				name: form.name,
				email: form.email,
				phone: form.phone,
				notes: form.notes || undefined
			};
			result = await api.post('/orders', payload);
			toast('Pesanan dibuat.', 'ok');
			const url = (result as { whatsappUrl: string | null }).whatsappUrl;
			if (url) window.location.href = url;
		} catch (err) {
			if (err instanceof ApiError && err.fields) errors = err.fields;
			toast(err instanceof Error ? err.message : 'Gagal.', 'bad');
		}
	}
</script>

<div class="wrap section">
	<h1>Pesan Paket</h1>
	<p class="muted small">Tanpa bayar di muka — lanjut diskusi via WhatsApp.</p>
	{#if result}
		<div class="alert ok">
			Pesanan tercatat! Kode booking: <strong>{result.trackingCode}</strong>
			{#if result.whatsappUrl} <a href={result.whatsappUrl}>Buka WhatsApp manual</a>{/if}
		</div>
	{:else}
		<form class="form" onsubmit={submit}>
			<label class="f">ID Paket<input bind:value={form.package_id} placeholder="cth. 1" /></label>
			{#if errors.package_id}<span class="ferr">{errors.package_id[0]}</span>{/if}
			<label class="f">Nama<input bind:value={form.name} /></label>
			{#if errors.name}<span class="ferr">{errors.name[0]}</span>{/if}
			<label class="f">Email<input type="email" bind:value={form.email} /></label>
			{#if errors.email}<span class="ferr">{errors.email[0]}</span>{/if}
			<label class="f">No. HP<input bind:value={form.phone} /></label>
			{#if errors.phone}<span class="ferr">{errors.phone[0]}</span>{/if}
			<label class="f">Catatan<textarea bind:value={form.notes}></textarea></label>
			<button class="btn" type="submit">Buat Pesanan</button>
		</form>
	{/if}
</div>
