Vue.component('firefly-item-name', {
	props: {
		selected: {type: Boolean, default: false},
		item: {type: Object},
		canEdit: {type: Boolean, default:true},
	},
	template: `
		<div>
			<h5 v-if="!editing" :title="itemName" class="fireflyThumb_caption" @click="editName">{{itemName}}</h5>
			<template v-else>
				<input style="font-weight:400;font-size:14px;text-align:center;width:100%;margin:5px 0 0 0;padding:0;padding:0px;" @keyup="inputKeyup" @blur="updateName($event.target.value)" type="text" :value="itemName" />
			</template>
		</div>`,
	computed: {
		itemName () { return this.$store.getters['Firefly/getItemName'](this.item); },
		editing () { return this.$store.getters['Firefly/getIsEditingItemName'](this.item); }
	},
	methods:{
		editName: function(){
			if (!this.selected) return;
			if (!this.canEdit) return;
			this.$store.dispatch('Firefly/ITEM_NAME_EDIT_START', {id: this.item.id});
			// focus the field
			var vm = this;
			Vue.nextTick(function () {
				$('input', vm.$el).focus().select();
			})
		},
		updateName: function(value){
			if (this.editing) {
				this.$store.dispatch('Firefly/ITEM_NAME_EDIT', {item: this.item, name: value});
			}
		},
		inputKeyup: function(e) {
			if (e.which == 13) {
				this.$store.dispatch('Firefly/ITEM_NAME_EDIT', {item: this.item, name: e.target.value});
			}
			// escape key
			if (e.which == 27) {
				// revert back to original name
				this.$store.commit('Firefly/ITEM_NAME_EDIT_STOP');
			}
		}
	}
});
