<template>
	<div :class="[classes, attributes.class]" class="neonField" v-show="isEditMode() ? true : visible" :style="{'opacity': (isEditMode() && !visible ?'0.5':'1')}" >
		<transition name="neonFade">
			<div class="neonField_content" v-show="fieldShouldShow()">
				<label v-if="label" class="neonField_label control-label" :for="labelFor">{{ label }}</label>
				<div class="hint-block hint-top neonField_hint">
					<neon-markdown :markdown="hint"></neon-markdown>
				</div>
				<div class="neonInput">
					<slot></slot>
					<label v-if="placeholderLabel" class="neonInput_placeholderLabel">{{ placeholder ? placeholder : label }}</label>
					<div class="neonField_errors">
						<transition name="neonFade">
							<div class="neonField_error help-block" v-if="getFirstError() && (isSubmitted || field.flags.touched)">{{ getFirstError() }}</div>
						</transition>
					</div>
				</div>
			</div>
		</transition>
	</div>
</template>

<script>
export default {
	name: 'neon-core-form-field',
	inject: ['parentForm', 'rootForm'],
	props: {
		required: Boolean,
		errors: { type: [Array, Object], default: () => [] },
		label: String,
		hint: { type: String },
		labelFor: String,
		inline: { type: Boolean, default: () => false },
		visible: { type: Boolean, default: true },
		attributes: { type: [Object, Array], default: function () { return {} } },
		showIf: { type: [Array, Object] },
		flags: { type: Object },
		placeholder: String,
		placeholderLabel: { type: Boolean, default: false }
	},
	computed: {
		classes() {
			var touched = this.isSubmitted || (_.isDefined(this.field.flags) && this.field.flags.touched);
			return {
				// Old styles to deprecate
				'has-error': this.hasError && touched,
				'form-group': !this.inline,
				'form-inline': this.inline,
				'isRequired': this.required,
				'isError': this.hasError && touched,
				'isValid': !this.hasError,
				'isFocused': _.isDefined(this.field.flags) && this.field.flags.focused,
				'isTouched': touched,
				'isDirty': this.field.value && _.isDefined(this.field.flags) && this.field.flags.dirty,
				'niForm': this.placeholderLabel,
				'isFixedPlaceholder': this.placeholderLabel
			}
		},
		path: function () {
			return this.getPath();
		},
		field: {
			set(val) {
				//console.log(val, 'field set');
			},
			get() {
				var flags = this.$store.getters['FORMS/getField'](this.path);
				return flags || {};
			}
		},
		/**
		 * Whether the parent form has been submitted
		 * @returns {neon-core-form-field.computed.submitted|(function())|neon-core-form-fields-submit.computed.submitted|neon-core-form-form.computed.submitted|boolean}
		 */
		submitted: function () {
			return this.$store.getters['FORMS/FORM'](this.rootForm.id).submitted
		},
		/**
		 * Whether the form has been submitted in the past
		 */
		isSubmitted: function () {
			return this.$store.getters['FORMS/FORM'](this.rootForm.id).isSubmitted;
		},
		firstError: function () {
			return this.$store.getters['FORMS/getErrors'](this.path);
		},
		hasError: function () {
			return this.$store.getters['FORMS/hasErrors'](this.path);
		},
	},
	methods: {

		fieldShouldShow: function (showIf = this.showIf) {
			if (_.isEmpty(showIf))
				return true;

			let booleanOperators = [
				'and', 'or', '&&', '||', '!=', '!==', '=', '==', '==='
			];
			let key = _.findKey(showIf);
			if (_.includes(booleanOperators, key)) {
				let length = showIf[key].length || Object.keys(showIf[key]).length;
				if (length < 2) {
					console.error("A boolean expression requires at least two operands")
					return true;
				}

				let field = this;
				function evaluateOrStatement() {
					// keep checking terms - return true as soon as we hit a true statement for 'or'
					for (let i = 0; i < length; i++) {
						if (field.fieldShouldShow(showIf[key][i]))
							return true;
					}
					return false;
				}
				function evaluateAndStatement() {
					// keep checking terms - return false as soon as we hit a false statement for 'and'
					for (let i = 0; i < length; i++) {
						if (!field.fieldShouldShow(showIf[key][i]))
							return false;
					}
					return true;
				}
				switch (key) {
					case '&&': case 'and': {
						return evaluateAndStatement();
					}
					case '||': case 'or': {
						return evaluateOrStatement();
					}
					case '!=': case '!==': {
						let lhs = this.fieldShouldShow(showIf[key][0]);
						let rhs = this.fieldShouldShow(showIf[key][1]);
						return lhs !== rhs;
					}
					case '=': case '==': case '===': {
						let lhs = this.fieldShouldShow(showIf[key][0]);
						let rhs = this.fieldShouldShow(showIf[key][1]);
						return lhs === rhs
					}
					default: {
						return true;
					}
				}
			}

			if (!_.isString(showIf[0])) {
				console.error('The first part of the rule array must be a string path to an object a global or this.$props.item')
				return true;
			}

			var path = showIf[0];
			var operator = showIf[1];
			var value = showIf[2];
			var targetValue;

			if (path.substring(0, 7) === 'window.') {
				targetValue = _.get(window, path.substring(7));
			} else if (path.substring(0, 5) === 'this.') {
				targetValue = _.get(this, path.substring(5));
			} else if (path.indexOf('.') > -1) { //assume this means a fully qualified path - required eg for showIfs to work with appForms
				let pathParts = path.split('.');
				let root = this.rootForm.value;
				for (let i = 0; i < pathParts.length; i++) {
					if (_.isUndefined(root[pathParts[i]])) break;
					root = root[pathParts[i]];
					if (i + 1 === pathParts.length)
						targetValue = root;
				}
			} else {
				// assume we are checking form values on the immediate parent form.
				targetValue = _.get(this.parentForm.value, path);
			}
			return neon.filter(operator)(targetValue, value);
		},
		field: function () {
			var field = this.$store.getters['FORMS/getField'](this.path)
			return field || {};
		},
		getPath: function () {
			if (this.$parent.getPath)
				return this.$parent.getPath();
		},
		getFirstError: function () {
			return _.values(this.field.errors)[0];
		},
		isEditMode() { return this.rootForm.isEditMode(); },
	},
}
</script>