<script lang="ts">
	import { goto } from '$app/navigation';
	import { home, headerOf, str } from '$lib/home';
	import { publicAsset } from '$lib/config';

	const d = $derived($home);
	const hero = $derived((d?.hero ?? {}) as Record<string, unknown>);

	function goTo(target: unknown, fallback = '/pesan'): void {
		goto(str(target) || fallback);
	}
</script>

<svelte:head>
	<meta name="theme-color" content="#f7f8fc" />
</svelte:head>

{#if !d}
	<div class="landing loading wrap"><p class="muted">Menyiapkan halaman…</p></div>
{:else}
	<div class="landing">
		<section class="hero">
			<div class="wrap hero-grid">
				<div class="hero-copy">
					<h1>{str(hero['heading']) || 'Website yang membuat bisnis Anda lebih mudah dipilih.'}</h1>
					<p class="hero-description">{str(hero['subheading']) || 'Kami merancang dan membangun website yang cepat, meyakinkan, dan siap membantu bisnis Anda tumbuh.'}</p>
					<div class="hero-actions">
						{#if hero['cta_label']}
							<button class="button button-primary" onclick={() => goTo(hero['cta_target'])}>{str(hero['cta_label'])}</button>
						{:else}
							<button class="button button-primary" onclick={() => goto('/pesan')}>Mulai proyek Anda</button>
						{/if}
						<a class="text-link" href="/portofolio">Lihat karya kami</a>
					</div>
					{#if hero['trust_badge_text']}<p class="trust-note">{str(hero['trust_badge_text'])}</p>{/if}
					<div class="hero-proof"><p><strong>Partner digital untuk bisnis bertumbuh</strong><br /><span>Strategi, desain, dan teknologi dalam satu tim.</span></p></div>
				</div>
				<div class="hero-art" aria-label="Ilustrasi tampilan website di desktop dan ponsel">
					<div class="orbit orbit-one"></div><div class="orbit orbit-two"></div>
					<div class="browser-window">
						<div class="browser-bar"><div class="browser-dots"><i></i><i></i><i></i></div><span>studio.example</span><span class="browser-menu">•••</span></div>
						<div class="site-preview">
							<div class="preview-nav"><b><span class="preview-mark">n</span> northline</b><div><i></i><i></i><i></i></div><i class="preview-pill"></i></div>
							<div class="preview-content"><div class="preview-copy"><span class="preview-caption">BUILT FOR WHAT'S NEXT</span><span class="preview-title">Make room<br />for your next.</span><span class="preview-lines"><i></i><i></i></span><span class="preview-button"></span></div><div class="preview-photo"><div class="photo-sun"></div><div class="photo-building"></div><div class="photo-plant"></div></div></div>
							<div class="preview-footer"><span></span><span></span><span></span><span></span></div>
						</div>
					</div>
					<div class="phone-window"><div class="phone-speaker"></div><div class="phone-screen"><div class="phone-top"><b>n.</b></div><div class="phone-heading">Make room<br />for your next.</div><div class="phone-landscape"><span></span></div><i class="phone-cta"></i></div></div>
				</div>
			</div>
		</section>

		{#if d.clientLogos.length > 0}
			<section class="client-strip wrap"><span>Dipercaya oleh tim yang<br />ingin melangkah lebih jauh</span><div class="logos">{#each d.clientLogos as l (l.id)}<img loading="lazy" src={publicAsset(str(l['logo_url']))} alt={str(l['name'])} />{/each}</div></section>
		{/if}

		<div class="wrap content-wrap">
			<section class="section intro-section">
				<div class="section-heading"><div><h2>{str(headerOf(d, 'value_props')['heading']) || 'Website profesional bekerja untuk bisnis Anda.'}</h2></div>{#if headerOf(d, 'value_props')['intro_text']}<p class="section-intro">{str(headerOf(d, 'value_props')['intro_text'])}</p>{/if}</div>
				<div class="value-grid">{#each d.valueProps as v, i (v.id)}<article class="value-item"><h3>{str(v['title'])}</h3><p>{str(v['description'])}</p></article>{/each}</div>
			</section>

			<section class="section work-section">
				<div class="section-heading"><div><h2>{str(headerOf(d, 'portfolio')['heading']) || 'Karya yang bicara.'}</h2></div><a class="text-link" href="/portofolio">Lihat semua proyek</a></div>
				<div class="portfolio-grid">{#each d.featuredPortfolios as p, i (p.id)}<a class:portfolio-wide={i === 0} class="portfolio-card" href="/portofolio"><div class="portfolio-image">{#if p['image_url']}<img loading="lazy" src={publicAsset(str(p['image_url']))} alt={str(p['title'])} />{:else}<div class="portfolio-placeholder placeholder-{i % 3}"><span>{str(p['title'])}</span><i></i></div>{/if}</div><div class="portfolio-meta"><h3>{str(p['title'])}</h3>{#if p['category']}<span>{str((p['category'] as Record<string, unknown>)['name'])}</span>{/if}</div></a>{/each}</div>
			</section>

			{#if d.services.length > 0}
				<section class="section service-section"><div class="section-heading"><div><h2>{str(headerOf(d, 'services')['heading']) || 'Semua yang dibutuhkan untuk hadir dengan percaya diri.'}</h2></div></div><div class="service-list">{#each d.services as s, i (s.id)}<article class="service-row"><span class="service-index">{String(i + 1).padStart(2, '0')}</span><h3>{str(s['name'])}</h3><p>{str(s['description'])}</p></article>{/each}</div></section>
			{/if}

			{#if d.processSteps.length > 0}
				<section class="section process-section"><div class="section-heading"><div><h2>{str(headerOf(d, 'process')['heading']) || 'Jelas sejak langkah pertama.'}</h2></div></div><div class="process-grid">{#each d.processSteps as s (s.id)}<article class="process-card"><span class="process-number">{str(s['step_number'])}</span><h3>{str(s['title'])}</h3><p>{str(s['description'])}</p></article>{/each}</div></section>
			{/if}

			{#if d.aboutTimeline.length > 0}
				<section class="section about-section"><div class="section-heading"><div><h2>{str(headerOf(d, 'about')['heading']) || 'Tumbuh bersama setiap proyek.'}</h2></div></div><div class="about-timeline">{#each d.aboutTimeline as t (t.id)}<article><span>{str(t['period'])}</span><div><h3>{str(t['title'])}</h3><p>{str(t['description'])}</p></div></article>{/each}</div></section>
			{/if}

			{#if d.teamMembers.length > 0}
				<section class="section team-section"><div class="section-heading"><div><h2>{str(headerOf(d, 'team')['heading']) || 'Tim kecil, perhatian penuh.'}</h2></div></div><div class="team-grid">{#each d.teamMembers as m (m.id)}<article>{#if m['photo_url']}<img loading="lazy" src={publicAsset(str(m['photo_url']))} alt={str(m['name'])} />{:else}<div class="team-placeholder">{str(m['name']).slice(0, 1)}</div>{/if}<h3>{str(m['name'])}</h3><p>{str(m['role'])}</p></article>{/each}</div></section>
			{/if}

			{#if d.pricePackages.length > 0}
				<section class="section pricing-section"><div class="section-heading"><div><h2>{str(headerOf(d, 'pricing')['heading']) || 'Pilih titik awal Anda.'}</h2></div></div><div class="pricing-grid">{#each d.pricePackages as p (p.id)}<article class:recommended={p['is_recommended']} class="price-card">{#if p['is_recommended']}<span class="recommend-label">Pilihan populer</span>{/if}<h3>{str(p['name'])}</h3>{#if p['tagline']}<p class="price-tagline">{str(p['tagline'])}</p>{/if}{#if p['show_price'] && p['price']}<p class="price-value"><span>Rp</span> {Number(p['price']).toLocaleString('id-ID')}</p>{/if}{#if Array.isArray(p['features'])}<ul>{#each p['features'] as f}<li>{String(f)}</li>{/each}</ul>{/if}<button class="button price-button" onclick={() => goTo(p['cta_action'] === 'contact' ? '/konsultasi' : `/pesan?package=${p['id']}`)}>{str(p['cta_label']) || 'Pilih paket'}</button></article>{/each}</div></section>
			{/if}

			{#if d.objectionQuestions.length > 0}
				<section class="section concerns-section"><div class="section-heading"><div><h2>{str(headerOf(d, 'objection')['heading']) || 'Pertanyaan yang sering muncul.'}</h2></div></div><div class="concerns-grid">{#each d.objectionQuestions as q (q.id)}<article><h3>{str(q['question'])}</h3><p>{str(q['answer'])}</p></article>{/each}</div>{#if d.referencePriceCards.length > 0}<div class="reference-prices">{#each d.referencePriceCards as c (c.id)}<div><span>{str(c['label'])}</span><strong>{str(c['price_value'])}</strong><small>{str(c['price_note'])}</small></div>{/each}</div>{/if}{#if headerOf(d, 'objection')['note_text']}<p class="pricing-note">{str(headerOf(d, 'objection')['note_text'])}</p>{/if}</section>
			{/if}

			{#if d.mockupOffer}
				<section class="mockup-banner"><div><h2>{str(d.mockupOffer['heading'])}</h2><p>{str(d.mockupOffer['description'])}</p>{#if Array.isArray(d.mockupOffer['feature_bullets'])}<ul>{#each d.mockupOffer['feature_bullets'] as b}<li>{String(b)}</li>{/each}</ul>{/if}</div><div class="mockup-offer-action"><strong>Rp {Number(d.mockupOffer['price'] ?? 0).toLocaleString('id-ID')}</strong><button class="button button-light" onclick={() => goto('/mockup')}>{str(d.mockupOffer['cta_label']) || 'Minta mockup'}</button></div></section>
			{/if}

			{#if d.faqs.length > 0}
				<section class="section faq-section"><div class="section-heading"><div><h2>{str(headerOf(d, 'faq')['heading']) || 'Ada yang ingin ditanyakan?'}</h2></div><a class="text-link" href="/konsultasi">Bicara dengan tim kami</a></div><div class="faq-list">{#each d.faqs as f (f.id)}<details><summary>{str(f['question'])}</summary><p>{str(f['answer'])}</p></details>{/each}</div></section>
			{/if}
		</div>
		<section class="final-cta"><div class="wrap final-cta-inner"><h2>Mari wujudkan sesuatu<br />yang membuat Anda bangga.</h2><p>Ceritakan ide Anda. Kami bantu menemukan langkah terbaik.</p><button class="button button-light" onclick={() => goto('/konsultasi')}>Jadwalkan konsultasi</button></div></section>
	</div>
{/if}
