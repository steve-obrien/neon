/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./form sync recursive \\.vue$":
/*!***************************!*\
  !*** ./form/ sync \.vue$ ***!
  \***************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

var map = {
	"./fields/Password.vue": "./form/fields/Password.vue"
};


function webpackContext(req) {
	var id = webpackContextResolve(req);
	return __webpack_require__(id);
}
function webpackContextResolve(req) {
	if(!__webpack_require__.o(map, req)) {
		var e = new Error("Cannot find module '" + req + "'");
		e.code = 'MODULE_NOT_FOUND';
		throw e;
	}
	return map[req];
}
webpackContext.keys = function webpackContextKeys() {
	return Object.keys(map);
};
webpackContext.resolve = webpackContextResolve;
module.exports = webpackContext;
webpackContext.id = "./form sync recursive \\.vue$";

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./form/fields/Password.vue?vue&type=script&lang=js":
/*!**************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./form/fields/Password.vue?vue&type=script&lang=js ***!
  \**************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  name: 'neon-core-form-fields-password',
  "extends": Vue.component('neon-core-form-fields-field'),
  props: {
    value: String,
    checkPasswordDatabases: {
      type: Boolean,
      "default": true
    }
  },
  data: function data() {
    return {
      showPassword: false,
      hackedTimes: null,
      factor: 0
    };
  },
  watch: {
    'value': function value(val) {
      this.debouncedPasswordCrackCheck();
    }
  },
  created: function created() {
    var vm = this;
    this.debouncedPasswordCrackCheck = _.debounce(_.throttle(function () {
      return vm.passwordCrackCheck();
    }, 1500, {
      leading: true
    }), 200);
  },
  methods: {
    passwordCrackCheck: function passwordCrackCheck() {
      if (!this.checkPasswordDatabases) return;
      this.hackedTimes = 'loading';
      var vm = this;
      neon.hibpPasswordCheck(this.value).then(function (timesComprimised) {
        vm.hackedTimes = timesComprimised;
      });
    }
  },
  computed: {
    length: function length() {
      return this.value ? this.value.length : 0;
    },
    strength: function strength() {
      var length = this.length;
      var lower = /[a-z]/,
        upper = /[A-Z]/,
        number = /[0-9]/,
        special = /[^a-zA-Z0-9]/;
      this.factor = 0;
      if (lower.test(this.value)) this.factor += 26;
      if (upper.test(this.value)) this.factor += 26;
      if (number.test(this.value)) this.factor += 10;
      if (special.test(this.value)) this.factor += 30; // approximation but represents most common special characters
      var strength = Math.pow(this.factor, length);
      return strength ? strength : 0;
    },
    /**
     * This attempts to approximate how long it would take to crack a password
     * it does this by simply dividing the number of hashing algorithms a computer
     * can run per second by the strength of the password, where the strength is given by
     * the total number of combinations.
     */
    calculateTimeToCrack: function calculateTimeToCrack() {},
    strengthDetails: function strengthDetails() {
      var bad = 10e+8;
      var weak = 10e+10;
      var medium = 10e+14; // quintillion
      var high = 10e+21; // Sextillion
      var strong = 10e+24; // Septillion
      var veryStrong = 10e+30; // Nonillion
      var googol = 10e+100; // Googol - google :-)

      var percent = this.length * this.factor / 1200 * 100;
      percent = percent < 100 ? percent : 100;
      var hue = (percent / 100 * 120).toString(10);
      var color = ["hsl(", hue, ",100%,40%)"].join("");
      var label = function (strength) {
        if (strength <= bad) return 'Bad';
        if (strength < weak) return 'Bad';
        if (strength < medium) return 'Weak';
        if (strength < high) return 'Medium';
        if (strength < strong) return 'High';
        if (strength < veryStrong) return 'Strong';
        if (strength < googol) return 'Very Strong';
        if (strength > googol) return 'Googoliciously Strong!';
        return 'unknown';
      }(this.strength);
      return {
        label: label,
        percent: percent,
        color: color
      };
    },
    /**
     * Attempts to find the min password length from the validator
     * @returns {Number}
     */
    minLength: function minLength() {
      // find the string validator
      // - it cuts to root of how validators are managed which is a little messy
      var stringValidator = _.find(this.validators, function (v) {
        var c = v.component || v["class"];
        return c == 'neon\\core\\validators\\StringValidator' || c == 'string';
      });
      return _.get(stringValidator, 'min');
    }
  }
});

/***/ }),

/***/ "./form/fields/Password.vue":
/*!**********************************!*\
  !*** ./form/fields/Password.vue ***!
  \**********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Password_vue_vue_type_template_id_5d970b94__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Password.vue?vue&type=template&id=5d970b94 */ "./form/fields/Password.vue?vue&type=template&id=5d970b94");
/* harmony import */ var _Password_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Password.vue?vue&type=script&lang=js */ "./form/fields/Password.vue?vue&type=script&lang=js");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _Password_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"],
  _Password_vue_vue_type_template_id_5d970b94__WEBPACK_IMPORTED_MODULE_0__.render,
  _Password_vue_vue_type_template_id_5d970b94__WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "form/fields/Password.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./form/fields/Password.vue?vue&type=script&lang=js":
/*!**********************************************************!*\
  !*** ./form/fields/Password.vue?vue&type=script&lang=js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Password_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Password.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./form/fields/Password.vue?vue&type=script&lang=js");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Password_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./form/fields/Password.vue?vue&type=template&id=5d970b94":
/*!****************************************************************!*\
  !*** ./form/fields/Password.vue?vue&type=template&id=5d970b94 ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Password_vue_vue_type_template_id_5d970b94__WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   staticRenderFns: () => (/* reexport safe */ _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Password_vue_vue_type_template_id_5d970b94__WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Password_vue_vue_type_template_id_5d970b94__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../node_modules/vue-loader/lib/index.js??vue-loader-options!./Password.vue?vue&type=template&id=5d970b94 */ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./form/fields/Password.vue?vue&type=template&id=5d970b94");


/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./form/fields/Password.vue?vue&type=template&id=5d970b94":
/*!*******************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./form/fields/Password.vue?vue&type=template&id=5d970b94 ***!
  \*******************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render),
/* harmony export */   staticRenderFns: () => (/* binding */ staticRenderFns)
/* harmony export */ });
var render = function () {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return !_vm.isPrintOnly
    ? _c(
        "neon-core-form-field",
        _vm._b(
          { staticClass: "neonFieldPassword" },
          "neon-core-form-field",
          _vm.fieldProps,
          false
        ),
        [
          _c("div", { staticClass: "has-feedback" }, [
            _c(
              "span",
              {
                staticClass: "form-control-feedback",
                staticStyle: {
                  "pointer-events": "all",
                  cursor: "pointer",
                  top: "50%",
                  transform: "translateY(-50%)",
                },
                attrs: { title: "Toggle password visibility" },
                on: {
                  click: function ($event) {
                    _vm.showPassword = !_vm.showPassword
                  },
                },
              },
              [
                _c("i", {
                  staticClass: "fa",
                  class: _vm.showPassword ? "fa-eye" : "fa-eye-slash",
                }),
              ]
            ),
            _vm._v(" "),
            (_vm.showPassword ? "text" : "password") === "checkbox"
              ? _c(
                  "input",
                  _vm._b(
                    {
                      directives: [
                        {
                          name: "model",
                          rawName: "v-model",
                          value: _vm.modelValue,
                          expression: "modelValue",
                        },
                      ],
                      staticClass: "form-control",
                      attrs: {
                        name: _vm.inputName,
                        id: _vm.id,
                        placeholder: _vm.placeholder,
                        readonly: _vm.isReadOnly,
                        type: "checkbox",
                      },
                      domProps: {
                        checked: Array.isArray(_vm.modelValue)
                          ? _vm._i(_vm.modelValue, null) > -1
                          : _vm.modelValue,
                      },
                      on: {
                        blur: function ($event) {
                          return _vm.onBlur()
                        },
                        focus: function ($event) {
                          return _vm.onFocus()
                        },
                        change: function ($event) {
                          var $$a = _vm.modelValue,
                            $$el = $event.target,
                            $$c = $$el.checked ? true : false
                          if (Array.isArray($$a)) {
                            var $$v = null,
                              $$i = _vm._i($$a, $$v)
                            if ($$el.checked) {
                              $$i < 0 && (_vm.modelValue = $$a.concat([$$v]))
                            } else {
                              $$i > -1 &&
                                (_vm.modelValue = $$a
                                  .slice(0, $$i)
                                  .concat($$a.slice($$i + 1)))
                            }
                          } else {
                            _vm.modelValue = $$c
                          }
                        },
                      },
                    },
                    "input",
                    _vm.attributes,
                    false
                  )
                )
              : (_vm.showPassword ? "text" : "password") === "radio"
              ? _c(
                  "input",
                  _vm._b(
                    {
                      directives: [
                        {
                          name: "model",
                          rawName: "v-model",
                          value: _vm.modelValue,
                          expression: "modelValue",
                        },
                      ],
                      staticClass: "form-control",
                      attrs: {
                        name: _vm.inputName,
                        id: _vm.id,
                        placeholder: _vm.placeholder,
                        readonly: _vm.isReadOnly,
                        type: "radio",
                      },
                      domProps: { checked: _vm._q(_vm.modelValue, null) },
                      on: {
                        blur: function ($event) {
                          return _vm.onBlur()
                        },
                        focus: function ($event) {
                          return _vm.onFocus()
                        },
                        change: function ($event) {
                          _vm.modelValue = null
                        },
                      },
                    },
                    "input",
                    _vm.attributes,
                    false
                  )
                )
              : _c(
                  "input",
                  _vm._b(
                    {
                      directives: [
                        {
                          name: "model",
                          rawName: "v-model",
                          value: _vm.modelValue,
                          expression: "modelValue",
                        },
                      ],
                      staticClass: "form-control",
                      attrs: {
                        name: _vm.inputName,
                        id: _vm.id,
                        placeholder: _vm.placeholder,
                        readonly: _vm.isReadOnly,
                        type: _vm.showPassword ? "text" : "password",
                      },
                      domProps: { value: _vm.modelValue },
                      on: {
                        blur: function ($event) {
                          return _vm.onBlur()
                        },
                        focus: function ($event) {
                          return _vm.onFocus()
                        },
                        input: function ($event) {
                          if ($event.target.composing) {
                            return
                          }
                          _vm.modelValue = $event.target.value
                        },
                      },
                    },
                    "input",
                    _vm.attributes,
                    false
                  )
                ),
          ]),
          _vm._v(" "),
          _c("el-progress", {
            staticStyle: { margin: "0px 1px 5px 1px" },
            attrs: {
              percentage: _vm.strengthDetails.percent,
              color: _vm.strengthDetails.color,
              "show-text": false,
            },
          }),
          _vm._v(" "),
          _c(
            "div",
            {
              directives: [
                {
                  name: "show",
                  rawName: "v-show",
                  value: _vm.flags.touched && _vm.flags.dirty,
                  expression: "flags.touched && flags.dirty",
                },
              ],
              staticClass: "hint-block",
            },
            [
              _vm._v(
                _vm._s(
                  _vm.length
                    ? "Password strength: " + _vm.strengthDetails.label
                    : ""
                )
              ),
            ]
          ),
          _vm._v(" "),
          _vm.minLength
            ? _c(
                "div",
                {
                  directives: [
                    {
                      name: "show",
                      rawName: "v-show",
                      value: _vm.flags.touched && _vm.flags.dirty,
                      expression: "flags.touched && flags.dirty",
                    },
                  ],
                  staticClass: "hint-block",
                },
                [
                  _vm._v("Your password must be at least "),
                  _c("strong", [_vm._v(_vm._s(_vm.minLength))]),
                  _vm._v(
                    " characters long. It is currently " +
                      _vm._s(_vm.length) +
                      " characters."
                  ),
                ]
              )
            : _vm._e(),
          _vm._v(" "),
          _vm.hackedTimes != null && _vm.checkPasswordDatabases
            ? _c(
                "div",
                {
                  directives: [
                    {
                      name: "show",
                      rawName: "v-show",
                      value: _vm.flags.touched && _vm.flags.dirty,
                      expression: "flags.touched && flags.dirty",
                    },
                  ],
                  staticClass: "hint-block",
                },
                [
                  _vm.hackedTimes == "loading"
                    ? _c("div", [
                        _c("i", {
                          staticClass: " neonSpinner el-icon-loading",
                        }),
                        _vm._v(" Checking..."),
                      ])
                    : _vm._e(),
                  _vm._v(" "),
                  _vm.hackedTimes == 0 && _vm.length > 0
                    ? _c("div", { staticClass: " " }, [
                        _c("i", { staticClass: "fa fa-check" }),
                        _vm._v(" This password has not been compromised."),
                      ])
                    : _vm._e(),
                  _vm._v(" "),
                  _vm.hackedTimes > 0
                    ? _c("div", {}, [
                        _c("i", { staticClass: "fa fa-exclamation" }),
                        _vm._v(" Oh no! This password was found "),
                        _c("strong", [_vm._v(_vm._s(_vm.hackedTimes))]),
                        _vm._v(" times in compromised passwords databases!"),
                      ])
                    : _vm._e(),
                ]
              )
            : _vm._e(),
        ],
        1
      )
    : _vm._e()
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js":
/*!********************************************************************!*\
  !*** ./node_modules/vue-loader/lib/runtime/componentNormalizer.js ***!
  \********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ normalizeComponent)
/* harmony export */ });
/* globals __VUE_SSR_CONTEXT__ */

// IMPORTANT: Do NOT use ES2015 features in this file (except for modules).
// This module is a runtime utility for cleaner component module output and will
// be included in the final webpack user bundle.

function normalizeComponent(
  scriptExports,
  render,
  staticRenderFns,
  functionalTemplate,
  injectStyles,
  scopeId,
  moduleIdentifier /* server only */,
  shadowMode /* vue-cli only */
) {
  // Vue.extend constructor export interop
  var options =
    typeof scriptExports === 'function' ? scriptExports.options : scriptExports

  // render functions
  if (render) {
    options.render = render
    options.staticRenderFns = staticRenderFns
    options._compiled = true
  }

  // functional template
  if (functionalTemplate) {
    options.functional = true
  }

  // scopedId
  if (scopeId) {
    options._scopeId = 'data-v-' + scopeId
  }

  var hook
  if (moduleIdentifier) {
    // server build
    hook = function (context) {
      // 2.3 injection
      context =
        context || // cached call
        (this.$vnode && this.$vnode.ssrContext) || // stateful
        (this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext) // functional
      // 2.2 with runInNewContext: true
      if (!context && typeof __VUE_SSR_CONTEXT__ !== 'undefined') {
        context = __VUE_SSR_CONTEXT__
      }
      // inject component styles
      if (injectStyles) {
        injectStyles.call(this, context)
      }
      // register component module identifier for async chunk inferrence
      if (context && context._registeredComponents) {
        context._registeredComponents.add(moduleIdentifier)
      }
    }
    // used by ssr in case component is cached and beforeCreate
    // never gets called
    options._ssrRegister = hook
  } else if (injectStyles) {
    hook = shadowMode
      ? function () {
          injectStyles.call(
            this,
            (options.functional ? this.parent : this).$root.$options.shadowRoot
          )
        }
      : injectStyles
  }

  if (hook) {
    if (options.functional) {
      // for template-only hot-reload because in that case the render fn doesn't
      // go through the normalizer
      options._injectStyles = hook
      // register for functional component in vue file
      var originalRender = options.render
      options.render = function renderWithStyleInjection(h, context) {
        hook.call(context)
        return originalRender(h, context)
      }
    } else {
      // inject component registration as beforeCreate hook
      var existing = options.beforeCreate
      options.beforeCreate = existing ? [].concat(existing, hook) : [hook]
    }
  }

  return {
    exports: scriptExports,
    options: options
  }
}


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!*****************!*\
  !*** ./core.js ***!
  \*****************/
var fieldsContext = __webpack_require__("./form sync recursive \\.vue$");
fieldsContext.keys().forEach(function (fileName) {
  // Get the component config
  var componentConfig = fieldsContext(fileName);
  var component = componentConfig["default"] || componentConfig;
  alert(component.name);
  // Get the PascalCase version of the component name
  // Register the component globally
  Vue.component(component.name,
  // Look for the component options on `.default`, which will exist if the component is
  // exported with `export default`, otherwise fall back to module's root.
  componentConfig["default"] || componentConfig);
});
})();

/******/ })()
;