<script lang="ts">
	import { goto } from '$app/navigation';
	import { api, ensureCsrf, ApiError } from '$lib/api';
	import { toast } from '$lib/toast';

	let form = $state({ email: '', password: '' });
	let errors = $state<Record<string, string[]>>({});
	let busy = $state(false);

	async function login(e: SubmitEvent): Promise<void> {
		e.preventDefault();
		errors = {};
		busy = true;
		try {
			await ensureCsrf();
			await api.post('/admin/login', form);
			toast('Login berhasil.', 'ok');
			goto('/admin');
		} catch (err) {
			if (err instanceof ApiError && err.fields) errors = err.fields;
			toast(err instanceof Error ? err.message : 'Login gagal.', 'bad');
		} finally {
			busy = false;
		}
	}
</script>

<div class="wrap section" style="max-width:480px">
	<h1>Login Admin</h1>
	<form class="form" onsubmit={login}>
		<label class="f">Email<input type="email" bind:value={form.email} /></label>
		{#if errors.email}<span class="ferr">{errors.email[0]}</span>{/if}
		<label class="f">Kata sandi<input type="password" bind:value={form.password} /></label>
		{#if errors.password}<span class="ferr">{errors.password[0]}</span>{/if}
		<button class="btn" type="submit" disabled={busy}>Masuk</button>
	</form>
</div>
