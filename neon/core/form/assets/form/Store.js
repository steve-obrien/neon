/**
 * Global helpers for forms
 * @type {Object}
 */
neon.form = neon.form || {};

// store direct references to root form components
neon.form.forms = {};

/**
 * Update a field in the store
 * @param {String} path - the string path to the field (this is its unique key inside the store) rootFormId.fieldName
 * @param {Object} updates
 */
neon.form.updateField = function (path, updates) {
	neon.Store.commit('FORMS/UPDATE_FIELD', {path: path, updates: updates});
};

/**
 * Run validation for a field specified by its path
 * @param {String} path
 */
neon.form.validateField = function (path) {
	return neon.Store.dispatch('FORMS/VALIDATE_FIELD', {path: path});
};

neon.form.getData = function(path) {
	return neon.Store.getters['FORMS/getData'](path);
};

/**
 * Create a new form from props in the store
 * This is useful when large amounts of json encoded data needs to be passed to the form
 * and prevents having to first pass through a html template which could break encodings
 * @param props
 */
neon.form.create = function(props) {
	neon.Store.commit('FORMS/CREATE', props);
};

neon.form.validators = (function () {

	var required = function(options, field) {
		return _runYiiValidation('required', field.value, options);
	};

	var string = function(options, field) {
		return _runYiiValidation('string', field.value, options);
	};

	var email =  function(options, field) {
		options.pattern = /^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/;
		options.fullPattern = /^[^@]*<[a-zA-Z0-9!#$%&'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?>$/;
		return _runYiiValidation('email', field.value, options);
	};

	var regularExpression = function(options, field) {
		// regualr expressions will be encoded to json therefore they will be "/[0-9]/"
		// we need to convert this into a javascript
		var yiiOptions = _.clone(options);
		yiiOptions.pattern = new RegExp(options.pattern.slice(1, -1));
		return _runYiiValidation('regularExpression', field.value, yiiOptions);
	};

	var _runYiiValidation = function(name, value, options) {
		var messageArray = [];
		yii.validation[name](value, messageArray, options);
		return messageArray[0];
		// messageArray.forEach(function(item) { messagesObject[options.key || name] = item; });
		// return messagesObject;
	};

	return {
		"neon\\core\\validators\\RequiredValidator": required,
		"required": required,

		"neon\\core\\validators\\StringValidator": string,
		"string": string,

		"neon\\core\\validators\\EmailValidator": email,
		"email": email,

		"neon\\core\\validators\\RegularExpressionValidator": regularExpression,
		"match": regularExpression,
		"regularExpression": regularExpression
	};
})();

/**
 * Created by steve on 08/02/2018.
 */
_.mixin({
	keysFromPath(path) {
		// from http://codereview.stackexchange.com/a/63010/8176
		/**
		 * Repeatedly capture either:
		 * - a bracketed expression, discarding optional matching quotes inside, or
		 * - an unbracketed expression, delimited by a dot or a bracket.
		 */
		var re = /\[("|')(.+)\1\]|([^.\[\]]+)/g;

		var elements = [];
		var result;
		while ((result = re.exec(path)) !== null) {
			elements.push(result[2] || result[3]);
		}
		return elements;
	},

	// Gets the value at any depth in a nested object based on the
	// path described by the keys given. Keys may be given as an array
	// or as a dot-separated string.
	getPath: function (ks, obj) {
		ks = typeof ks == "string" ? _.keysFromPath(ks) : ks;
		var i = -1, length = ks.length;

		// If the obj is null or undefined we have to break as
		// a TypeError will result trying to access any property
		// Otherwise keep incrementally access the next property in
		// ks until complete
		while (++i < length && obj != null) {
			obj = obj[ks[i]];
		}
		return i === length ? obj : void 0;
	},

	getField: function(path, object) {
		if (_.isString(path))
			path = path.split('.');
		if (_.isUndefined(path))
			return undefined;
		let objPath = path.join('.fields.');
		return _.getPath(objPath, object);
	},

	// Returns a boolean indicating whether there is a property
	// at the path described by the keys given
	hasPath: function (ks, obj) {
		ks = typeof ks == "string" ? _.keysFromPath(ks) : ks;
		var i = -1, length = ks.length;
		while (++i < length && _.isObject(obj)) {
			if (ks[i] in obj) {
				obj = obj[ks[i]];
			} else {
				return false;
			}
		}
		return i === length;
	},

	uuid: function (s='-') {
		var format = 'XXXXXXXX'+s+'XXXX'+s+'XXXX'+s+'XXXX'+s+'XXXXXXXXXXXX';
		return format.replace(/[X]/g, function(c){
			return (Math.floor(Math.random() * 16) + 1).toString(16);
		});
	},

	bool: function(value) {
		if (value === '0' || value === 'false')
			return false;
		return !!value;
	},

	/**
	 * Returns the parent path for e.g.
	 * `
	 * getParentPath('root.parent.child')
	 * // => 'root.parent'
	 * getParentPath('root/parent/child', '/')
	 * // => 'root/parent'
	 * `
	 *
	 * @param {String} path - the string path
	 * @param {String} delimiter - defaults to '.'
	 */
	getParentPath: function(path, delimiter) {
		delimiter = delimiter || '.';
		var keys = _.keysFromPath(path);
		keys.pop();
		return keys.join(delimiter);
	}

});

neon.Store.registerModule('FORMS', {
	namespaced: true,
	strict: true,
	state: {},
	actions: {
		/**
		 *
		 * @param commit
		 * @param path
		 * @param definition
		 * @param key
		 */
		ADD_FIELD: function ({ commit }, { path, definition, key }) {
			commit('ADD_FIELD', { path, definition, key });
		},

		REMOVE_FIELD: function ({ commit }, { objectPath, deleteKey }) {
			commit('REMOVE_FIELD', { objectPath, deleteKey });
		},

		/**
		 * Validate a form or a field by its path,
		 * This recursively calls validate if the field or a child field is a form containing fields.
		 * @param commit
		 * @param state
		 * @param getters
		 * @param dispatch
		 * @param path
		 * @returns {Promise}
		 * @constructor
		 */
		VALIDATE_FIELD: function ({ commit, state, getters, dispatch }, {path}) {
			let field = getters.getField(path);
			if (_.isUndefined(field)) {
				console.error('No field found with path of "' + path + '"');
			}

			if (!_.isUndefined(field.fields)) {
				// must be a form.
				// therefore....
					_.each(field.fields, (field) => {
						dispatch('VALIDATE_FIELD', {path: path + '.' + field.name});
					});
			} else {
				// validate an individual field
				var messages = {};

				//getters.getValidatorFunctions(field);
				_.each(field.validators, function (validatorDefinition, key) {
					var validator = getters.getValidatorFunction(validatorDefinition);
					// prevent execution of this validator if a validator is not found!
					if (!_.isFunction(validator))
						return false;
					var messagesArray = validator(validatorDefinition, field);
					if (!_.isEmpty(messagesArray)) {
						if (_.isUndefined(messages[key])) {
							messages[key] = messagesArray;
						} else {
							messages[key] = _.concat(messages[key], messagesArray);
						}
					}
				});
				commit('UPDATE_FIELD', {path: path, updates: {errors: messages}});
			}
		},

		/**
		 * Synchronously validate a form over ajax,
		 * sending the form data to the validateUrl URL or action URL for server-side validation
		 * @param commit
		 * @param state
		 * @param getters
		 * @param dispatch
		 * @param path
		 */
		AJAX_VALIDATE_FORM: function ({ commit, state, getters, dispatch }, {path}) {
			return new Promise(function(resolve, reject) {
				var formData = {};
				var form;
				if (!_.isArray(path)) {
					// must be a form.
					// therefore....
					form = getters.getField(path);
					formData[form.name] = getters.getData(path);
				} else {
					// validate a group of fields
					_.each(path, function(pathPart) {
						var field = getters.getField(pathPart);
						var bits = pathPart.split('.');
						// remove form id from beginning of path.
						var formId = bits.shift();
						form =  getters.getField(formId);
						// add form name to the beginning of the array (instead of the form id)
						bits.unshift(form.name);
						_.set(formData, bits, field.value);
					});
				}
				var validateUrl = !(_.isNull(form.validationUrl) || _.isUndefined(form.validationUrl)) ? form.validationUrl : form.action;

				// Don't do the ajax call if the form has been told not to validate by ajax
				if (!form.enableAjaxValidation) {
					resolve(true);
					return;
				}
				$.ajax({
					url: validateUrl,
					type: 'POST',
					data: formData,
					success: function(messages) {
						_.each(messages, function(message, path) {
							// only include errors for fields we posted data for
							// the server currently returns all validation messages for all form fields
							// if we were only validating 2 out of 10 fields we only want to pay attention to the
							// error messages for the specific fields that we sent data for validation

							// because of: https://bitbucket.org/newicon/neon/commits/62ef27602a9a9f2802b409185a04cd1f7bfefe1a
							// Replace paths with their correct id path - the server will respond with something like the following:
							// {"formName.fieldName":["This cannot be blank."],"formName.fieldName2":[],"category.Save":[]}
							// as javascript must be able to contain multiple versions of the same form indexed by a unique form id
							var bits = path.split('.');
							// replace the first bit with the form.id instead of the form.name
							bits.splice(0,1, form.id);
							if (_.has(formData, path)) {
								if (message.length > 0) {
									commit('UPDATE_FIELD', {path: bits, updates: {errors: message}});
								}
							}
						});
						resolve(messages);
					},
					error: function(err) {
						console.error(err);
						reject(err);
					}
				});
			});
		},

		AJAX_SUBMIT_FORM: function ({ commit, state, getters, dispatch }, {path}) {
			return new Promise(function(resolve, reject) {
				var formData = {};
				var form;
				if (!_.isArray(path)) {
					// must be a form.
					// therefore....
					form = getters.getField(path);
					formData[form.name] = getters.getData(path);
				} else {
					// Not a form, can't submit
					return false;
				}
				var actionUrl = form.action;

				// Don't do the ajax call if the form has been told not to submit by ajax
				if (!form.enableAjaxSubmission) {
					resolve(true);
					return;
				}
				$.ajax({
					url: actionUrl,
					type: 'POST',
					data: formData,
					success: function(response) {
						resolve(response);
					},
					error: function(err) {
						console.error(err);
						reject(err);
					}
				});
			});
		},

		createFieldState: function ({ commit, state, getters, dispatch }, {path, props}) {

			if (!_.isUndefined(path) && path.includes('.') === false) {
				throw new Error('The field with path "'+path+'" has no parent form. Fields must be contained in a parent <neon-core-form-form> component');
			} else {
				var field = getters.getField(path);
				if (_.isUndefined(field)) {
					// get parent from path
					var parentPath = _.getParentPath(path);
					// what if the parent does not exist?
					if (_.isUndefined(getters.getField(parentPath))) {
						console.error('Parent does not exist - this error is unhandled - not sure what to do here - it should never happen');
						return;
					}
					commit('ADD_FIELD', {path: parentPath, definition: _.cloneDeep(props)});
				} else {
					commit('UPDATE_FIELD', {path: path, updates: props});
				}
			}
		}
	},
	mutations: {

		updateFieldName: function(state, payload) {
			var parentPath = payload.parentPath; // the current field
			var currentName = payload.currentName;
			var newName = payload.newName;
			var parent = _.getField(parentPath, state);
			//Vue.set(parent, newName, parent[currentName]);
			Vue.delete(parent, currentName);
		},

		/**
		 * Create the form state - sets up the state for this form in the store
		 * @param state
		 * @param payload
		 * @constructor
		 */
		CREATE: function(state, payload) {
			// we also want to store the initial state in order to reset a form??
			let formState = _.defaults(payload, {submitted:false});
			formState.reset = _.cloneDeep(formState);
			Vue.set(state, payload.id, Object.assign({ }, formState));
		},

		ADD_FIELD: function(state, { path, definition }) {
			let form = _.getField(path, state);
			Vue.set(form.fields, definition.name, definition)
		},

		/**
		 *
		 * @param state
		 * @param {String} objectPath a path to the field object
		 * @param {String} deleteKey the name / key in the object specified by objectPath to delete
		 * @constructor
		 */
		REMOVE_FIELD: function(state, { objectPath, deleteKey }) {
			let form = _.getField(objectPath, state);
			Vue.delete(form.fields, deleteKey);
		},


		CREATE_FIELD: function(state, {path, name, field}) {
			let form = _.getField(path, state);
			field.class = field.class || 'neon\\core\\form\\fields\\text';
			Vue.set(form['fields'], name, field);
		},

		UPDATE_FIELD: function (state, { path, updates=false }) {
			if (!_.isObject(updates)){
				throw 'Invalid update parameter: You must specify an object specifying the field properties and values to update: for e.g. {value: "my new value", label: "my new label"}';
			}
			let field = _.getField(path, state);
			if (_.isUndefined(field)) {
				console.warn('field with path "'+path+'" not found');
			} else{
				// add new keys as reactive properties - if they exist
				_.each(_.difference(_.keys(updates), _.keys(field)), function(prop){
					Vue.set(field, prop, updates[prop]);
				});
				// this is horrible code to deal specifically with the data duplication of required boolean switch and
				// adding the required validator object to a field - the two can potentially get out of sync.
				// the first thought is to make the switch a derived property  - but this has issues with it because
				// ultimately a required:true will exist in the definition.
				// To make it a derived property we must remove `required:true` from all definitions
				if (_.isDefined(updates.required) && field.required !== updates.required) {
					if (updates.required === false) {
						reqValidatorKey = _.findKey(field.validators, function(validator) {
							return validator.class === "neon\\core\\validators\\RequiredValidator";
						});
						Vue.delete(field.validators, reqValidatorKey);
					} else {
						Vue.set(field.validators, 'required', {"class": "neon\\core\\validators\\RequiredValidator"});
					}
				}
				Object.assign(field, updates);
			}
		},

		UPDATE_FORM: function(state, {id, updates}) {
			state[id] = Object.assign({}, state[id], updates);
		},

		RESET: function(state, {id}) {
			resetObj = state[id]['reset'];
			resetObj.reset = _.cloneDeep(resetObj);
			Vue.set(state, id, resetObj);
		}
	},
	getters: {

		FORM: (state) => (formName) => {
			return state[formName];
		},

		// DATA: (state) => (formName) => {
		// 	if (neon.form.forms && neon.form.forms[formName])
		// 		return neon.form.forms[formName].getData();
		// 	return '';
		// },

		FIELD: (state) => (path) => {
			return _.getField(path, state);
		},

		/**
		 * Get a field based on its path in the form
		 * @param {String|Array} path - the path specified by a dot-separated string or an array of keys
		 * @param state
		 */
		getField: (state) => (path) => {
			return _.getField(path, state);
		},

		/**
		 * Gets an executable function that will run the validator
		 * the validator function returned expects the value to be validated to be passed as its first and only parameter
		 * it will return an object of error messages or an empty object if validation passes.
		 * ```js
		 * let validator = getValidatorFunction(validatorDefinition)
		 * validator(valueToBeValidated) // => {}
		 * ```
		 * @param {Object} validatorDefinition
		 * @param {string} key - the key you want to index validation messages by
		 * @return Function(value) or false if no function
		 */
		getValidatorFunction: (state, getters) => (validatorDefinition) => {
			// get validator functions and options.
			// convert validator objects into js validator functions and associated options object
			const functionName = neon.form.validators[validatorDefinition.class];
			if (_.isUndefined(functionName)) {
				console.warn('No client validator exists for "' + validatorDefinition.class + '"', validatorDefinition);
				return false;
			}
			return functionName;
		},


		/**
		 * Get errors for a (form|field) by its string path
		 *
		 * @param  {String} path   - The string path to a single form or field for e.g. myform.myfield
		 * @param  {Object} errors [optional] - by reference will be populated with errors
		 * @return {Object} - error object populated with errors or empty object
		 */
		getErrors: (state, getters) => (path, errors) => {
			errors = errors || {};
			// get all errors in the form and its children - indexed by id.
			let field = getters.getField(path);
			if (_.isUndefined(field)) {
				console.warn('No field found with path of "' + path + '"');
				return false;
			}

			if (!_.isUndefined(field.fields)) {
				// must be a form.
				// therefore....
				_.each(field.fields, (field) => {
					// check the component has a name
					if (field.name)
						getters.getErrors(path + '.' + field.name, errors);
				});
			}

			// validate an individual field
			if (!_.isEmpty(field.errors)) {
				errors[path] = field.errors;
			}

			return errors;
		},

		hasErrors:  (state, getters) => (path) => {
			return !_.isEmpty(getters.getErrors(path));
		},

		/**
		 * Returns an object representing the forms data indexed by the forms name
		 * the object returned should look similar to the html data that will be posted
		 * @param state
		 * @param getters
		 */
		getData: (state, getters) => (path) => {
			let values = {};
			let field = getters.getField(path);
			if (_.isUndefined(field)) {
				return [];
			}
			// if is a form
			if (!_.isUndefined(field.fields)) {
				// if it is a form return an object of values
				_.each(field.fields, (field) => {
					values[field.name] = getters.getData(path+'.'+field.name);
				});
			} else {
				values = field.value;
			}
			return values;
		}

	}
});
