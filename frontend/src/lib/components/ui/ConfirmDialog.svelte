<script lang="ts">
	let { title = 'Hapus data ini?', confirmLabel = 'Hapus', onConfirm }: {
		title?: string;
		confirmLabel?: string;
		onConfirm: () => void | Promise<void>;
	} = $props();

	let dlg: HTMLDialogElement | undefined = $state();
	let busy = $state(false);

	export function open(): void {
		dlg?.showModal();
	}

	async function confirm(): Promise<void> {
		busy = true;
		try {
			await onConfirm();
			dlg?.close();
		} finally {
			busy = false;
		}
	}
</script>

<dialog class="modal" bind:this={dlg}>
	<h3 style="margin-top:0">{title}</h3>
	<div style="display:flex;gap:10px;justify-content:flex-end">
		<button class="btn ghost small" onclick={() => dlg?.close()}>Batal</button>
		<button class="btn danger small" disabled={busy} onclick={confirm}>{confirmLabel}</button>
	</div>
</dialog>
