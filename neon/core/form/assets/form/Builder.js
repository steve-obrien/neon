
Vue.component('neon-core-form-fields-builder', {
	extends: Vue.component('neon-core-form-fields-base'),
	template:`<neon-core-form-field v-bind="fieldProps">
		<div>
			<neon-core-form-fields-json name="propsForm" v-model="modelValue"></neon-core-form-fields-json>
		</div>
		<div>
			<neon-core-form-form v-if="form.name" :name="form.name" :id="form.id" :fields="form.fields" v-bind="form"></neon-core-form-form>
		</div>
	</neon-core-form-field>`,
	mounted() {
		// indent json on first load
		this.modelValue = JSON.stringify(this.form, null, 4);
	},
	computed: {
		form() {
			if (this.modelValue)
				try {
					return JSON.parse(this.modelValue);
				} catch(e) {
					return {};
				}
			return {};
		}
	},
});