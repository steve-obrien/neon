Vue.component('form-builder-edit-property', Vue.extend({
	props: {
		// the field object we want to modify
		field: Object,
		// the name of the property to edit
		property: String,
		// form field to edit the property
		editor: String,
		// properties of the the fieldComponent
		editorProps: Object,
	},
	template: `
		<div>
			<component :is="editor" :field="field" v-bind="editorProps" :value="editorProps.value" v-model="dynamic" :name="property"></component>
		</div>
		`,
	computed: {
		dynamic: {
			cache: false,
			get () {
				// check if the field (the actual fields definition that will be saved) has the property defined
				// we only invoke this condition if php is not aware of the property.
				// which kind of makes the statement invalid anyway
				// however if for some reason this property editor component is not tied to a php component class
				// then you would expect default values defined for props in vue components to filter through to
				// the editor - therefore this code exists - as we do not necessarily have to be operating in the
				// context of saving to a php component and serializing as per standard forms.

				if (!_.has(this.field, this.property)) {
					// if it is not defined then use the default (if available defined by the editor)
					return this.editorProps.default;
				}

				return this.field[this.property];
			},
			set (value) {
				let update = {}; update[this.property] = value;
				this.$store.commit('phoebe/updateFieldProps', {field: this.field, update: update});
				this.$store.commit('FORMS/UPDATE_FIELD', {path: 'builder.'+this.field.name, updates: update});
			}
		},
	}
}));
