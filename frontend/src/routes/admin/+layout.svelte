<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import { api } from '$lib/api';
	import { toast } from '$lib/toast';
	import Toasts from '$lib/components/ui/Toasts.svelte';
	import '../../app.css';

	let { children } = $props();
	const path = $derived($page.url.pathname);

	const groups: { label: string; links: { href: string; label: string }[] }[] = [
		{
			label: 'Utama',
			links: [
				{ href: '/admin', label: 'Dashboard' },
				{ href: '/admin/orders', label: 'Pesanan' },
				{ href: '/admin/notifications', label: 'Notifikasi' },
				{ href: '/admin/messages/contact', label: 'Pesan Kontak' },
				{ href: '/admin/messages/consultations', label: 'Konsultasi Masuk' }
			]
		},
		{
			label: 'Konten',
			links: [
				{ href: '/admin/content/hero', label: 'Hero' },
				{ href: '/admin/content/section-headers', label: 'Section Headers' },
				{ href: '/admin/content/value-props', label: 'Value Props' },
				{ href: '/admin/content/process-steps', label: 'Process Steps' },
				{ href: '/admin/content/objection', label: 'Objection' },
				{ href: '/admin/content/services', label: 'Services' },
				{ href: '/admin/content/portfolio', label: 'Portfolio' },
				{ href: '/admin/content/about-timeline', label: 'About Timeline' },
				{ href: '/admin/content/team', label: 'Team' },
				{ href: '/admin/content/clients', label: 'Clients' },
				{ href: '/admin/content/pricing', label: 'Pricing' },
				{ href: '/admin/content/mockup-offer', label: 'Mockup Offer' },
				{ href: '/admin/content/faq', label: 'FAQ' }
			]
		},
		{
			label: 'Pengaturan',
			links: [
				{ href: '/admin/settings/business', label: 'Bisnis & Navigasi' },
				{ href: '/admin/settings/seo', label: 'SEO' },
				{ href: '/admin/settings/testimonials', label: 'Testimoni' },
				{ href: '/admin/settings/password', label: 'Ganti Kata Sandi' },
				{ href: '/admin/settings/notifications', label: 'Preferensi Notifikasi' }
			]
		}
	];

	async function logout(): Promise<void> {
		try {
			await api.post('/admin/logout');
		} catch {
			// abaikan
		}
		toast('Logout.', 'ok');
		goto('/admin/login');
	}
</script>

{#if path === '/admin/login'}
	<main style="min-height:100vh">{@render children()}</main>
{:else}
	<div class="admin-shell">
		<aside class="sidenav">
			<strong style="padding:8px 10px">Admin</strong>
			{#each groups as g}
				<div class="grp">{g.label}</div>
				{#each g.links as l}
					<a href={l.href} class:active={path === l.href}>{l.label}</a>
				{/each}
			{/each}
			<div class="grp">Sesi</div>
			<a href="/" >← Situs</a>
			<a href="#logout" onclick={(e) => { e.preventDefault(); logout(); }}>Logout</a>
		</aside>
		<main class="admin-main">{@render children()}</main>
	</div>
{/if}

<Toasts />
