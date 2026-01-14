neon.formProps = {
	config: {type: Object},
	id: {type: String, required: true, default: ''},
	name: {type: String, required: true},
	errors: {type:[Array, Object], default: () => []},
	csrfParam: {type: String, required: false},
	csrfToken: {type: String, required: false},
	label: {type: String, default: ''},
	hint: {type: String, default: ''},
	visible: {type: Boolean, default: true},
	inline: {type: Boolean, default: false},
	action: {type: String, default: ''},
	readOnly: {type: Boolean, default: false},
	fields: {type: [Object, Array], default: () => {return {}}},
	enableAjaxValidation: {type: [Boolean, String], default: false},
	enableAjaxSubmission: {type: [Boolean, String], default: false},
	validationUrl: {type: String, default: ''},
	attributes: {type: Object, default: function() {return {class: ''}}},
	printOnly: {type: Boolean, default: false},
	isSubmitted: {type: Boolean, default: true},
	validateOnBlur: {type: Boolean, default: true},
	enableClientValidation: {type: Boolean, default: true},
	/**
	 * A token hash referencing the serialised form object
	 */
	objectToken:{type:String},
};

Vue.component('neon-core-form-form', {
	store: neon.Store,
	props: neon.formProps,
	provide: function() {
		// as the parentForm property is injected into to forms by their parent forms (if they have them)
		// a root form will not have a parentForm
		if (this.parentForm === false) {
			// must be the root form
			return {
				rootForm: this,
				parentForm: this
			}
		}

		// not a root form so only need provide the immediate parent
		// Vue will inject the rootForm from any hierarchy
		return {
			parentForm: this
		}
	},
	inject: {
		// the root form will have no parent form so set the parentForm to false by default
		// therefore a root form will always have its parentForm property === false
		'rootForm': { default: false },
		'parentForm': { default: false }
	},
	created: function() {
		if (this.isRoot()) {
			this.$store.commit('FORMS/CREATE', this.$props);
			// store a reference to the form component
			// this is useful for direct access such as
			// neon.form.forms[myid].submit();
			// neon.form.forms[myid].validate();
			// etc etc
			window.neon.form.forms[this.id] = this;
		} else {
			this.$store.dispatch('FORMS/createFieldState', {path: this.getPath(), props: this.$props});
		}
	},
	mounted: function () {
		this.$emit('mounted', this);
		neon.app.$emit('form-'+this.id+'.mounted', this);
	},
	template: `
		<component @input="bubbleInput($event)" @change="bubbleInput($event)" @submit="handleSubmit" :is="isRoot() ? 'form' : ((isReadOnly || isPrintOnly) ? 'div' : 'fieldset')" method="post" enctype="multipart/form-data" :action="action" class="neonForm" :class="attributes.class" :name="name" :id="getId()" v-bind="attributes" >
			<slot name="header">
				<div v-if="label" class="form-group"><legend>{{label}}</legend></div>
			</slot>
			<slot name="formError">
			</slot>
			<div class="neonForm_errors" v-if="errors[0]">
				<div class="neonField_error">{{errors[0]}}</div>
			</div>
			<slot name="fields">
				<component v-for="(field, key) in form.fields" v-if="field.class" :is="component(field.class)" v-bind="field" id="" :class="''" :key="key" ></component>
			</slot>

			<!-- default slot html inside the form tags such as other fields will appear here -->
			<slot></slot>
			<input type="hidden" :name="form.csrfParam" :value="form.csrfToken" v-if="isRoot()" />
		</component>
	`,
	computed: {
		value: {
			// where the object property key is the name of the form field
			// and the value is the value for the form field.
			set: function(value) {
				var pathToValues = _.flattenObject(value);
				var form = this;
				_.forEach(pathToValues, function(value, key){
					neon.form.updateField(form.id + '.' + key, {value: value});
				});
			},
			get: function() {
				// get the form data
				return this.getData();
			}
		},
		formData() {
			return this.$store.getters['FORMS/getData'](this.path);
		},
		form() {
			if (this.isRoot())
				return this.$store.getters['FORMS/FORM'](this.id);
			return this;
		},
		// TODO tidy up duplicate in baseField and other places 10/04/2018
		isReadOnly() {
			var rootForm = this.getRootFormData();
			if (rootForm) {
				return this.readOnly || rootForm.readOnly;
			}
			return false;
		},
		isPrintOnly() {
			var rootForm = this.getRootFormData();
			if (rootForm) {
				return this.printOnly || rootForm.printOnly;
			}
			return false;
		},
		submitted: function () {
			return this.getRootFormData().submitted;
		},
		path: function() {
			return this.getPath();
		}
	},
	methods: {
		bubbleInput: function(e) {
			this.$emit('input', this.formData)
		},
		// region Events ===============================================================================================
		onChange: function(callback) {
			var vm = this;
			this.$on('change', function(e) { callback(vm.formData, e); });
		},
		onBeforeSubmit: function(callback) {
			this.$on('beforeSubmit', function(e) { callback(e); });
		},
		onAfterSubmit: function(callback) {
			this.$on('afterSubmit', function(e) { callback(e); });
		},
		// endregion ===================================================================================================
		getId() {
			// if (this.id)
			// 	return this.id;
			return this.path.replace(/\./g, '-');
		},
		getRootFormData() {
			var rootFormId = this.path.split('.')[0];
			return this.$store.getters['FORMS/FORM'](rootFormId);
		},
		getRootForm() {
			var rootFormId = this.getPath().split('.')[0];
			return this.$store.getters['FORMS/FORM'](rootFormId);
		},
		/**
		 * Submit the form - note this does the same as clicking a submit button.
		 * Due to the HTML spec calling submit on the form element itself (this.$el.submit)
		 * will not  call its onsubmit handlers. Submit events are only fired when submit is triggered via a submit
		 * button or 'enter' key press.
		 * This function aims to make submitting a form programmatically more predictable by also firing the submit
		 * events.
		 * Note: if you are not submitting via ajax then after submit will never get called as the form will submit
		 * and thus do a new http post request.  Before submit event will fire.
		 */
		submit() {
			// submit the form call submit on the html element
			//this.$el.submit();
			return this.handleSubmit();
		},
		handleSubmit(e) {
			// prevent submission - we will do this manually later if necessary via this.$el.submit();
			if (e) e.preventDefault();
			var vm = this;
			neon.form.updateField(vm.id, {submitted: true});
			neon.form.updateField(vm.id, {isSubmitted: true});
			// todo: should make this function return a promise - resolve if submitted and response successful - reject if validation fails or submit request fails

			var processSubmit = function () {
				// pass simple event object in - if submit is set to false by any event listening function
				// it will stop the form submitting
				// or call preventDefault();
				var event = {submit: true, preventDefault: function(){ this.submit=false; }};
				vm.$emit('beforeSubmit', event);
				// don't post if we have a validation error
				// or if the before event event object submit prop is false
				var isValid = vm.enableClientValidation ? vm.validate() : true;
				if (event.submit === false || !isValid) {
					neon.form.updateField(vm.id, {submitted: false});
					return false;
				}
				if (vm.enableAjaxSubmission) {
					vm.ajaxSubmit().then(function(response) {
						// Handle a successful submission
						event.success = true;
						event.response = response;
						vm.$emit('afterSubmit', event);
					}).catch(function(err) {
						console.error(err, 'ajaxSubmit failed');
						// Handle an unsuccessful submission
						event.success = false;
						event.error = err;
						vm.$emit('afterSubmit', event);
						neon.form.updateField(vm.id, {submitted: false});
					});
					return false;
				} else {
					// submit the form - will do html form post and reload page
					// the page will now be refreshed
					vm.$el.submit();
					return true;
				}
			};
			// synchronous ajax validation
			if (this.enableAjaxValidation) {
				this.ajaxValidate()
					.then(processSubmit)
					.catch(function(err) {
						console.error(err, 'VALIDATION FAILED');
						// Catch the rejected promise caused by validation errors
						vm.$emit('validationFailed', err);
					});
			} else {
				return processSubmit()
			}
		},
		ajaxSubmit() {
			return this.$store.dispatch('FORMS/AJAX_SUBMIT_FORM', {path: this.path});
		},
		ajaxValidate(path) {
			var vm = this;
			if (!_.isArray(path))
				var path = _.values(arguments);
			if (path.length)
				return this.$store.dispatch('FORMS/AJAX_VALIDATE_FORM', {path: path});
			else
				return this.$store.dispatch('FORMS/AJAX_VALIDATE_FORM', {path: this.path});
		},
		/**
		 * Validate the form using the client side validators
		 * Will not run the ajax validation - call this.ajaxValidate() to trigger server validation
		 *
		 * @param {String} [path] - if not defined will use this.path
		 * @returns {boolean} - true if valid (there are no errors) and false if invalid
		 * (the form and/or one its fields)
		 */
		validate(path) {
			var vm = this;
			if (!_.isArray(path))
				var path = _.values(arguments);

			if (path.length)
				_.each(path, function (pathPart) { vm.$store.dispatch('FORMS/VALIDATE_FIELD', {path: pathPart}); });
			else
				// validate this form and its children
				this.$store.dispatch('FORMS/VALIDATE_FIELD', {path: this.path});
			return !this.hasErrors();
		},
		reset() {
			return this.$store.commit('FORMS/RESET', {id: this.id});
		},
		hasErrors() {
			return this.$store.getters['FORMS/hasErrors'](this.path);
		},
		getErrors() {
			// all errors from fields that have been validated
			// will not return errors for a field unless validate has been called for it
			// path = ['FormNewStarter.hmrc_has_p45'];var ret = {}; _.each(errors, function(item, key){if (path.indexOf(key) !== -1)  {ret[key] = item} });
			var path = _.values(arguments);

			var errors = this.$store.getters['FORMS/getErrors'](this.path);

			if (path.length === 0)
				return errors;

			var ret = {}; _.each(errors, function(item, key) {if (path.indexOf(key) !== -1)  { ret[key] = item; } });
			return ret
		},
		getData() {
			return neon.form.getData(this.path);
		},
		isForm() {
			return true;
		},
		isRoot() {
			// this form is a root form if it has no parent form
			return this.parentForm === false;
		},
		/**
		 * The unique path to a form is constructed by the root parent id and then the child forms and fields names
		 * joined with a full stop. e.g. {id}.{name}.{name} this is mainly because id's contain the full concatenated
		 * path of a field or a form names indicate just the identifier at that level. So names are unique in its level
		 * of the form tree only ids are unique at any level because they contain the tree information (like the path)
		 * separated with dashes.
		 * @returns {string}
		 */
		getPath() {
			// we are a sub form if we have a parent form
			if (this.parentForm) return this.parentForm.getPath() + '.' + this.name;
			// we are the root form so just return the id.
			// if no id has been set then return the name
			return this.id ? this.id : this.name;
		},
		/**
		 * return a path to the item this is similar to getPath but uses the rootForms name instead of its id
		 * the server often does not require the rootForm id namespace
		 * @returns {string|*}
		 */
		getServerPath() {
			// we are a sub form if we have a parent form
			if (this.parentForm) return this.parentForm.getPath() + '.' + this.name;
			return this.name;
		},
		/**
		 * @duplicated todo duplicated in formrepeater form
		 * @returns {boolean}
		 */
		isSubForm() {
			// this form is a sub form if its parent component is a form
			return this.parentForm !== false;
		},
		component: function(className) {
			if (_.isUndefined(className)) {return 'neon-core-form-fields-text';}
			// translates component/class names like '\neon\form\MyComponent' to 'neon-forms-mycomponent'
			className = className.replace(/\\/g, '-').toLowerCase();
			// attempts to find component - will attempt to prefix 'neon-core-form-fields-' if not found.
			var cmp = Vue.component(className) || Vue.component('neon-core-form-fields-'+className);
			if (_.isUndefined(cmp) && neon.isDebug()) {
				console.error('Unknown vue component: "'+className+'"');
				// return spec for stand in component - adds div with error message
				return {render: function(e){
					return e('div', {style:{color:'red',padding:'10px',border:'2px solid red',margin:'10px'}},
						'Unknown form field component "'+className+'" ')
				}}
			}
			return cmp;
		},
		setReadOnly: function(value) {
			this.$store.commit('FORMS/UPDATE_FIELD', {path: this.name, updates: {
				readOnly: value
			}});
		},

		/**
		 * update the state of a field by its form path
		 * @param {String} path - the form string path (formName.fieldName)
		 * @param {Object} updates
		 */
		updateField: function(path, updates) {
			this.$store.commit('FORMS/UPDATE_FIELD', {path: path, updates: updates});
		},

		isEditMode() {
			if (this.rootForm == false) {
				return this.id == 'builder'
			}
			return this.rootForm.isEditMode();
		}
	}
});



/**
 * Created by newicon on 26/01/2018.
 */
Vue.component('neon-core-form-formrepeater', {
	provide: function() {
		// As the parentForm property is injected into forms by their parent forms (if they have them)
		// a root form will not have a parentForm
		if (this.parentForm === false) {
			// must be the root form
			return {
				rootForm: this,
				parentForm: this
			}
		}

		// not a root form so only need provide the immediate parent
		// Vue will inject the rootForm for us into any child hierarchy
		return {
			parentForm: this
		}
	},
	inject: {
		// the root form will have no parent form so set the parentForm to false by default
		// therefore a root form will always have its parentForm property === false
		'rootForm': { default: false },
		'parentForm': { default: false }
	},
	props: {
		id: {type: String, required: true},
		name: {type: String, required: true},
		fields: {type: Object, default: () => {return {}}},
		label: {type: String, default: ''},
		styleType: {type: String, default: 'inline'},
		count: {type: Number, default: 1},
		countMax: {type: Number, default: 0},
		countMin: {type: Number, default: 1},
		allowAdd: {type: Boolean, default: true},
		allowRemove: {type: Boolean, default: true},
		template: Object,
		visible: {type: Boolean, default: true},
		hint: String,
		// a repeater simply passes this property onto its children
		inline: {type: Boolean, default: false},
		readOnly: {type: Boolean, default: false},
		printOnly: {type: Boolean, default: false}
	},
	created: function() {
		this.$store.dispatch('FORMS/createFieldState', {path: this.getPath(), props: this.$props});
		var rowsToCreate = this.shouldCreateRows();
		if (rowsToCreate > 0) {
			for (i = 0; i < rowsToCreate; i++) {
				this.onAddRow(i);
			}
		}
	},
	/**
	<!-- This component will be a form containing all the child forms -->
	 */
	template: `
		<component :is="(isReadOnly || isPrintOnly) ? 'div' : 'fieldset'" class="form-group neonRepeater" :id="getId()">

			<div class="neonForm_hint">{{hint}}</div>

			<!--
			Show a placeholder header so we can add a subform if we currently have no subforms
			It's a little confusing to show the parent form (that contains the subforms) title and then the titles for
			all subforms within it.  This may be different for inline subforms that do not have a title them selves
			-->
			<legend class="neonLegend" slot="header" v-if="countRows() == 0">
				<div class="neonLegend_title">{{label}}</div>
				<div v-if="!(isReadOnly || isPrintOnly)" class="text-right neonLegend_controls">
					<button v-if="allowAdd" :disabled="!canAddRows()" class="btn btn-default neonRepeater_button neonRepeater_buttonLegend" @click.prevent="onAddRow()"><i class="fa fa-plus"></i> Add</button>
				</div>
			</legend>

			<!-- Each component will represent a child form -->
			<component v-for="(field, key, index) in fields" :key="key" :is="component(field.class)" :name="''+key" v-bind="field" id="" :class="''" :inline="inline" >
				<legend slot="header" class="neonLegend">
					<div class="neonLegend_title">{{label}}</div>
					<div v-if="!(isReadOnly || isPrintOnly)" class="text-right neonLegend_controls">
						<button v-if="allowRemove && canRemoveRows()" :disabled="!canRemoveRows()" class="btn btn-default neonRepeater_button" @click.prevent="onRemoveRow(key)"><i class="fa fa-minus"></i> Remove</button>
						<button v-if="Object.keys(fields).length==0" :disabled="!canAddRows()" class="btn btn-default neonRepeater_button neonRepeater_buttonLegend" @click.prevent="onAddRow()"><i class="fa fa-plus"></i> Add</button>
					</div>
				</legend>
				<div v-if="!(isReadOnly || isPrintOnly) && (index==Object.keys(fields).length-1)" class="text-right neonLegend_controls">
					<button v-if="allowAdd" :disabled="!canAddRows()" class="btn btn-default neonRepeater_button neonRepeater_buttonLegend" @click.prevent="onAddRow()"><i class="fa fa-plus"></i> Add</button>
				</div>
			</component>
		</component>
	`,
	computed: {
		// TODO tidy up duplicate in baseField 10/04/2018
		isReadOnly: function() {
			var rootFormName = this.getPath().split('.')[0];
			if (this.$store.state.FORMS[rootFormName]) {
				return this.readOnly || this.$store.state.FORMS[rootFormName].readOnly;
			}
			return false;
		},
		isPrintOnly: function() {
			var rootFormName = this.getPath().split('.')[0];
			if (this.$store.state.FORMS[rootFormName]) {
				return this.printOnly || this.$store.state.FORMS[rootFormName].printOnly;
			}
			return false;
		},
		path: function() {
			return this.getPath();
		},
		field() {
			return this.$store.getters['FORMS/getField'](this.path);
		},
	},
	methods: {
		getId: function() {
			if (this.id)
				return this.id;
			return this.path.replace(/\./g, '-');
		},
		canAddRows: function() {
			if (this.canMakeChanges())
				// if countMax is zero then we can always add a new row
				return this.countMax == 0 || (this.countRows() < this.countMax);
			return false;
		},
		canRemoveRows: function() {
			return this.canMakeChanges() && this.countRows() > this.countMin;
		},
		countRows: function() {
			return Object.keys(this.fields).length;
		},
		canMakeChanges: function() {
			return !(this.isPrintOnly || this.isReadOnly);
		},
		/**
		 * Return the number of empty form instances we should create
		 * If countMin is set to 3 and you have no data this will return 3 and the repeater will create 3 empty form
		 * row instances
		 * @returns {number}
		 */
		shouldCreateRows: function() {
			if (this.canMakeChanges())
				return this.count - this.countRows();
			return 0;
		},
		getData: function() {
			return this.$store.getters['FORMS/getData'](this.path);
			// let data = {};
			// _.each(this.$children, (vm) => {
			// 	if (vm.getData)
			// 		data[vm.name] = vm.getData();
			// });
			// return data;
		},
		isForm: function() {
			return true;
		},
		/**
		 * @duplicated todo duplicated in formrepeater form
		 * @returns {boolean}
		 */
		isSubForm: function() {
			// this form is a sub form if its parent component is a form
			return this.parentForm !== false
		},
		/**
		 * The unique path to a form is constructed by the root parent id and then the child forms and fields names
		 * joined with a full stop. e.g. {id}.{name}.{name} this is mainly because id's contain the full concatenated
		 * path of a field or a form names indicate just the identifier at that level. So names are unique in its level
		 * of the form tree only ids are unique at any level because they contain the tree information (like the path)
		 * separated with dashes.
		 * @returns {string}
		 */
		getPath: function() {
			// we are a sub form if we have a parent form
			if (this.parentForm) {
				return this.parentForm.getPath() + '.' + this.name;
			}
			// we are the root form so just return the id.
			return this.id;
		},
		onAddRow: function(key) {
			var uuid = _.uuid('');
			var defParsed = JSON.parse(JSON.stringify(this.template));
			defParsed.name = uuid;
			defParsed.label = this.label;
			delete defParsed.id;
			this.$store.dispatch('FORMS/ADD_FIELD', {path: this.getPath(), definition: defParsed, key: key});
		},
		onRemoveRow: function(key) {
			this.$store.dispatch('FORMS/REMOVE_FIELD', {objectPath: this.getPath(), deleteKey: key});
		},
		component: function(className) {
			if (_.isUndefined(className)) {return 'neon-core-form-fields-text';}
			let c =  className.replace(/\\/g, '-').toLowerCase();
			return c;
		}
	}
});

Vue.directive('nl2br', {
	bind: function (el, binding, vnode) {
		if (!_.isString(binding.value))
			return '';
		el.innerHTML = binding.value.replace(/\n/g,"<br />");
	}
});

Vue.component('neon-markdown', {
	props: {
		markdown: {type: String, default: ''}
	},
	render(create) {
		return create('div', { domProps: { innerHTML: this.markdownHtml } });
	},
	computed: {
		markdownHtml: function() {
			marked.setOptions({
				renderer: new marked.Renderer(),
				pedantic: false,
				gfm: true,
				tables: true,
				breaks: true,
				sanitize: false,
				smartLists: true,
				smartypants: false,
				xhtml: false
			});
			var markdownText = this.markdown || '';
			const outHtml = marked(markdownText);
			this.$emit('rendered', outHtml);
			return outHtml;
		}
	},
});
