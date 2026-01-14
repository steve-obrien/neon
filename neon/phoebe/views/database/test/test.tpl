{*
 @link http://www.newicon.net/neon
 @copyright Copyright (c) 2016 Newicon Ltd
 @license http://www.newicon.net/neon/license/
 @author Steve O'Brien <steve.obrien@newicon.net> 06/11/2016 23:38
 @package neon
*}
<hr/>
<pre>
// The form data given by <code>$form->getData()</code>
{$values}
</pre>

<div>
	<div class="row">
		<div class="col-md-6">

			{$form->run() nofilter}
			<hr/>
			ANOTHER FORM
			{*{$form->setId('newid')|void}*}
			{*{$form->run() nofilter}*}
			{*{$form2->run() nofilter}*}
			{*<div>*}
			{*{$form->setId('other')|void}*}
			{*{$form->run() nofilter}*}
			{*</div>*}
		</div>
		<div class="col-md-6">

				<div id="test">
					{literal}
						<div class="alert alert-warning">
							<a class="btn btn-default" @click="toggleReadOnly()" >ReadOnly: {{formProps.readOnly}}</a>
							<a class="btn btn-default" @click="toggleIsPrint();" >PrintMode: {{formProps.printOnly}}</a>
							<a class="btn btn-default" @click="form.validate(!formProps.readOnly)" >Validate</a>
							<a class="btn btn-default" @click="form.reset()" >Reset</a>
						</div>
						Form Data
						<pre>{{formData}}</pre>
						Form Data 2
						<pre>formData2</pre>
						<pre>modelData</pre>
						<pre>$store.state.FORMS</pre>
					{/literal}
				</div>

		</div>
	</div>
</div>

{js}{literal}
<script>
window.testFormControl = (new Vue({
	store: neon.Store,
	data:{
		idetest: 'qwefqwef',
		modelData: {},
	},
	methods: {
		formDataMethod() {
			return neon.form.forms['testy'].getData()
		},
		validate () {
			this.form.validate();
		},
		toggleReadOnly () {
			this.form.setReadOnly(!this.formProps.readOnly);
		},
		toggleIsPrint() {
			this.formProps.printOnly = !this.formProps.printOnly;
			form.reset();
		}
	},
	computed: {
		form: function () {
			return neon.form.forms.testy;
		},
		formProps: function () {
			return this.$store.getters['FORMS/getField']('testy');
		},
		formData: function(){
			return this.$store.getters['FORMS/getData']('testy');
		},
		formData2: function(){
			return this.$store.getters['FORMS/DATA']('newid');
		},
	}
})).$mount('#test');

</script>
{/literal}{/js}