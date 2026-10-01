<script lang="ts">
	import { api, ensureCsrf, ApiError } from '$lib/api';
	import { toast } from '$lib/toast';

	let form = $state({ current_password: '', password: '', password_confirmation: '' });
	let errors = $state<Record<string, string[]>>({});

	async function save(e: SubmitEvent): Promise<void> {
		e.preventDefault();
		errors = {};
		try {
			await ensureCsrf();
			await api.patch('/admin/password', form);
			toast('Kata sandi diganti.', 'ok');
			form = { current_password: '', password: '', password_confirmation: '' };
		} catch (err) {
			if (err instanceof ApiError && err.fields) errors = err.fields;
			toast(err instanceof Error ? err.message : 'Gagal.', 'bad');
		}
	}
</script>

<h1>Ganti Kata Sandi</h1>
<form class="form" onsubmit={save}>
	<label class="f">Sandi saat ini<input type="password" bind:value={form.current_password} /></label>
	{#if errors.current_password}<span class="ferr">{errors.current_password[0]}</span>{/if}
	<label class="f">Sandi baru (min 8)<input type="password" bind:value={form.password} /></label>
	{#if errors.password}<span class="ferr">{errors.password[0]}</span>{/if}
	<label class="f">Konfirmasi sandi baru<input type="password" bind:value={form.password_confirmation} /></label>
	<button class="btn small" type="submit">Simpan</button>
</form>
