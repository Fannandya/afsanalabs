<script lang="ts">
	import { goto } from '$app/navigation';
	import { home, headerOf, str } from '$lib/home';
	import { publicAsset } from '$lib/config';

	const d = $derived($home);
	const hero = $derived((d?.hero ?? {}) as Record<string, unknown>);
</script>

{#if !d}
	<div class="wrap section"><p class="muted">Memuat…</p></div>
{:else}
	<section class="hero">
		<div class="wrap">
			<span class="eyebrow">{str(hero['eyebrow'])}</span>
			<h1>{str(hero['heading'])}</h1>
			<p class="sub">{str(hero['subheading'])}</p>
			{#if hero['trust_badge_text']}<p><span class="badge">{str(hero['trust_badge_text'])}</span></p>{/if}
			{#if hero['cta_label']}
				<button class="btn ghost" onclick={() => goto(str(hero['cta_target']) || '/pesan')}>{str(hero['cta_label'])}</button>
			{/if}
			{#if hero['image_url']}
				<p><img class="cover" style="max-width:520px;height:auto" src={publicAsset(str(hero['image_url']))} alt="hero" /></p>
			{/if}
		</div>
	</section>

	<div class="wrap">
		<section class="section">
			<span class="eyebrow">{str(headerOf(d, 'value_props')['eyebrow_text'])}</span>
			<h2>{str(headerOf(d, 'value_props')['heading']) || 'Apa yang Kami Maksud dengan Website Profesional'}</h2>
			{#if headerOf(d, 'value_props')['intro_text']}<p class="sub">{str(headerOf(d, 'value_props')['intro_text'])}</p>{/if}
			<div class="grid c4">
				{#each d.valueProps as v (v.id)}
					<div class="card"><h3>{str(v['title'])}</h3><p class="muted small">{str(v['description'])}</p></div>
				{/each}
			</div>
		</section>

		<section class="section">
			<span class="eyebrow">{str(headerOf(d, 'process')['eyebrow_text'])}</span>
			<h2>{str(headerOf(d, 'process')['heading']) || 'Proses Kerja'}</h2>
			<div class="grid c4">
				{#each d.processSteps as s (s.id)}
					<div class="card"><span class="badge">{String(s['step_number'] ?? '')}</span><h3>{str(s['title'])}</h3><p class="muted small">{str(s['description'])}</p></div>
				{/each}
			</div>
		</section>

		<section class="section">
			<span class="eyebrow">{str(headerOf(d, 'objection')['eyebrow_text'])}</span>
			<h2>{str(headerOf(d, 'objection')['heading']) || 'Pra-Keputusan'}</h2>
			<div class="grid c3">
				{#each d.objectionQuestions as q (q.id)}
					<div class="card"><h3>{str(q['question'])}</h3><p class="muted small">{str(q['answer'])}</p></div>
				{/each}
			</div>
			<div class="grid c3" style="margin-top:12px">
				{#each d.referencePriceCards as c (c.id)}
					<div class="card"><h3>{str(c['label'])}</h3><p><strong>{str(c['price_value'])}</strong> <span class="muted small">{str(c['price_note'])}</span></p></div>
				{/each}
			</div>
			{#if headerOf(d, 'objection')['note_text']}<p class="small muted">{str(headerOf(d, 'objection')['note_text'])}</p>{/if}
		</section>

		<section class="section">
			<span class="eyebrow">{str(headerOf(d, 'services')['eyebrow_text'])}</span>
			<h2>{str(headerOf(d, 'services')['heading']) || 'Layanan Kami'}</h2>
			<div class="grid c4">
				{#each d.services as s (s.id)}
					<div class="card"><h3>{str(s['name'])}</h3><p class="muted small">{str(s['description'])}</p></div>
				{/each}
			</div>
		</section>

		<section class="section">
			<span class="eyebrow">{str(headerOf(d, 'portfolio')['eyebrow_text'])}</span>
			<h2>{str(headerOf(d, 'portfolio')['heading']) || 'Portofolio'}</h2>
			<div class="grid c3">
				{#each d.featuredPortfolios as p (p.id)}
					<div class="card">
						{#if p['image_url']}<img class="cover" src={publicAsset(str(p['image_url']))} alt={str(p['title'])} />{/if}
						<h3>{str(p['title'])}</h3>
					</div>
				{/each}
			</div>
			<p><button class="btn" onclick={() => goto('/portofolio')}>Lihat Lebih Banyak</button></p>
		</section>

		{#if d.aboutTimeline.length > 0}
			<section class="section">
				<h2>{str(headerOf(d, 'about')['heading']) || 'Tentang Kami'}</h2>
				<div class="timeline">
					{#each d.aboutTimeline as t (t.id)}
						<div><span class="badge">{str(t['period'])}</span><h3>{str(t['title'])}</h3><p class="muted small">{str(t['description'])}</p></div>
					{/each}
				</div>
			</section>
		{/if}

		{#if d.teamMembers.length > 0}
			<section class="section">
				<h2>{str(headerOf(d, 'team')['heading']) || 'Tim Kami'}</h2>
				<div class="grid c4">
					{#each d.teamMembers as m (m.id)}
						<div class="card">
							{#if m['photo_url']}<img class="cover" src={publicAsset(str(m['photo_url']))} alt={str(m['name'])} />{/if}
							<h3>{str(m['name'])}</h3><p class="muted small">{str(m['role'])}</p>
						</div>
					{/each}
				</div>
			</section>
		{/if}

		{#if d.clientLogos.length > 0}
			<section class="section">
				<div class="logos">
					{#each d.clientLogos as l (l.id)}
						<img src={publicAsset(str(l['logo_url']))} alt={str(l['name'])} />
					{/each}
				</div>
			</section>
		{/if}

		<section class="section">
			<span class="eyebrow">{str(headerOf(d, 'pricing')['eyebrow_text'])}</span>
			<h2>{str(headerOf(d, 'pricing')['heading']) || 'Paket & Harga'}</h2>
			<div class="grid c3">
				{#each d.pricePackages as p (p.id)}
					<div class="card">
						{#if p['is_recommended']}<span class="badge green">Rekomendasi</span>{/if}
						<h3>{str(p['name'])}</h3>
						{#if p['tagline']}<p class="muted small">{str(p['tagline'])}</p>{/if}
						{#if p['show_price'] && p['price']}<p><strong>Rp {Number(p['price']).toLocaleString('id-ID')}</strong></p>{/if}
						{#if Array.isArray(p['features'])}
							<ul class="small muted">{#each p['features'] as f}<li>{String(f)}</li>{/each}</ul>
						{/if}
						<button class="btn small" onclick={() => goto(p['cta_action'] === 'contact' ? '/konsultasi' : `/pesan?package=${p['id']}`)}>
							{str(p['cta_label']) || 'Pilih'}
						</button>
					</div>
				{/each}
			</div>
		</section>

		{#if d.mockupOffer}
			<section class="section">
				<div class="card">
					<span class="eyebrow">{str(d.mockupOffer['eyebrow'])}</span>
					<h2>{str(d.mockupOffer['heading'])}</h2>
					<p class="muted">{str(d.mockupOffer['description'])}</p>
					{#if Array.isArray(d.mockupOffer['feature_bullets'])}
						<ul class="small muted">{#each d.mockupOffer['feature_bullets'] as b}<li>{String(b)}</li>{/each}</ul>
					{/if}
					<p><strong>Rp {Number(d.mockupOffer['price'] ?? 0).toLocaleString('id-ID')}</strong></p>
					<button class="btn" onclick={() => goto('/mockup')}>{str(d.mockupOffer['cta_label']) || 'Minta Mockup'}</button>
				</div>
			</section>
		{/if}

		<section class="section faq">
			<span class="eyebrow">{str(headerOf(d, 'faq')['eyebrow_text'])}</span>
			<h2>{str(headerOf(d, 'faq')['heading']) || 'FAQ'}</h2>
			{#each d.faqs as f (f.id)}
				<details><summary>{str(f['question'])}</summary><p class="muted small">{str(f['answer'])}</p></details>
			{/each}
		</section>
	</div>
{/if}
