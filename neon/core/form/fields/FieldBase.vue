<script>
export default {
	name: 'neon-core-form-fields-base',
	store: neon.Store,
	inject: ['parentForm', 'rootForm'],
	// separate props into a standard field props object to import in
	props: {
		name: {type: String, required: true, editor: false},
		label: {type: String, editor: {order:128, component: 'neon-core-form-fields-text', placeholder: 'Set the label for the field'}},
		hint: {type: String, editor: {order:256, component: 'neon-core-form-fields-markdown', editorOptions: {showGutter: false, highlightActiveLine: false}, label:'Guide Text', placeholder: 'Add additional guide text'}},
		placeholder: {type: String, editor: {order:384, component: 'neon-core-form-fields-text', placeholder: 'Add helper placeholder text'}},
		placeholderLabel: {type: Boolean, editor: {order:400}},
		value: {type: String, editor: {order:512, label: 'Default Value'}, default:''},
		required: {type: Boolean, editor: {order:640}},
		errors: {type: [Array, Object], editor: false },
		visible: {type: Boolean, default: true, editor:{order:768}},
		disabled: {type: Boolean, default: false, editor:{order:12800}},
		showIf: {type: [Array, Object], editor:{order: 12928} },
		inline: {type: Boolean, default: false, editor: false},
		readOnly: {type: Boolean, default: false, editor: false},
		printOnly: {type: Boolean, default: false, editor: false},
		validators: {type: [Array, Object], default: function() { return {}; }, editor:false },
		attributes: {type: [Array, Object], default: function() {return {class: ''}}, editor:false },
		validateOnBlur: {type: Boolean, default: true},
		enableAjaxValidation: {type: Boolean, default:null, editor:false}, // if not set gets property from parent
		enableClientValidation: {type: Boolean, default: true},
		flags: {
			type: Object, editor: false, default: function () {
				return {
					// true if the field is currently focused
					focused: false,
					// true if the field has previously been blurred
					touched: false,
					// dirty if the field value has been changed or the field has received input
					dirty: false,
				};
			}
		}
	},
	created: function() {
		this.$store.dispatch('FORMS/createFieldState', {path: this.getPath(), props: this.$props});
	},
	methods: {
		updateField(updateData) {
			return this.$store.commit('FORMS/UPDATE_FIELD', {path: this.getPath(), updates: updateData});
		},
		/**
		 * set the field flags
		 * @example this.setFlags({touched:true}) // make the field touched
		 * @param flags
		 */
		setFlags(flags) {
			this.updateField({flags: Object.assign({}, this.field.flags, flags)});
		},
		onFocus() {
			this.setFlags({focused: true});
			this.$emit('focus');
		},
		onBlur() {
			this.setFlags({focused: false,  touched: true});

			if (this.shouldValidateOnBlur && this.enableClientValidation) {
				this.validate();
			}
			this.$emit('blur');
		},
		getPath() {
			if (this.parentForm) {
				return this.parentForm.getPath() + '.' + this.name;
			} else {
				// no parent form
				console.warn('This does not have a parent form', this.name);
				return this.name;
			}
		},
		getServerPath() {
			if (this.parentForm) {
				return this.parentForm.getServerPath() + '.' + this.name;
			} else {
				// no parent form
				console.warn('This does not have a parent form', this.name);
				return this.name;
			}
		},
		getData() {
			return this.value;
		},
		hasError() {
			return Object.values(this.errors).length;
		},
		validate() {
			this.$store.dispatch('FORMS/VALIDATE_FIELD', {path: this.getPath()});
			if (this.shouldAjaxValidate) {
				this.rootForm.ajaxValidate(this.getPath());
			}
		},
		/**
		 * Get the root form vue instance
		 * @returns {Object}
		 */
		getRootFormInstance() {
			return this.rootForm;
		},
	},
	computed: {
		
		shouldValidateOnBlur() {
			return this.validateOnBlur === null ? this.rootForm.validateOnBlur : this.validateOnBlur
		},
		shouldAjaxValidate() {
			return this.enableAjaxValidation === null ? this.rootForm.enableAjaxValidation : this.enableAjaxValidation
		},
		/**
		 * Whether the form field is valid
		 * @returns {boolean}
		 */
		isValid() {
			return Object.values(this.errors).length === 0;
		},
		/**
		 * Whether the root form is currently submitting a request
		 * @returns {neon-core-form-field.computed.submitted|(function(): (neon-core-form-field.computed.submitted|(function())|BaseField.computed.submitted|neon-core-form-form.computed.submitted|boolean))|BaseField.computed.submitted|(function())|neon-core-form-form.computed.submitted|boolean}
		 */
		submitted: function() {
			return this.$store.getters['FORMS/FORM'](this.rootForm.id).submitted
		},
		/**
		 * Whether the form has been submitted in the past
		 */
		isSubmitted: function(){
			return this.$store.getters['FORMS/FORM'](this.rootForm.id).isSubmitted;
		},
		path: function() {
			return this.getPath();
		},
		isReadOnly() {
			if (this.rootForm) {
				return this.readOnly || this.rootForm.readOnly;
			}
			return false;
		},
		isPrintOnly() {
			if (this.rootForm) {
				return this.printOnly || this.rootForm.printOnly;
			}
			return false;
		},
		/**
		 * Get the object representing properties of the field
		 * these include:
		 * - label
		 * - error
		 * - id
		 * - name
		 */
		fieldProps() {
			return {
				required: this.required,
				name: this.name,
				// computed prop
				labelFor: this.id,
				label: this.label,
				hint: this.hint,
				placeholder: this.placeholder,
				placeholderLabel: this.placeholderLabel,
				errors: this.errors,
				value: this.value,
				inline: this.inline,
				visible: this.visible,
				dirty: this.dirty,
				attributes: this.attributes,
				showIf: this.showIf,
				flags: this.flags,
			};
		},
		field() {
			return this.$store.getters['FORMS/getField'](this.path);
		},
		modelValue: {
			get() {
				if (_.isUndefined(this.field))
					return this.value;
				return this.field.value;
			},
			set(value) {
				if (!this.$store) this.$store = neon.Store;
				this.setFlags({dirty: true});
				neon.form.updateField(this.path, {value: value});
				neon.form.validateField(this.path);
				this.$emit('input', value);
				// raise an event on the root form, to allow hooking into the update
				this.rootForm.bubbleInput();
				this.rootForm.$emit('change',{path: this.path, name: this.name, value: value});
			}
		},
		/**
		 * @duplicate todo: remove duplicate function in field and form
		 * @returns String correct html input name string
		 */
		inputName() {
			var bits = this.path.split('.');
			// replace the first part of the path with the root forms name NOT its id
			// a path is rootForm.id then each fields name.
			bits.shift(); // remove the initial id
			return this.rootForm.name + '[' + bits.join('][') + ']';
		},
		id() {
			return this.path.replace(/\./g, '-');
		},
		isEditMode() { return this.rootForm.isEditMode(); },
	}
};
</script>