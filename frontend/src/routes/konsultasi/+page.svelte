<script lang="ts">
	import { api, ApiError } from '$lib/api';
	import { toast } from '$lib/toast';

	let form = $state({ name: '', email: '', phone: '', message: '', business_type: '' });
	let errors = $state<Record<string, string[]>>({});
	let done = $state(false);

	async function submit(e: SubmitEvent): Promise<void> {
		e.preventDefault();
		errors = {};
		try {
			await api.post('/consultations', form);
			done = true;
			toast('Konsultasi terkirim.', 'ok');
		} catch (err) {
			if (err instanceof ApiError && err.fields) errors = err.fields;
			toast(err instanceof Error ? err.message : 'Gagal.', 'bad');
		}
	}
</script>

<div class="wrap section">
	<h1>Konsultasi</h1>
	{#if done}
		<div class="alert ok">Terima kasih! Kami akan menghubungi Anda via email/WhatsApp.</div>
	{:else}
		<form class="form" onsubmit={submit}>
			<label class="f">Nama<input bind:value={form.name} /></label>
			{#if errors.name}<span class="ferr">{errors.name[0]}</span>{/if}
			<label class="f">Email<input type="email" bind:value={form.email} /></label>
			{#if errors.email}<span class="ferr">{errors.email[0]}</span>{/if}
			<label class="f">No. HP<input bind:value={form.phone} /></label>
			<label class="f">Jenis usaha<input bind:value={form.business_type} placeholder="cth. kuliner" /></label>
			<label class="f">Pesan<textarea bind:value={form.message}></textarea></label>
			{#if errors.message}<span class="ferr">{errors.message[0]}</span>{/if}
			<button class="btn" type="submit">Kirim Konsultasi</button>
		</form>
	{/if}
</div>
