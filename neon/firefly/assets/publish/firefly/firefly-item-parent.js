/**
 * Created by newicon on 09/05/2017.
 */
/**
 * ------------------------
 * <firefly-item>
 * ------------------------
 *
 * Firefly item represents an individual item stored - this could be either a directory or a file
 */
Vue.component('firefly-item-parent', {
	props: {
		/**
		 * The picker name
		 */
		name: {type: String},
		selected: { type: Boolean }
	},
	template: `
		<div v-show="path != '/'" class="fireflyItem" :class="{'is-selected':selected, dragOver:dragOver}"  @dblclick="doubleClick">
			<div class="fireflyThumb fireflyThumbDir">
	
				<!-- Directory -->
				<div>
					<div class="fireflyThumb_folder fireflyThumb_preview"></div>
					<h5 class="fireflyThumb_caption" >..</h5>
				</div>
				
			</div>
		</div>
	`,
	computed: {
		dragOver:      function() { return this.$store.getters['Firefly/getIsItemBeingDroppedOver'](this.name, this.parentPath); },
		path:          function() { return this.$store.getters['Firefly/getPath'](this.name); },
		parent:        function() { return this.$store.getters['Firefly/getParent'](this.name); },
		selectedItems: function() { return this.$store.getters['Firefly/getSelectedItems'](this.name); },
		parentPath:    function() { return this.parent ? this.parent.path : '/'; }
	},
	methods:{
		doubleClick: function() { this.$store.dispatch('Firefly/LOAD_ITEMS', {path: this.parentPath, name: this.name}); },
	},
	mounted: function() {
		var vm = this;
		$(vm.$el).droppable({
			accept: ".fireflyItem",
			tolerance: "pointer",
			over: function(event, ui) { vm.$store.commit('Firefly/DROP_OVER', {id: vm.parentPath, name: vm.name});},
			out: function(event, ui) {vm.$store.commit('Firefly/DROP_OUT', {name: vm.name});},
			drop: function(event, ui) {
				vm.$store.dispatch('Firefly/ITEMS_DROPPED', {into: vm.parentPath, items: vm.selectedItems, name: vm.name});
				vm.$store.commit('Firefly/DROP_OUT', {name: vm.name});
			}
		})
	}
});