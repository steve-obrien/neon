Vue.component('form-builder-field-form-default', {
	props: {
		field: {
			type: Object,
			required: true,
			default: function () {
				return {
					classLabel: 'Unknown',
					label: 'Label',
					required: false,
					hint: 'A handy hint',
					placeholder: 'Placeholder text'
				}
			}
		}
	},
	template: `
		<div>
			<div class="hr-text">{{field.classLabel}}</div>
			<form-builder-field-form-element-name :field="field"></form-builder-field-form-element-name>
			<!--<form-builder-field-form-element-label :field="field"></form-builder-field-form-element-label>-->
			<form-builder-edit-property property="label" :field="field"
				editor="neon-core-form-fields-text"
				:editor-props="{label:'Label', placeholder: 'Set the label for the field'}"></form-builder-edit-property>
			<form-builder-edit-property property="hint" :field="field"
				editor="neon-core-form-fields-text"
				:editor-props="{label:'Guide Text', placeholder: 'Add additional guide text'}"></form-builder-edit-property>
			<form-builder-edit-property property="placeholder" :field="field"
				editor="neon-core-form-fields-text"
				:editor-props="{label:'Placeholder', placeholder: 'Add helper placeholder text'}"></form-builder-edit-property>
			<form-builder-field-form-element-required :field="field"></form-builder-field-form-element-required>
			<div class="form-group"><form-builder-field-form-element-delete :field="field"></form-builder-field-form-element-delete></div>
		</div>
	`
});