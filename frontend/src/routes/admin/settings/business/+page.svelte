<script lang="ts">
	import SingletonPage from '$lib/components/admin/SingletonPage.svelte';
	import CrudPage from '$lib/components/admin/CrudPage.svelte';
</script>

<h1>Info Bisnis, Navigasi & Legal</h1>
<SingletonPage
	title="Info Bisnis"
	endpoint="/admin/business-settings"
	fields={[
		{ key: 'business_name', label: 'Nama bisnis', type: 'text', required: true },
		{ key: 'footer_tagline', label: 'Tagline footer', type: 'text' },
		{ key: 'footer_copyright', label: 'Copyright', type: 'text' },
		{ key: 'contact_email', label: 'Email kontak', type: 'text', required: true },
		{ key: 'phone', label: 'Telepon', type: 'text' },
		{ key: 'whatsapp_number', label: 'Nomor WhatsApp', type: 'text' },
		{ key: 'whatsapp_message_template', label: 'Template WA ({{kode}} {{paket}} {{nama}})', type: 'textarea' },
		{ key: 'address', label: 'Alamat', type: 'textarea' },
		{ key: 'description', label: 'Deskripsi', type: 'textarea' },
		{ key: 'payment_instructions', label: 'Instruksi bayar (mockup)', type: 'textarea' },
		{ key: 'footer_map_url', label: 'Gambar latar footer', type: 'image', folder: 'business' }
	]}
/>
<h2 style="margin-top:28px">Navigasi</h2>
<CrudPage
	title="Nav Links"
	endpoint="/admin/nav-links"
	fields={[
		{ key: 'label', label: 'Label', type: 'text', required: true },
		{ key: 'target', label: 'Target (/xxx atau #xxx)', type: 'text', required: true },
		{
			key: 'placement',
			label: 'Penempatan',
			type: 'select',
			options: [
				{ value: 'topnav', label: 'topnav' },
				{ value: 'footer', label: 'footer' }
			]
		},
		{ key: 'sort_order', label: 'Urutan', type: 'number' },
		{ key: 'is_active', label: 'Aktif', type: 'checkbox' }
	]}
	columns={['label', 'target', 'placement', 'is_active']}
/>
<h2 style="margin-top:28px">Link legal footer</h2>
<CrudPage
	title="Footer Legal Links"
	endpoint="/admin/footer-legal-links"
	fields={[
		{ key: 'label', label: 'Label', type: 'text', required: true },
		{ key: 'url', label: 'URL', type: 'text', required: true },
		{ key: 'sort_order', label: 'Urutan', type: 'number' },
		{ key: 'is_active', label: 'Aktif', type: 'checkbox' }
	]}
	columns={['label', 'url', 'is_active']}
/>
