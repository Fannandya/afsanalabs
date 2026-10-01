<script lang="ts">
	import SingletonPage from '$lib/components/admin/SingletonPage.svelte';
</script>

<SingletonPage
	title="SEO"
	endpoint="/admin/seo-settings"
	fields={[
		{ key: 'meta_title', label: 'Meta title', type: 'text' },
		{ key: 'meta_description', label: 'Meta description', type: 'textarea' },
		{ key: 'meta_keywords', label: 'Meta keywords', type: 'text' },
		{ key: 'og_image_url', label: 'OG image', type: 'image', folder: 'seo' }
	]}
/>
