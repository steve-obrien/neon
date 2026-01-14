/**
 * Auto loads .vue component files from the directories
 * Can import other dependenies here to be compiled by webpack.
 * Will auto register components found globally via Vue.component()
 * Using their name property defined on Vue component definition
 */
const fieldsContext = require.context('./form', true, /\.vue$/);

import FieldBase from './form/fields/FieldBase.vue';
Vue.component(FieldBase.name, FieldBase);
// import Field from './form/fields/Field.vue';
// Vue.component(Field.name, Field);
// import Text from './form/fields/Text.vue';
// Vue.component(Text.name, Text);
// import Textarea from './form/fields/Textarea.vue';
// Vue.component(Textarea.name, Textarea);



// /Vue.component('neon-core-form-form', Form);

fieldsContext.keys().forEach(fileName => {
	// Get the component config
	const componentConfig = fieldsContext(fileName);

	// Look for the component options on `.default`, which will exist if the component is
	// exported with `export default`, otherwise fall back to module's root.
	// Most components should use export default { /* config */ }
	const component = componentConfig.default || componentConfig

	// Get the snake-case version of the component name
	// Register the component globally
	// This is important for neon form components - to work on the builder.
	if (component.name) {
		Vue.component(component.name, componentConfig.default || componentConfig);
	}
});