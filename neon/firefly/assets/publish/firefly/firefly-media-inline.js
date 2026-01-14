/**
 * ------------------------
 * <firefly-media-inline>
 * ------------------------
 *
 */
Vue.component('firefly-media-inline', {
	props: {
		name: {type: String, required:true},
		startPath: { type: String, default: '/' }
	},
	template: `
		<div class="fireflyFinder">
			<template v-if="pickerExists">
				<!-- toolbar -->
				<firefly-media-browser-toolbar :name="name"></firefly-media-browser-toolbar>
				<firefly-media-browser-breadcrumb :name="name"></firefly-media-browser-breadcrumb>
				<firefly-media-browser :name="name"></firefly-media-browser>
			</template>
		</div>
	`,
	mounted: function() {
		this.$store.commit('Firefly/'+'CREATE_PICKER', {name: this.name, startPath: this.startPath, browserHistory:true})
	},
	computed: {
		pickerExists() { return this.$store.getters['Firefly/getPickerExists'](this.name) },
	}
});
