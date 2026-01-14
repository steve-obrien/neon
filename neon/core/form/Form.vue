<template>
	<component @input="bubbleInput($event)" @change="bubbleInput($event)" @submit="handleSubmit" :is="isRoot() ? 'form' : ((isReadOnly || isPrintOnly) ? 'div' : 'fieldset')" method="post" enctype="multipart/form-data" :action="action" class="neonForm" :class="attributes.class" :name="name" :id="getId()" v-bind="attributes">
		<slot name="header">
			<div v-if="label" class="form-group">
				<legend>{{ label }}</legend>
			</div>
		</slot>
		<slot name="formError">
		</slot>
		<div class="neonForm_errors" v-if="errors[0]">
			<div class="neonField_error">{{ errors[0] }}</div>
		</div>
		<slot name="fields">
			<component v-for="(field, key) in form.fields" v-if="field.class" :is="component(field.class)" v-bind="field" id="" :class="''" :key="key"></component>
		</slot>

		<!-- default slot html inside the form tags such as other fields will appear here -->
		<slot></slot>
		<input type="hidden" :name="form.csrfParam" :value="form.csrfToken" v-if="isRoot()" />
	</component>
</template>
<script>
export default {
	name: 'neon-core-form-formvvv',
	store: neon.Store,
	props: {
		config: { type: Object },
		id: { type: String, required: true, default: '' },
		name: { type: String, required: true },
		errors: { type: [Array, Object], default: () => [] },
		csrfParam: { type: String, required: false },
		csrfToken: { type: String, required: false },
		label: { type: String, default: '' },
		hint: { type: String, default: '' },
		visible: { type: Boolean, default: true },
		inline: { type: Boolean, default: false },
		action: { type: String, default: '' },
		readOnly: { type: Boolean, default: false },
		fields: { type: [Object, Array], default: () => { return {} } },
		enableAjaxValidation: { type: [Boolean, String], default: false },
		enableAjaxSubmission: { type: [Boolean, String], default: false },
		validationUrl: { type: String, default: '' },
		attributes: { type: Object, default: function () { return { class: '' } } },
		printOnly: { type: Boolean, default: false },
		isSubmitted: { type: Boolean, default: true },
		validateOnBlur: { type: Boolean, default: true },
		enableClientValidation: { type: Boolean, default: true },
		/**
		 * A token hash referencing the serialised form object
		 */
		objectToken: { type: String },
	},
	provide: function () {
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
	created: function () {
		if (this.isRoot()) {
			this.$store.commit('FORMS/CREATE', this.$props);
			// store a reference to the form component
			// this is useful for direct access such as
			// neon.form.forms[myid].submit();
			// neon.form.forms[myid].validate();
			// etc etc
			window.neon.form.forms[this.id] = this;
		} else {
			this.$store.dispatch('FORMS/createFieldState', { path: this.getPath(), props: this.$props });
		}
	},
	mounted: function () {
		this.$emit('mounted', this);
		neon.app.$emit('form-' + this.id + '.mounted', this);
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
			set: function (value) {
				var pathToValues = _.flattenObject(value);
				var form = this;
				_.forEach(pathToValues, function (value, key) {
					neon.form.updateField(form.id + '.' + key, { value: value });
				});
			},
			get: function () {
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
		path: function () {
			return this.getPath();
		}
	},
	methods: {
		bubbleInput: function (e) {
			this.$emit('input', this.formData)
		},
		// region Events ===============================================================================================
		onChange: function (callback) {
			var vm = this;
			this.$on('change', function (e) { callback(vm.formData, e); });
		},
		onBeforeSubmit: function (callback) {
			this.$on('beforeSubmit', function (e) { callback(e); });
		},
		onAfterSubmit: function (callback) {
			this.$on('afterSubmit', function (e) { callback(e); });
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
			neon.form.updateField(vm.id, { submitted: true });
			neon.form.updateField(vm.id, { isSubmitted: true });
			// todo: should make this function return a promise - resolve if submitted and response successful - reject if validation fails or submit request fails

			var processSubmit = function () {
				// pass simple event object in - if submit is set to false by any event listening function
				// it will stop the form submitting
				// or call preventDefault();
				var event = { submit: true, preventDefault: function () { this.submit = false; } };
				vm.$emit('beforeSubmit', event);
				// don't post if we have a validation error
				// or if the before event event object submit prop is false
				var isValid = vm.enableClientValidation ? vm.validate() : true;
				if (event.submit === false || !isValid) {
					neon.form.updateField(vm.id, { submitted: false });
					return false;
				}
				if (vm.enableAjaxSubmission) {
					vm.ajaxSubmit().then(function (response) {
						// Handle a successful submission
						event.success = true;
						event.response = response;
						vm.$emit('afterSubmit', event);
					}).catch(function (err) {
						console.error(err, 'ajaxSubmit failed');
						// Handle an unsuccessful submission
						event.success = false;
						event.error = err;
						vm.$emit('afterSubmit', event);
						neon.form.updateField(vm.id, { submitted: false });
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
					.catch(function (err) {
						console.error(err, 'VALIDATION FAILED');
						// Catch the rejected promise caused by validation errors
						vm.$emit('validationFailed', err);
					});
			} else {
				return processSubmit()
			}
		},
		ajaxSubmit() {
			return this.$store.dispatch('FORMS/AJAX_SUBMIT_FORM', { path: this.path });
		},
		ajaxValidate(path) {
			var vm = this;
			if (!_.isArray(path))
				var path = _.values(arguments);
			if (path.length)
				return this.$store.dispatch('FORMS/AJAX_VALIDATE_FORM', { path: path });
			else
				return this.$store.dispatch('FORMS/AJAX_VALIDATE_FORM', { path: this.path });
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
				_.each(path, function (pathPart) { vm.$store.dispatch('FORMS/VALIDATE_FIELD', { path: pathPart }); });
			else
				// validate this form and its children
				this.$store.dispatch('FORMS/VALIDATE_FIELD', { path: this.path });
			return !this.hasErrors();
		},
		reset() {
			return this.$store.commit('FORMS/RESET', { id: this.id });
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

			var ret = {}; _.each(errors, function (item, key) { if (path.indexOf(key) !== -1) { ret[key] = item; } });
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
		component: function (className) {
			if (_.isUndefined(className)) { return 'neon-core-form-fields-text'; }
			// translates component/class names like '\neon\form\MyComponent' to 'neon-forms-mycomponent'
			className = className.replace(/\\/g, '-').toLowerCase();
			// attempts to find component - will attempt to prefix 'neon-core-form-fields-' if not found.
			var cmp = Vue.component(className) || Vue.component('neon-core-form-fields-' + className);
			if (_.isUndefined(cmp) && neon.isDebug()) {
				console.error('Unknown vue component: "' + className + '"');
				// return spec for stand in component - adds div with error message
				return {
					render: function (e) {
						return e('div', { style: { color: 'red', padding: '10px', border: '2px solid red', margin: '10px' } },
							'Unknown form field component "' + className + '" ')
					}
				}
			}
			return cmp;
		},
		setReadOnly: function (value) {
			this.$store.commit('FORMS/UPDATE_FIELD', {
				path: this.name, updates: {
					readOnly: value
				}
			});
		},

		/**
		 * update the state of a field by its form path
		 * @param {String} path - the form string path (formName.fieldName)
		 * @param {Object} updates
		 */
		updateField: function (path, updates) {
			this.$store.commit('FORMS/UPDATE_FIELD', { path: path, updates: updates });
		}
	}
};

</script>