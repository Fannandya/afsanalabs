<script lang="ts">
	import { api } from '$lib/api';
	import { publicAsset } from '$lib/config';
	import { goto } from '$app/navigation';

	let categories = $state<Record<string, unknown>[]>([]);
	let items = $state<Record<string, unknown>[]>([]);
	let testimonials = $state<Record<string, unknown>[]>([]);
	let active = $state<string>('');

	async function load(cat = ''): Promise<void> {
		active = cat;
		const q = cat ? `?category=${encodeURIComponent(cat)}` : '';
		[items, categories, testimonials] = await Promise.all([
			api.get<Record<string, unknown>[]>(`/portfolios${q}`),
			categories.length ? categories : api.get<Record<string, unknown>[]>('/categories'),
			testimonials.length ? testimonials : api.get<Record<string, unknown>[]>('/testimonials')
		]);
	}

	$effect(() => {
		void load();
	});
</script>

<div class="wrap section">
	<h1>Portofolio</h1>
	<div class="toolbar">
		<button class="btn ghost small" onclick={() => load('')}>Semua</button>
		{#each categories as c (c.id)}
			<button class="btn ghost small" onclick={() => load(String(c['slug']))}>{String(c['name'])}</button>
		{/each}
	</div>
	<div class="grid c3">
		{#each items as p (p.id)}
			<div class="card">
				{#if p['image_url']}<img class="cover" src={publicAsset(String(p['image_url']))} alt={String(p['title'])} />{/if}
				<h3>{String(p['title'])}</h3>
				<p class="muted small">{String(p['description'] ?? '')}</p>
			</div>
		{/each}
	</div>
	{#if items.length === 0}<p class="muted">Belum ada karya.</p>{/if}

	<h2 style="margin-top:36px">Testimoni</h2>
	<div class="grid c3">
		{#each testimonials as t (t.id)}
			<div class="card"><p>“{String(t['content'])}”</p><p class="small muted">— {String(t['client_name'])} · ★{String(t['rating'])}</p></div>
		{/each}
	</div>
	<p><button class="btn ghost" onclick={() => goto('/')}>← Beranda</button></p>
</div>
