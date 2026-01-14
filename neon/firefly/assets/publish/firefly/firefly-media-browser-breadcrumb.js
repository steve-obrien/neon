/**
 * ------------------------
 * <firefly-media-browser>
 * ------------------------
 *
 */
Vue.component('firefly-media-browser-breadcrumb', {
	props: {
		name: {type: String, required:true}
	},
	template: `
		<nav class="breadcrumb">
			<firefly-item-breadcrumb :picker="name" name="Media" path="/"></firefly-item-breadcrumb>
			<firefly-item-breadcrumb v-for="part in pathBits" :picker="name" :name="part.name" :path="part.path" :key="part.path"></firefly-item-breadcrumb>
		</nav> 
	`,
	methods: {
		loadPath:  function(path) { this.$store.dispatch('Firefly/LOAD_ITEMS', { path: path, name: this.name }); },
	},
	computed: {
		dropText:       function() { return this.$store.getters['Firefly/getDropMoveText'](this.name);},
		path:           function() { return this.$store.getters['Firefly/getPath'](this.name); },
		pathBits:       function() { return this.$store.getters['Firefly/getPathBits'](this.name); },
	}
});
