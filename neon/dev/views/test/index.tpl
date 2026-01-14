{$form->run() nofilter}
{literal}
<div class="panel panel-default" style="width:50%;margin:auto;">
	<div class="panel-body">

		<div onclick="$('#textField').show().focus()" >Click this to focus</div>
		<input style="display:none;" id="textField" type="number" onblur="alert('oi')" value="2">



		<div id="test">
			<neon-focus-thing></neon-focus-thing>
			<neon-focus-thing></neon-focus-thing>
			<neon-focus-thing></neon-focus-thing>
			<neon-focus-thing></neon-focus-thing>

			<neon-core-form-form name="testy" id="testy" :fields="fields">
				<div slot="header">
					<legend><h1>Test</h1></legend>
				</div>
				<neon-core-form-fields-el-switch name="myswitch" label="switchy"></neon-core-form-fields-el-switch>
				<div class="row">
					<div class="col-sm-6">
						<neon-core-form-fields-text :validators=" { required: { 'class': 'required', message: 'required field dude' } } " name="first" label="First"></neon-core-form-fields-text>
					</div>
					<div class="col-sm-6">
						<neon-core-form-fields-text :validators=" { required: { 'class': 'required', message: 'required field dude' } } " name="last" label="Last"></neon-core-form-fields-text>
					</div>
				</div>
				<neon-core-form-fields-submit name="submit" ><i class="fa fa-save"></i> Savey baby</neon-core-form-fields-submit>
			</neon-core-form-form>

			{{output}}

			<neon-core-form-form name="one" id="one">
				<neon-core-form-fields-text name="text_one"></neon-core-form-fields-text>
				<neon-core-form-form name="two">
					<neon-core-form-fields-text name="text_two"></neon-core-form-fields-text>
					<neon-core-form-form name="three">
						<neon-core-form-fields-text name="text_three"></neon-core-form-fields-text>
					</neon-core-form-form>
				</neon-core-form-form>
			</neon-core-form-form>


		</div>
	</div>
</div>
{/literal}


{registerAsset path="neon\\core\\form\\assets\\FormAsset"}
{js}<script>{literal}

	Vue.component('neon-unknown-component', {
		template:`<div class="alter alert-danger">Unknown component</div>`
	});

	Vue.mixin({
		_uuid: {type:String},
		mounted: function() {
			console.log(this.$key, 'key');
		},
		methods: {
			$resolveComponent: function (className) {
				if (_.isString(className))
					return neon.classToComponent(className);
				if (_.isObject(className)) {
					if (!_.isUndefined(className.component))
						return className.component;
					if (!_.isUndefined(className.class) && className.class.includes('\\'))
						return neon.classToComponent(className.class);
				}
				return 'neon-unknown-component';
			}
		}
	});

	Vue.component('neon-core-grid', {
		// extends: Vue.component('neon-core-form-form'),
		props: {
			id: '',
			fields: {type: Object},
			col1: {type: Array},
			col2: {type: Array}
		},
		template: `
			<div class="row">
				<div class="col-sm-6">
					<component v-for="key in col1" :is="$resolveComponent(fields[key])" v-bind="fields[key]" :key=key></component>
				</div>
				<div class="col-sm-6">
					<component v-for="key in col2" :is="$resolveComponent(fields[key])" v-bind="fields[key]" :key=key></component>
				</div>
			</div>
		`,
	});

	Vue.component('neon-focus-thing', {
		data: function() {
			return {editing: false, theValue: 1}
		},
		directives: {
			focus: {inserted: function (el) {el.focus()}}
		},
		template: `
			<div>
				<input v-if="!editing" readonly @click="onClick" value="click me"/>
				<input v-if="editing" type="number" v-model="theValue" v-focus @blur="editing = false" @keyup.escape="editing = false" @keyup.enter="editing = false"/>
			</div>
		`,
		methods: {
			onClick: function() {
				this.editing = true;
			}
		}
	});


	(new Vue({
		data: function () {
			return {
				output: {},
				fields: {
					thing:{ name: 'thing', class: 'neon\\core\\form\\fields\\switchbutton', label: 'thing' },
					grid: {
						id: 'grid',
						name: 'grid',
						class: 'neon-core-grid',
						fields: {
							dynFirst: {
								name: 'dynFirst',
								label: 'def first',
								class: 'neon\\core\\form\\fields\\text',
								validators: {required: {'class': 'required', message: 'required field dude'}}
							},
							dynLast: {
								name: 'dynLast',
								label: 'def last',
								class: 'neon\\core\\form\\fields\\text'
							}
						},
						col1: ['dynFirst'],
						col2: ['dynLast']
					}
				}
			};
		}
	})).$mount('#test');
{/literal}</script>{/js}