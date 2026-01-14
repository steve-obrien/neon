Vue.component('form-builder-field-form-element-decimal', Vue.extend({
	props: {
		field: Object
	},
	template: `
			<div class="form-group">
				<label class="control-label" for="data-name">Decimal Places</label>
				<div class="hint-block hint-top">Set the max number of decimal places</div>
				<input class="form-control" :value="field.decimal" @change="update($event.target.value)" type="number" min=0 max=10 id="data-decimal" name="decimal"  />
			</div>
		`,
	methods: {
		update: function (value) {
			this.field.decimal = value;
			// override the default implementation and toggle the value
			this.$store.commit('phoebe/updateField', {field: this.field});
		}
	}
}));

