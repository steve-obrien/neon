Vue.component('firefly-item-breadcrumb', {
	props: {
		picker: {type: String, required:true},
		path: {type: String, required:true},
		name: {type: String, required:true},
	},
	template: `
		<div :class="{m_dragOver:dragOver}" class="fireflyBreadcrumb">
			<template v-if="currentPath != path">
				<a class="fireflyBreadcrumb_link" @click.prevent="loadPath(path)" :href="path">{{name}}</a>
				<svg width="16" height="16" viewBox="0 0 16 16"><title>arrow-right</title><path d="M10.414 7.05l4.95 4.95-4.95 4.95L9 15.534 12.536 12 9 8.464z" fill="#637282" fill-rule="evenodd"></path></svg>
			</template>
			<span v-if="currentPath == path">{{name}}</span>
		</div>
	`,
	mounted: function() {
		var vm = this;
		$(vm.$el).droppable({
			accept: ".fireflyItem",
			tolerance: "pointer",
			greedy: true,
			over: function(event, ui) { vm.$store.commit('Firefly/DROP_OVER', {id: vm.path, name: vm.picker}); },
			out: function(event, ui) { vm.$store.commit('Firefly/DROP_OUT', {name: vm.picker}); },
			drop: function(event, ui) {
				vm.$store.dispatch('Firefly/ITEMS_DROPPED', {into: vm.path, items: vm.selectedItems, name: vm.picker});
				vm.$store.commit('Firefly/DROP_OUT', {name: vm.picker});
			}
		})
	},
	methods: {
		loadPath:  function(path) { this.$store.dispatch('Firefly/LOAD_ITEMS', { path: path, name: this.picker }); },
	},
	computed: {
		dragOver:      function() { return this.$store.getters['Firefly/getIsItemBeingDroppedOver'](this.picker, this.path); },
		selectedItems: function() { return this.$store.getters['Firefly/getSelectedItems'](this.picker); },
		currentPath:   function() { return this.$store.getters['Firefly/getPath'](this.picker); },
		pathBits:      function() { return this.$store.getters['Firefly/getPathBits'](this.picker); },
	}
});
