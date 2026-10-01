<script lang="ts">
	import CrudPage from '$lib/components/admin/CrudPage.svelte';
</script>

<CrudPage
	title="Testimoni"
	endpoint="/admin/testimonials"
	fields={[
		{ key: 'client_name', label: 'Nama klien', type: 'text', required: true },
		{ key: 'content', label: 'Isi', type: 'textarea', required: true },
		{ key: 'rating', label: 'Rating 1–5', type: 'number', required: true },
		{ key: 'sort_order', label: 'Urutan', type: 'number' },
		{ key: 'is_active', label: 'Aktif', type: 'checkbox' }
	]}
	columns={['client_name', 'rating', 'sort_order', 'is_active']}
/>
