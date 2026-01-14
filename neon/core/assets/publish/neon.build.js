"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

;
/**
 * Register the global neon object
 * // note: previously used const neon -
 * // There is a strange bug in webkit where having a const variable declaration and a dom id selector with the same name breaks!
 * @type Object
 */

window.neon = window.neon || {};
/**
 * Log an error
 * @param error
 * @param context
 */

neon.error = function (error, context) {
  if (typeof console !== 'undefined') {
    console.error(error, context);
  } else {
    throw error;
  }
};
/**
 * Whether we are in debug mode
 * @returns {boolean}
 */


neon.isDebug = function () {
  return NEON_DATA.debug ? true : false;
};
/**
 * Alert the user with a message
 * @param string type
 * @param string message
 */


neon.alert = function (type, message) {
  // TODO - 20180427 - make these do something more interesting than alerts
  switch (type) {
    case 'error':
      alert(message);
      break;

    case 'success':
      alert(message);
      break;
  }
};
/**
 * Get base url
 * @returns string
 */


neon.base = function (host) {
  return host ? NEON_DATA.host : NEON_DATA.base;
};
/**
 * Create a correct url
 * Useage: `neon.url('my/endpoint', {myGetParam:'myGetValue'})`
 *
 * @param {string} url
 * @param {object} params
 */


neon.url = function (url, params, host) {
  host = host || false;
  url = url[0] === '/' ? url.substr(1) : url; // TODO remove jquery dependancy $.param

  var queryPrefix = url.match(/\?/) !== null ? '&' : '?';
  var query = params ? queryPrefix + $.param(params) : '';
  return neon.base(host) + '/' + url + query;
};
/**
 * Get the csrf param
 *
 * @returns {String}
 */


neon.csrfParam = function () {
  return NEON_DATA.csrfParam;
};
/**
 * Get the csrf token
 *
 * @returns String
 */


neon.csrfToken = function () {
  // $('meta[name="csrf-token"]').attr('content')
  return NEON_DATA.csrfToken;
};
/**
 * Show a message to flash to the user
 * Types include: error | success | info
 * ~~~js
 * neon.flash('Hey here is a success message', 'success', 10000)
 * ~~~
 * @param {string} message - The message body content.
 * @param {string} type - 'error' | 'success' | 'info' | 'warning'
 * @param {int} duration - The number of milliseconds to show the notification for - if 0 show indefinately
 */


neon.flash = function (message) {
  var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'info';
  var duration = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 5000;
  var dangerouslyUseHtml = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : false;
  neon.app.$message({
    message: message,
    type: type,
    duration: duration,
    showClose: true,
    dangerouslyUseHTMLString: dangerouslyUseHtml
  });
};
/**
 * Show a message to flash to the user
 * Types include: error | success | info
 * ~~~js
 * neon.notify('Saved', 'Hey here is a success message - the thing saved!', 'success')
 * ~~~
 * @param {string} title - The title of the notification - shorter text shown in a bolder style
 * @param {string} message - The message body content.
 * @param {string} type - 'error' | 'success' | 'info' | 'warning'
 * @param {int} duration - The number of milliseconds to show the notification for
 */


neon.notify = function (title, message) {
  var type = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'info';
  var duration = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 5000;
  neon.app.$notify({
    title: title,
    message: message,
    type: type,
    duration: duration
  });
};
/**
 * Take a class path and format into a component string
 * For e.g. `neon\\core\\form\\fields\\Text` becomes `neon-core-form-fields-text`
 * @param field
 * @returns {string}
 */


neon.classToComponent = function (className) {
  return className.replace(/\\/g, '-').replace(/^-/, '') // replace '\' with '-' and remove any leading '-'
  .toLowerCase();
};
/**
 * Implements sha1 hashing function
 * @param string
 * @returns {PromiseLike<string | never>}
 * @example neon.sha1(pwd).then(function(hash){ ... })
 */


neon.sha1 = function sha1(string) {
  var buffer = new TextEncoder("utf-8").encode(string);
  return crypto.subtle.digest("SHA-1", buffer).then(function (buffer) {
    // Get the hex code
    var hexCodes = [];
    var view = new DataView(buffer);

    for (var i = 0; i < view.byteLength; i += 4) {
      // Using getUint32 reduces the number of iterations needed (we process 4 bytes each time)
      var value = view.getUint32(i); // toString(16) will give the hex representation of the number without padding

      var stringValue = value.toString(16); // We use concatenation and slice for padding

      var padding = '00000000';
      var paddedValue = (padding + stringValue).slice(-padding.length);
      hexCodes.push(paddedValue);
    } // Join all the hex strings into one


    return hexCodes.join("");
  });
};
/**
 * Have I Been Pwned https://haveibeenpwned.com/ password checker.
 * Checks if a password has previously been compromised
 * this uses the https://haveibeenpwned.com/API/v2#PwnedPasswords API.
 * The function returns a promise that will return the number of times the password has appeared
 * in compromised passwords databases.
 * Only the first 5 characters of a sha1 hash of the password are ever sent to the service
 * keeping it anonymous, this is a technique called k-Anonymity.
 *
 * @param {String} password
 * @returns {Promise<Number>}
 * @example
 * neon.hibpPasswordCheck().then(function(result) {
 *     console.log(`This password was found ${result} times in compromised passwords databases!`);
 * })
 * @see neon.sha1 - depends on neon.sha1
 */


neon.hibpPasswordCheck = function (password) {
  var promise = new Promise(function (resolve, reject) {
    // We hash the pwd first
    neon.sha1(password).then(function (hash) {
      // We send the first 5 chars of the hash to hibp's API
      var req = new XMLHttpRequest();
      req.addEventListener("load", function () {
        // When we get back a response from the server
        // We create an array of lines and loop through them
        var resp = this.responseText.split('\n');
        var hashSub = hash.slice(5).toUpperCase();

        for (var index in resp) {
          // Check if the line matches the rest of the hash
          if (resp[index].substring(0, 35) == hashSub) {
            return resolve(resp[index].split(':')[1]);
          }
        } // Trigger an event with the result
        //const event = new CustomEvent('hibpCheck', {detail: result});
        //document.dispatchEvent(event);


        resolve(0);
      });
      req.open('GET', 'https://api.pwnedpasswords.com/range/' + hash.substr(0, 5));
      req.send();
    });
  });
  return promise;
};
/**
 * Returns a function that evaluates statements for various operators.
 * This can be used to evaluate filters defined in JSON
 * @example
 * neon.filters('==')(1, 2); //evaluates as false
 * neon.filters('<')(1, 2); //evaluates as true
 * @param operator {String} The operator to use in the evaluation.
 * @returns Function (x, y)
 */


neon.filter = function (operator) {
  var f = {
    '=': function _(x, y) {
      return x == y;
    },
    '>': function _(x, y) {
      return x > y;
    },
    '<': function _(x, y) {
      return x < y;
    },
    '>=': function _(x, y) {
      return x >= y;
    },
    '<=': function _(x, y) {
      return x <= y;
    },
    '==': function _(x, y) {
      return x == y;
    },
    '!=': function _(x, y) {
      return x != y;
    },
    '===': function _(x, y) {
      return x === y;
    },
    '!==': function _(x, y) {
      return x !== y;
    },
    'empty': function empty(x) {
      return x === undefined || x === null || x === '';
    },
    '!empty': function empty(x) {
      return x !== undefined && x !== null && x !== '';
    }
  };

  if (!(operator in f)) {
    throw 'Not a valid operator.';
  }

  return f[operator];
};
/**
 * Generate a uuid
 * @param s
 * @returns {string}
 */


neon.uuid = function () {
  var s = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '-';
  var format = 'XXXXXXXX' + s + 'XXXX' + s + 'XXXX' + s + 'XXXX' + s + 'XXXXXXXXXXXX';
  return format.replace(/[X]/g, function (c) {
    return (Math.floor(Math.random() * 16) + 1).toString(16);
  });
};
/**
 * Generate a uuid64
 * @returns {string}
 */


neon.uuid64 = function () {
  // remove any hyphens
  var uuid = neon.uuid('');
  uuid += '0'; // 33 hexadecimal characters = 22 64-cimal chars.
  // to convert from base-16 to base-64 (not Base64)
  // 64 characters

  var letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  var encoding = '0123456789' + letters + '_Z';
  var uuid64 = '';
  var position = 0;

  while (position < 33) {
    var snip = uuid.substr(position, 3);
    var number = parseInt(snip, 16);
    uuid64 += encoding[Math.floor(number / 64)] + encoding[Math.floor(number % 64)];
    position += 3;
  }

  return uuid64;
};
/**
 * Flattens an object - this is useful to create string address paths to complex nested structures
 * You can use `_.get()` to address complex nested structures in this way
 * @example
 * neon.flattenObject({ 'a':{ 'b':{ 'b2':2 }, 'c':{ 'c2':2, 'c3':3 } } });
 * // gives => { 'a.b.b2':2, 'a.c.c2':2, 'a.c.c3':3 }
 * @param {Object} ob - the object to flatten
 * @return {Object} - an object of {}key:value} where the key is a string representing the structure
 */


neon.flattenObject = function (ob) {
  var toReturn = {};
  var flatObject;

  for (var i in ob) {
    if (!ob.hasOwnProperty(i)) {
      continue;
    }

    if (_typeof(ob[i]) === 'object') {
      flatObject = neon.flattenObject(ob[i]);

      for (var x in flatObject) {
        if (!flatObject.hasOwnProperty(x)) {
          continue;
        }

        toReturn[i + (!!isNaN(x) ? '.' + x : '')] = flatObject[x];
      }
    } else {
      toReturn[i] = ob[i];
    }
  }

  return toReturn;
};
/**
 * Capitalize the first character of a string
 *
 * @param {string} stringName
 * @returns {string}
 */


neon.capitalize = function (stringName) {
  return stringName[0].toUpperCase() + stringName.substring(1);
};
/**
 * Format a number into a string representing bytes
 * @example
 * > neon.formatBytes(3452345253)
 * < "3.45 GB"
 * @param {Number} size
 * @returns {string}
 */


neon.formatBytes = function (size) {
  var e = Math.log(size) / Math.log(1e3) | 0;
  return +(size / Math.pow(1e3, e)).toFixed(2) + ' ' + ('kMGTPEZY'[e - 1] || '') + 'B';
};
/**
 * Add underscore extensions
 */


neon._ = {
  /**
   * A simple inversion of _.isUndefined - can make complex statements easier to read
   * @param {*} reference - The value to test
   * @returns {boolean}
   */
  isDefined: function isDefined(reference) {
    return !_.isUndefined(reference);
  },

  /**
   * Validate an object
   *
   * @param {Object} object - The object containing properties to validate
   * @param {string} context - A message to help give context to the error message if validation fails
   * @param {Object} definition - The validation definition
   * @param {string} definition.type - The data type to test against e.g. String, Object, Array
   * @param {boolean} definition.required - Whether the item is required
   */
  validate: function validate(object, context, definition) {
    _.each(definition, function (prop, name) {
      // check the property exists if it is required
      if (_.isUndefined(object[name])) {
        if (prop.required) _.error('Missing required property: "' + name + '"', context);
        return;
      } // check the type


      var validType = _.assertType(object[name], prop.type);

      if (!validType.valid) {
        _.error('Invalid property: type check failed for property "' + name + '".' + ' Expected ' + validType.expectedType + ', got ' + Object.prototype.toString.call(object[name]).slice(8, -1) + '.', context);
      }
    });
  },

  /**
   * Assert that a value is of type
   *
   * ```js
   * _.assertType('is string', String); // => true
   * ```
   *
   * @param {*} value - The value to test
   * @param {*} type - The type to assert e.g. String | Number | Array
   * @returns {{valid: boolean, expectedType: *}}
   */
  assertType: function assertType(value, type) {
    var simpleCheckRE = /^(String|Number|Boolean|Function|Symbol)$/;
    var valid;

    var expectedType = _.getType(type);

    if (simpleCheckRE.test(expectedType)) {
      valid = _typeof(value) === expectedType.toLowerCase();
    } else if (expectedType === 'Object') {
      valid = _.isObject(value);
    } else if (expectedType === 'Array') {
      valid = _.isArray(value);
    } else {
      valid = value instanceof type;
    }

    return {
      valid: valid,
      expectedType: expectedType
    };
  },

  /**
   * Use the string name of the data type function check built-in types,
   * because a simple equality check will fail when running
   * across different vms / iframes.
   * @param {*} fn - The data type function e.g. String | Number | Array | Object etc
   * @returns {string} the data type string
   */
  getType: function getType(fn) {
    var match = fn && fn.toString().match(/^\s*function (\w+)/);
    return match ? match[1] : '';
  },
  error: neon.error,
  capitalize: neon.capitalize,
  formatBytes: neon.formatBytes,
  uuid: neon.uuid,
  flattenObject: neon.flattenObject
};
/**
 * Add neon "_" helpers to the underscore / lodash library if it exists
 * Thus functions become accessible via "_."
 */

if (typeof _ != "undefined") {
  _.mixin(neon._);
}
/**
 * Create a new global Vuex Store
 */


neon.Store = new Vuex.Store({
  strict: NEON_DATA.debug,
  state: {
    modals: []
  },
  getters: {
    /**
     * Whether a modal should be displayed
     *
     * @return boolean
     */
    getShowModal: function getShowModal(state, getters) {
      return function () {
        // do we have any modals to show on the stack
        return state.modals.length !== 0;
      };
    },

    /**
     * Get the string name of the component that the modal should render
     *
     * @return {string} The component name
     */
    getModalComponent: function getModalComponent(state, getters) {
      return function () {
        var last = _.last(state.modals);

        if (_.isUndefined(last)) {
          return '';
        }

        return last.component;
      };
    }
  },
  mutations: {
    /**
     * @param {Object} state
     * @param {Object} payload
     * @param {String} payload.form the form id
     * @param {Object} payload.fields the form data object where key is the field name and the value is the field value
     */
    CREATE_FORM: function CREATE_FORM(state, payload) {
      Vue.set(state.forms, payload.form, payload.fields);
    },

    /**
     * Show a modal
     * @param state
     * @param {Object} payload
     * @param {Object} payload.component - The component to show in the model e.g. 'firefly-picker-modal'
     * the component specified should be wrapped by 'neon-modal-wrap' component
     * @param {Object} payload.props - The properties to pass to the component
     */
    SHOW_MODAL: function SHOW_MODAL(state, payload) {
      _.validate(payload, 'in neon.Store§_MODAL', {
        component: {
          type: String,
          required: true
        },
        props: {
          type: Object,
          required: false
        }
      });

      state.modals.push({
        component: {
          name: payload.component,
          props: payload.props
        }
      });
    },

    /**
     * Close the current active modal
     *
     * @param state
     * @constructor
     */
    CLOSE_MODAL: function CLOSE_MODAL(state) {
      // pop the last modal off the stack
      state.modals.pop();
    }
  }
});
/**
 * This needs to be the last bit of script to run
 */

neon.app = new Vue({
  store: neon.Store,
  computed: Vuex.mapGetters(['getShowModal', 'getModalComponent']),
  methods: Vuex.mapMutations([{
    closeModal: 'CLOSE_MODAL'
  }])
});

if (jQuery) {
  $.ajaxSetup({
    headers: {
      'X-CSRF-Token': neon.csrfToken()
    }
  });
}
/**
 * Mount a vue object - factory for creating new mountable vue instances
 * @param {string} selector
 * @return Vue
 */


neon.mount = function (selector) {
  return new Vue({
    store: neon.Store
  }).$mount(selector);
};
/**
 * Set up Vue
 */


if (Vue) {
  Vue.config.performance = neon.isDebug;
}