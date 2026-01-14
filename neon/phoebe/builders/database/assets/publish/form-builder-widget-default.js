Vue.component('form-builder-widget-default', {
	props: {
		form:{},
		field: {
			type: Object,
			required: true,
			default: function () {
				return {
					class: "\neon\core\form\fields\Text",
					classLabel: "Text",
					name: '',
					label: 'Label',
					required: false,
					hint: 'A handy hint',
					placeholder: 'Placeholder text',
					order: 0
				}
			}
		}
	}
});