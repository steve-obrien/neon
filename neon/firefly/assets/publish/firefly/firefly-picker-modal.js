/**
 * ------------------------
 * <firefly-browser-picker>
 * ------------------------
 *
 */
Vue.component('firefly-picker-modal', {
	props: {
		name: {type: String, required:true},
	},
	template: `
		<div class="fireflyPicker">
			<firefly-media-browser-toolbar slot="header" :name="name" class="workbenchHeader-modal"></firefly-media-browser-toolbar>
			<firefly-media-browser-breadcrumb :name="name"></firefly-media-browser-breadcrumb>
			<firefly-media-browser :name="name" @onDoubleClickItem="selected"></firefly-media-browser>
		</div>
	`,
	computed: {
		selectedItem  () { return this.$store.getters['Firefly/getSelectedItem'](this.name); },
		isSelectedItemImage () { return this.selectedItem; } // return this.$store.getters['Firefly/getIsItemImage'](this.selectedItem); }
	},
	methods: {
		selected: function() {
			// Launch a gloabl event
			FIREFLY.events.$emit('selected', this.selectedItem);
		}
	}
});