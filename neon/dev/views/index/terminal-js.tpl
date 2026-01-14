<h4>JS Fiddle Terminal</h4>
{use class='neon\dev\widgets\NeonJsFiddle' type='block'}
{NeonJsFiddle bundles='\neon\core\form\assets\FormAsset,\neon\core\themes\neon\Assets'}
	{literal}
<style>
.neonField {
	margin:0;
	padding:0;
	--pad: 10px;
	--border: 1px solid #dcdfe6;
}
.neonField_content{
	display:grid;
	grid-template-areas: "one two three";
	grid-template-columns: 120px 200px 1fr;
	border-bottom:var(--border);
}
.neonField_label {grid-area: one; padding:var(--pad);}
.neonInput {grid-area: two; padding:var(--pad); border-left: var(--border);}
.neonField_hint {grid-area: three; padding:var(--pad); border-left: var(--border);}
.neonField_hint p {margin:0;}
.neonField_errors {height:0;}
</style>
<script>
new Vue({
	el: '#app',
	data: function() {return {dynProps:{} }},
	computed: {
		properties: function() {
			return Vue.component('neon-modal').options.props;
		}
	},
	methods: {
		onClick: function() {
			neon.modal.show('mymodal', {}, this.dynProps);
		},
		getDefaultValue: function(propData) {
			return _.result(propData,'default')
		},
		getFieldProps: function(propData) {
			return _.get(propData, 'editor', {});
		},
		getField: function(propData) {
			let component = _.get(propData, 'editor.component')
			if (component)
				return component;
			switch (propData.type) {
				case Boolean: return 'neon-core-form-fields-switchbutton';
				case String:  return 'neon-core-form-fields-text';
				case Number:  return 'neon-core-form-fields-el-number';
				case Object:  return 'neon-core-form-fields-text';
				default:      return 'neon-core-form-fields-text';
			}
		}
	}
});
</script>
<html>
<div id=app>
	<button @click=onClick>Launch modal</button>
	<h2>Modal Properties</h2>
	<p>These are the props you can pass to the modal via the third argument on "neon.modal.show()" </p>
	<neon-core-form-form v-model="dynProps" name="form" id="form">
		<component v-for="(data, name) in properties" :is="getField(data)" :value="getDefaultValue(data)" v-bind="getFieldProps(data)" :name="name" :label="name"></component>
	</neon-core-form-form>

	<!-- Note it is better to create modal dynamically -->
	<neon-modal name="mymodal" v-bind="dynProps">
		<div style="padding:10px;">
			<h1>Hey I'm A Modal</h1>
			I have been created with the following props:
			<pre>{{dynProps}}</pre>
		</div>
	</neon-modal>
</div>
</html>
	{/literal}
{/NeonJsFiddle}
