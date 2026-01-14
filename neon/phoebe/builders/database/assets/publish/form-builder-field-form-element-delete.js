Vue.component('form-builder-field-form-element-delete',  Vue.extend({
	props: {
		field: Object,
		wrapperClass:String,
		btnClass:String,
	},
	data: function() {
		return {isDeleting: false}
	},
	template: `
		<button @click="click" class="btn btn-danger" :class="btnClass"><i class="fa fa-trash"></i><span v-if="isDeleting">Deleting...</span></button>
	`,
	methods: {
		click: function(e) {
			var that = this;
			that.isDeleting = true;
			this.$store.dispatch('phoebe/deleteField', {field: this.field})
				.then(
					function() {
						that.isDeleting = false;
					},
					function() {
						that.isDeleting = false;
					}
				);
			e.preventDefault();
			return false;
		}
	}
}));