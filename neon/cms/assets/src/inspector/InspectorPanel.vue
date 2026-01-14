<template>
	<div>
		<div class="inspector_heading" @click="open = !open">
			{{ title }}
		</div>
		<div v-show="open" class="inspector_panel">
			<slot />
		</div>
	</div>
</template>

<script>
export default {
	name: 'InspectorPanel',
	props: {
		startOpen: { type: Boolean, default: true },
		title: { type: String, default: '' },
	},
	data() {
		return { open: this.startOpen };
	},
};
</script>

<style scoped>
	.inspector_heading { border-bottom: 1px solid #ccc; padding: 5px 10px; }
	.inspector_panel { padding: 10px; }
</style>
