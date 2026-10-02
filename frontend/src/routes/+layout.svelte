<script lang="ts">
	import '../app.css';
	import { goto } from '$app/navigation';
	import { page } from '$app/state';
	import { loadHome, home, str } from '$lib/home';
	import Toasts from '$lib/components/ui/Toasts.svelte';

	let { children } = $props();
	const isAdmin = $derived(page.url.pathname.startsWith('/admin'));

	$effect(() => {
		loadHome().catch(() => {});
	});

	function nav(href: string): void {
		goto(href);
	}
</script>

<svelte:head>
	<title>{$home?.seoSettings ? str($home.seoSettings['meta_title']) || 'Jasa Website' : 'Jasa Website'}</title>
	{#if $home?.seoSettings?.['meta_description']}
		<meta name="description" content={str($home.seoSettings['meta_description'])} />
	{/if}
</svelte:head>

<header class="topnav" class:landing-nav={!isAdmin}>
	<div class="wrap">
		<a class="brand" href="/" onclick={(e) => { e.preventDefault(); nav('/'); }}>
			{str($home?.businessSettings?.['business_name']) || 'LIMA AI'}
		</a>
		<nav class="navlinks">
			{#each $home?.navLinks?.topnav ?? [] as l (l.id)}
				<a href={str(l['target'])} onclick={(e) => { e.preventDefault(); nav(str(l['target'])); }}>{str(l['label'])}</a>
			{/each}
		</nav>
	</div>
</header>

<main>{@render children()}</main>

<footer class="footer" class:landing-footer={!isAdmin}>
	<div class="wrap">
		<strong>{str($home?.businessSettings?.['business_name']) || 'LIMA AI'}</strong>
		<p class="muted small">{str($home?.businessSettings?.['footer_tagline'])}</p>
		<nav>
			{#each $home?.navLinks?.footer ?? [] as l (l.id)}
				<a class="small" href={str(l['target'])} onclick={(e) => { e.preventDefault(); nav(str(l['target'])); }}>{str(l['label'])}</a>
			{/each}
			{#each $home?.footerLegalLinks ?? [] as l (l.id)}
				<a class="small" href={str(l['url'])}>{str(l['label'])}</a>
			{/each}
		</nav>
		<p class="small muted">{str($home?.businessSettings?.['footer_copyright'])}</p>
	</div>
</footer>

<Toasts />
