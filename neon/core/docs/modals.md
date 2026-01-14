#Javascript Modals

How to use modals in neon

<! neon\dev\widgets\NeonJsFiddle bundles='\neon\core\assets\CoreAsset,\neon\core\themes\neon\Assets' >
<script>
window.showMyModal = function(){
	neon.modal.show(
        // A Vue component definition
		{
			template:`<div> <h1>Hello!</h1></div>`,
		},
        // Data to pass as props to the component defined above
		{},
        // Properties for the modal
		{
			// can be a string like '100vw'
			// a numerical value will default to pixels
			width: 500,
			// modal buttons
			buttons: [
				{title: 'Close'},
				{
					loading: false,
					loadingText: 'loading...',
					title:'Hello',
					/**
					 * @param {Object} event - an event object
					 * @param {Object} event.sender - the modal component that raised the click event
					 * @param {Function} event.close() - a function on the event when called will close the modal
					 * @param {int} i - the button position inside the sender (modals) buttons array
					 */
					handler: function(event, i) {
						var thisButton = event.sender.buttons[i];
						thisButton.loading = true;
						var timer = 5;
						var interval = setInterval(() => {
							if (timer == 0) {
								clearInterval(interval);
								event.close();
							}
							thisButton.loadingText = 'I will close in ' + timer + '...';
							timer--;
						}, 1000);
					}
				}
			]
		}
	)
};
</script>
<html>
	<div id=app>
		<button onclick=showMyModal()>
			Launch modal
		</button>
	</div>
</html>
</!>

Passing data to the modal:


<! neon\dev\widgets\NeonJsFiddle bundles='\neon\core\assets\CoreAsset,\neon\core\themes\neon\Assets' >
<script>
window.showMyModal = function(){
	neon.modal.show(
		{
			props: {title:'', message:''},
			template:`<div> <h1>{{title}}</h1> <div v-html="message"></div> </div>`,
		},
		{title: 'Hello', message: '<h2>Potentially Dangerous HTML!🔥 Make sure you trust it! 😀</h2>'},
		{
			buttons: [
				{title: 'Close'},
				{
					title:'Hello',
					handler: function() { alert('hello') }
				}
			]
		}
	)
};
</script>
<html>
	<div id=app>
		<button onclick=showMyModal()>
			Launch modal
		</button>
	</div>
</html>
</!>

Example with events:

<! neon\dev\widgets\NeonJsFiddle bundles='\neon\core\assets\CoreAsset,\neon\core\themes\neon\Assets' >
<script>
window.showMyModal = function(){
	neon.modal.show(
		/**
		 * A Vue component definition
		 */
		{
			template:'<div>Hello</div>'
		},
		/**
         * Data to pass as props to the component defined above
         */
		{},
		/**
		 * Properties for the modal
		 */
		{
			draggable: true,
			width:'300px',
			height:'100px',
			buttons: [
				{title: 'Close'},
				{
					title: 'Close Me?',
					handler: function(e) {
						return confirm('Are you sure you want to close me?')
					}
				}
			]
		},
		{'before-open': function(event) {alert('before event - you can use event.stop() to prevent the modal showing');}}
	)
};
</script>
<html>
	<div id=app>
		<button onclick=showMyModal()>
			Launch modal
		</button>
	</div>
</html>
</!>

Modals are global things dynamically inserted into the page, and often you need to control them from external code.
Therefore it is usually useful to give the modal a name:

<! neon\dev\widgets\NeonJsFiddle bundles='\neon\core\assets\CoreAsset,\neon\core\themes\neon\Assets' >
<script>

window.clickMe = function() {
	neon.modal.hide('mymodal');
	setTimeout(function() {alert('show');neon.modal.show('mymodal')}, 1000)
}
window.showMyModal = function(){
	
	neon.modal.show(
		/**
		 * A Vue component definition
		 */
		{
			props: {clickMe: {type: Function}},
			template:'<div>Hello <button @click="clickMe()">Click me</button></div>'
		},
		/**
         * Data to pass as props to the component defined above
         */
		{clickMe: window.clickMe},
		/**
		 * Properties for the modal
		 */
		{
			name: 'mymodal',
			width:'300px',
		}
	)
};
</script>
<html>
	<div id=app>
		<button onclick=showMyModal()>
			Launch modal
		</button>
	</div>
</html>
</!>



## Play area - play with the modal options dynamically

<! neon\dev\widgets\NeonJsFiddle 
	activeTab="result" 
	bundles='\neon\core\form\assets\FormAsset,\neon\core\themes\neon\Assets' >
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
	<p>These are the props you can pass to the modal via the third argument on "neon.modal.show()" or by passing them into the neon-modal component via a template.
		Have a play with the options.  Then click on the Luanch modal to see the results.</p>
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
</!>


### Events

| Name         | Description |
| ---          | --- |
| before-open  | Emits while modal is still invisible, but was added to the DOM |
| opened       | Emits after modal became visible or started transition |
| before-close | Emits before modal is going to be closed. Can be stopped from the event listener calling `event.stop()` (example: you are creating a text editor, and want to stop closing and ask the user to correct mistakes if the text is not valid)
| closed       | Emits right before modal is destroyed |

