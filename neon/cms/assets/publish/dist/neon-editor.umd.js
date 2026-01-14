(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("vue"));
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["neon-editor"] = factory(require("vue"));
	else
		root["neon-editor"] = factory(root["Vue"]);
})((typeof self !== 'undefined' ? self : this), function(__WEBPACK_EXTERNAL_MODULE__8bbf__) {
return /******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "fae3");
/******/ })
/************************************************************************/
/******/ ({

/***/ "24d4":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"3c8b867e-vue-loader-template"}!./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/babel-loader/lib!./node_modules/vue-loader/lib/loaders/templateLoader.js??ref--6!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/Editor.vue?vue&type=template&id=297d18db
var render = function render() {
  var _vm = this,
    _c = _vm._self._c;
  return _c('div', [_c('NeonCmpList', {
    ref: "html",
    attrs: {
      "render": _vm.rootComponent
    }
  }), _vm.editMode ? _c('div', {
    staticClass: "niEditor_toolbar"
  }, [_vm._v("Toolbar")]) : _vm._e()], 1);
};
var staticRenderFns = [];

// CONCATENATED MODULE: ./src/Editor.vue?vue&type=template&id=297d18db

// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");
var external_commonjs_vue_commonjs2_vue_root_Vue_default = /*#__PURE__*/__webpack_require__.n(external_commonjs_vue_commonjs2_vue_root_Vue_);

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"3c8b867e-vue-loader-template"}!./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/babel-loader/lib!./node_modules/vue-loader/lib/loaders/templateLoader.js??ref--6!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/NeonCmpList.vue?vue&type=template&id=aa425d4e
var NeonCmpListvue_type_template_id_aa425d4e_render = function render() {
  var _vm = this,
    _c = _vm._self._c;
  return _c('div', [_vm._l(_vm.childComponents, function (uuid) {
    return _vm.exists(uuid) ? _c('NeonCmp', {
      key: uuid,
      attrs: {
        "render": uuid
      }
    }) : _vm._e();
  }), _c('NeonAdder', {
    attrs: {
      "uuid": _vm.render
    }
  })], 2);
};
var NeonCmpListvue_type_template_id_aa425d4e_staticRenderFns = [];

// CONCATENATED MODULE: ./src/NeonCmpList.vue?vue&type=template&id=aa425d4e

// EXTERNAL MODULE: ./src/store.js
var store = __webpack_require__("c0d6");

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/NeonCmpList.vue?vue&type=script&lang=js
//import _ from 'lodash';



/**
 * A NeonCmpList is a component that allows a list NeonCmp's inside
 * This component enables nesting and creating component trees
 */
/* harmony default export */ var NeonCmpListvue_type_script_lang_js = ({
  name: "NeonCmpList",
  comments: true,
  inject: {
    'editor': {
      default: false
    }
  },
  props: {
    render: ''
  },
  data() {
    return {
      componentMenu: false
    };
  },
  // components
  computed: {
    isEditMode() {
      return this.$store.getters[store["a" /* GETTERS */].isEditMode];
    },
    childComponents() {
      return this.$store.getters[store["a" /* GETTERS */].getChildren](this.render);
    }
  },
  methods: {
    exists(uuid) {
      var exists = !!this.$store.getters[store["a" /* GETTERS */].components][uuid];
      if (!exists) {
        // remove child
        this.$store.commit('editor/removeChild', {
          parent: this.render,
          child: uuid
        });
        return false;
      }
      return true;
    }
  }
});
// CONCATENATED MODULE: ./src/NeonCmpList.vue?vue&type=script&lang=js
 /* harmony default export */ var src_NeonCmpListvue_type_script_lang_js = (NeonCmpListvue_type_script_lang_js); 
// EXTERNAL MODULE: ./src/NeonCmpList.vue?vue&type=style&index=0&id=aa425d4e&prod&lang=css
var NeonCmpListvue_type_style_index_0_id_aa425d4e_prod_lang_css = __webpack_require__("64b6");

// EXTERNAL MODULE: ./node_modules/vue-loader/lib/runtime/componentNormalizer.js
var componentNormalizer = __webpack_require__("2877");

// CONCATENATED MODULE: ./src/NeonCmpList.vue






/* normalize component */

var component = Object(componentNormalizer["a" /* default */])(
  src_NeonCmpListvue_type_script_lang_js,
  NeonCmpListvue_type_template_id_aa425d4e_render,
  NeonCmpListvue_type_template_id_aa425d4e_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var NeonCmpList = (component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"3c8b867e-vue-loader-template"}!./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/babel-loader/lib!./node_modules/vue-loader/lib/loaders/templateLoader.js??ref--6!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/NeonCmp.vue?vue&type=template&id=2e4cc226
var NeonCmpvue_type_template_id_2e4cc226_render = function render() {
  var _vm = this,
    _c = _vm._self._c;
  return _c(_vm.componentIs, _vm._b({
    tag: "component",
    class: {
      'niEditor_selected': _vm.selected,
      'niEditor_hovering': _vm.hovering
    },
    attrs: {
      "uuid": _vm.render,
      "data-cmp": _vm.component.cmp,
      "data-uuid": _vm.render
    },
    nativeOn: {
      "click": function ($event) {
        if ($event.ctrlKey || $event.shiftKey || $event.altKey || $event.metaKey) return null;
        return _vm.select($event);
      },
      "mouseover": function ($event) {
        return _vm.onMouseOver($event);
      }
    }
  }, 'component', _vm.component.props, false));
};
var NeonCmpvue_type_template_id_2e4cc226_staticRenderFns = [];

// CONCATENATED MODULE: ./src/NeonCmp.vue?vue&type=template&id=2e4cc226

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/NeonCmp.vue?vue&type=script&lang=js


/* harmony default export */ var NeonCmpvue_type_script_lang_js = ({
  name: "NeonCmp",
  comments: true,
  inject: ['editor'],
  props: {
    // the uuid of the component to render
    render: {
      type: String
    },
    // attemp to create a mapping for props to a data source for example
    mapProps: {
      type: Object,
      default: function () {
        return {};
      }
    }
  },
  computed: {
    componentIs: function () {
      console.log(this.component);
      var cmp = this.component;
      // const unknown = {
      // 	data: function() { return {cmp: cmp} },
      // 	template: `<div>Unknown component - {{cmp.cmp}} props: <pre>{{cmp.props}}</pre></div>`
      // };
      const unknown = {
        data: function () {
          return {
            cmp: cmp
          };
        },
        template: `<NeonServerSideRender :tag="cmp.cmp"></NeonServerSideRender>`
      };

      // normal operation
      if (external_commonjs_vue_commonjs2_vue_root_Vue_default.a.options.components[this.component.cmp]) {
        return this.component.cmp;
      }
      return unknown;
    },
    selected: function () {
      return this.$store.getters[store["a" /* GETTERS */].isSelected](this.render);
    },
    hovering: function () {
      return this.$store.getters[store["a" /* GETTERS */].isHovering](this.render);
    },
    component() {
      var componentProps = this.$store.getters[store["a" /* GETTERS */].getComponent](this.render);
      _.forEach(this.mapProps, (value, key) => {
        componentProps[key] = value;
      });
      return componentProps;
    }
  },
  methods: {
    select: function ($event) {
      $event.stopPropagation();
      this.$store.commit('editor/select', {
        uuid: this.render
      });
    },
    onMouseOver: function ($event) {
      $event.stopPropagation();
      if (this.editor.dragging) return;
      this.$store.commit('editor/hover', {
        uuid: this.render
      });
    }
  }
});
// CONCATENATED MODULE: ./src/NeonCmp.vue?vue&type=script&lang=js
 /* harmony default export */ var src_NeonCmpvue_type_script_lang_js = (NeonCmpvue_type_script_lang_js); 
// EXTERNAL MODULE: ./src/NeonCmp.vue?vue&type=style&index=0&id=2e4cc226&prod&lang=css
var NeonCmpvue_type_style_index_0_id_2e4cc226_prod_lang_css = __webpack_require__("8cce");

// CONCATENATED MODULE: ./src/NeonCmp.vue






/* normalize component */

var NeonCmp_component = Object(componentNormalizer["a" /* default */])(
  src_NeonCmpvue_type_script_lang_js,
  NeonCmpvue_type_template_id_2e4cc226_render,
  NeonCmpvue_type_template_id_2e4cc226_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var NeonCmp = (NeonCmp_component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"3c8b867e-vue-loader-template"}!./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/babel-loader/lib!./node_modules/vue-loader/lib/loaders/templateLoader.js??ref--6!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/NeonCard.vue?vue&type=template&id=e7fa22fc
var NeonCardvue_type_template_id_e7fa22fc_render = function render() {
  var _vm = this,
    _c = _vm._self._c;
  return _c('div', {
    staticClass: "card cardBackground",
    attrs: {
      "data-animation": "zooming"
    }
  }, [_c('div', {
    staticClass: "card_background",
    style: _vm.overlayStyle
  }), _c('a', {
    staticClass: "card_body",
    attrs: {
      "href": "javascript:;"
    }
  }, [_c('div', {
    staticClass: "content_foot"
  }, [_c('h6', {
    staticClass: "text-white opacity-8"
  }, [_vm._v(_vm._s(_vm.subTitle) + " - " + _vm._s(_vm.uuid))]), _c('h5', {
    staticClass: "card_title"
  }, [_vm._v(_vm._s(_vm.title) + " selected " + _vm._s(_vm.selected) + " - hovering " + _vm._s(_vm.hovering) + " ")])])])]);
};
var NeonCardvue_type_template_id_e7fa22fc_staticRenderFns = [];

// CONCATENATED MODULE: ./src/components/NeonCard.vue?vue&type=template&id=e7fa22fc

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/NeonCard.vue?vue&type=script&lang=js

/* harmony default export */ var NeonCardvue_type_script_lang_js = ({
  name: "NeonCard",
  comments: true,
  props: {
    uuid: {
      type: String
    },
    title: {
      type: String,
      default: 'Some title'
    },
    subTitle: {
      type: String,
      default: 'Some subtitle'
    },
    imgSrc: {
      type: String,
      default: 'https://images.unsplash.com/photo-1568576157197-72cd17d47eed?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1999&q=80'
    }
  },
  computed: {
    selected: function () {
      return this.$store.getters[store["a" /* GETTERS */].isSelected](this.uuid);
    },
    hovering: function () {
      return this.$store.getters[store["a" /* GETTERS */].isHovering](this.uuid);
    },
    overlayStyle() {
      return {
        backgroundImage: 'url(' + this.imgSrc + ')'
      };
    }
  },
  methods: {
    save() {
      return this.$el.innerHTML;
    }
  }
});
// CONCATENATED MODULE: ./src/components/NeonCard.vue?vue&type=script&lang=js
 /* harmony default export */ var components_NeonCardvue_type_script_lang_js = (NeonCardvue_type_script_lang_js); 
// EXTERNAL MODULE: ./src/components/NeonCard.vue?vue&type=style&index=0&id=e7fa22fc&prod&lang=css
var NeonCardvue_type_style_index_0_id_e7fa22fc_prod_lang_css = __webpack_require__("fc28");

// CONCATENATED MODULE: ./src/components/NeonCard.vue






/* normalize component */

var NeonCard_component = Object(componentNormalizer["a" /* default */])(
  components_NeonCardvue_type_script_lang_js,
  NeonCardvue_type_template_id_e7fa22fc_render,
  NeonCardvue_type_template_id_e7fa22fc_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var NeonCard = (NeonCard_component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"3c8b867e-vue-loader-template"}!./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/babel-loader/lib!./node_modules/vue-loader/lib/loaders/templateLoader.js??ref--6!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/NeonComeTalkToUs.vue?vue&type=template&id=ad321366&scoped=true
var NeonComeTalkToUsvue_type_template_id_ad321366_scoped_true_render = function render() {
  var _vm = this,
    _c = _vm._self._c;
  return _vm._m(0);
};
var NeonComeTalkToUsvue_type_template_id_ad321366_scoped_true_staticRenderFns = [function () {
  var _vm = this,
    _c = _vm._self._c;
  return _c('section', {
    staticClass: "callout"
  }, [_c('div', {
    staticClass: "callout_images"
  }, [_c('div', {
    staticClass: "callout_image"
  }, [_c('img', {
    attrs: {
      "src": "editor.srv(\"asset\", {\"path\":\"images/temp/callout-image-1.svg\"})",
      "alt": "",
      "width": "277",
      "height": "209"
    }
  })]), _c('div', {
    staticClass: "callout_image"
  }, [_c('img', {
    attrs: {
      "src": "{asset path='images/temp/callout-image-2.png'}",
      "alt": "",
      "width": "222",
      "height": "222"
    }
  })]), _c('div', {
    staticClass: "callout_image"
  }, [_c('img', {
    attrs: {
      "src": "{asset path='images/temp/callout-image-3.png'}",
      "alt": "",
      "width": "145",
      "height": "68"
    }
  })])]), _c('div', {
    staticClass: "callout_content"
  }, [_c('div', {
    staticClass: "shell"
  }, [_c('div', {
    staticClass: "callout_inner"
  }, [_c('div', {
    staticClass: "callout_head"
  }, [_c('h1', {
    staticClass: "callout_title"
  }, [_vm._v("Talk to us…")]), _c('h4', {
    staticClass: "callout_subtitle"
  }, [_vm._v("From Digital Transformation to Augmented Reality"), _c('br'), _vm._v("we can help you build your future.")]), _c('p', [_vm._v("To discuss digital transformation in your business or startup …")]), _c('a', {
    staticClass: "btn btn-white",
    attrs: {
      "href": "#"
    }
  }, [_vm._v("Send us a message")]), _vm._v(" "), _c('a', {
    staticClass: "btn btn-gray btn-outline",
    attrs: {
      "href": "",
      "onclick": "Calendly.initPopupWidget({ 'url': 'https://calendly.com/mark-probert/30-mins-call' });return false;"
    }
  }, [_vm._v("Book a call with Mark")])]), _c('div', {
    staticClass: "callout_body"
  }, [_c('ul', {
    staticClass: "list-contacts"
  }, [_c('li', [_c('h5', [_vm._v("Call us on:")]), _c('a', {
    attrs: {
      "href": "tel:01172050425"
    }
  }, [_vm._v("0117 205 0425")])]), _c('li', [_c('h5', [_vm._v("Email us at:")]), _c('a', {
    attrs: {
      "href": "mailto:talktous@newicon.net"
    }
  }, [_vm._v("talktous@newicon.net")])])])])])])])]);
}];

// CONCATENATED MODULE: ./src/components/NeonComeTalkToUs.vue?vue&type=template&id=ad321366&scoped=true

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/components/NeonComeTalkToUs.vue?vue&type=script&lang=js
/* harmony default export */ var NeonComeTalkToUsvue_type_script_lang_js = ({
  name: "NeonComeTalkToUs"
});
// CONCATENATED MODULE: ./src/components/NeonComeTalkToUs.vue?vue&type=script&lang=js
 /* harmony default export */ var components_NeonComeTalkToUsvue_type_script_lang_js = (NeonComeTalkToUsvue_type_script_lang_js); 
// CONCATENATED MODULE: ./src/components/NeonComeTalkToUs.vue





/* normalize component */

var NeonComeTalkToUs_component = Object(componentNormalizer["a" /* default */])(
  components_NeonComeTalkToUsvue_type_script_lang_js,
  NeonComeTalkToUsvue_type_template_id_ad321366_scoped_true_render,
  NeonComeTalkToUsvue_type_template_id_ad321366_scoped_true_staticRenderFns,
  false,
  null,
  "ad321366",
  null
  
)

/* harmony default export */ var NeonComeTalkToUs = (NeonComeTalkToUs_component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"3c8b867e-vue-loader-template"}!./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/babel-loader/lib!./node_modules/vue-loader/lib/loaders/templateLoader.js??ref--6!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/CmpTree.vue?vue&type=template&id=66496a22
var CmpTreevue_type_template_id_66496a22_render = function render() {
  var _vm = this,
    _c = _vm._self._c;
  return _c('ul', {
    staticClass: "tree isRoot",
    staticStyle: {
      "margin": "0px"
    }
  }, [_c('CmpTreeNode', {
    attrs: {
      "cmp": _vm.cmp,
      "children": _vm.children
    }
  })], 1);
};
var CmpTreevue_type_template_id_66496a22_staticRenderFns = [];

// CONCATENATED MODULE: ./src/CmpTree.vue?vue&type=template&id=66496a22

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"3c8b867e-vue-loader-template"}!./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/babel-loader/lib!./node_modules/vue-loader/lib/loaders/templateLoader.js??ref--6!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/CmpTreeNode.vue?vue&type=template&id=73b94e4d
var CmpTreeNodevue_type_template_id_73b94e4d_render = function render() {
  var _vm = this,
    _c = _vm._self._c;
  return _c('li', [_c('div', {
    staticClass: "niNode",
    class: {
      isBranch: _vm.hasChildren,
      isLeaf: !_vm.hasChildren,
      isSelected: _vm.isSelected,
      isHover: _vm.isHover,
      isOpen: _vm.isOpen,
      'boxTitle': _vm.isPage,
      isPage: _vm.isPage
    },
    style: {
      paddingLeft: _vm.padding + 'px'
    },
    on: {
      "mouseover": _vm.onMouseOver,
      "click": _vm.toggle
    }
  }, [_c('span', {
    staticClass: "niNode_caret"
  }), _c('span', {
    staticClass: "niNode_icon",
    domProps: {
      "innerHTML": _vm._s(_vm.icon)
    }
  }), _c('span', {
    staticClass: "niNode_label"
  }, [_vm._v(_vm._s(_vm.label))])]), _vm.hasChildren ? _c('ul', {
    directives: [{
      name: "show",
      rawName: "v-show",
      value: _vm.isOpen,
      expression: "isOpen"
    }],
    staticClass: "tree"
  }, _vm._l(_vm.children, function (cmp) {
    return _c('CmpTreeNode', {
      key: cmp,
      attrs: {
        "cmp": cmp
      }
    });
  }), 1) : _vm._e()]);
};
var CmpTreeNodevue_type_template_id_73b94e4d_staticRenderFns = [];

// CONCATENATED MODULE: ./src/CmpTreeNode.vue?vue&type=template&id=73b94e4d

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/CmpTreeNode.vue?vue&type=script&lang=js

/* harmony default export */ var CmpTreeNodevue_type_script_lang_js = ({
  name: 'CmpTreeNode',
  props: {
    cmp: String
  },
  data: function () {
    return {
      depth: (this.$parent.depth || 0) + 1,
      isOpen: false,
      showProps: false
    };
  },
  methods: {
    toggle: function () {
      this.$store.commit('editor/select', {
        uuid: this.cmp
      });
      if (this.hasChildren) {
        this.isOpen = !this.isOpen;
      }
    },
    onMouseOver: function () {
      this.$store.commit('editor/hover', {
        uuid: this.cmp
      });
    },
    expandParents: function () {
      if (this.$parent.$options.name === this.$options.name) {
        this.$parent.expandParents();
      }
      this.isOpen = true;
    }
  },
  created: function () {
    if (this.isSelected) this.expandParents();
  },
  watch: {
    isSelected: function (val) {
      if (val === true) {
        this.expandParents();
      }
    }
  },
  computed: {
    isPage: function () {
      return this.type === 'NeonPage';
    },
    padding: function () {
      return this.depth * 10;
    },
    isHover: function () {
      return this.$store.getters[store["a" /* GETTERS */].isHovering](this.cmp);
    },
    isSelected: function () {
      return this.$store.getters[store["a" /* GETTERS */].isSelected](this.cmp);
    },
    component: function () {
      return this.$store.getters[store["a" /* GETTERS */].components][this.cmp];
    },
    componentConfig: function () {
      return this.$store.getters[store["a" /* GETTERS */].getComponentConfig](this.type);
    },
    props: function () {
      return this.component;
    },
    children: function () {
      if (this.component) {
        return this.component.children;
      }
      return [];
    },
    type: function () {
      if (this.component) {
        return this.component.cmp;
      }
      return 'unknown';
    },
    hasChildren: function () {
      if (this.children === undefined) return false;
      return this.children.length;
    },
    label() {
      return _.get(this.componentConfig, 'title', this.type);
    },
    icon() {
      return _.get(this.componentConfig, 'icon', 'icon');
    }
  }
});
// CONCATENATED MODULE: ./src/CmpTreeNode.vue?vue&type=script&lang=js
 /* harmony default export */ var src_CmpTreeNodevue_type_script_lang_js = (CmpTreeNodevue_type_script_lang_js); 
// EXTERNAL MODULE: ./src/CmpTreeNode.vue?vue&type=style&index=0&id=73b94e4d&prod&lang=css
var CmpTreeNodevue_type_style_index_0_id_73b94e4d_prod_lang_css = __webpack_require__("941d");

// CONCATENATED MODULE: ./src/CmpTreeNode.vue






/* normalize component */

var CmpTreeNode_component = Object(componentNormalizer["a" /* default */])(
  src_CmpTreeNodevue_type_script_lang_js,
  CmpTreeNodevue_type_template_id_73b94e4d_render,
  CmpTreeNodevue_type_template_id_73b94e4d_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var CmpTreeNode = (CmpTreeNode_component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/CmpTree.vue?vue&type=script&lang=js

/* harmony default export */ var CmpTreevue_type_script_lang_js = ({
  name: 'CmpTree',
  components: {
    CmpTreeNode: CmpTreeNode
  },
  watch: {},
  props: {
    cmp: String
  }
});
// CONCATENATED MODULE: ./src/CmpTree.vue?vue&type=script&lang=js
 /* harmony default export */ var src_CmpTreevue_type_script_lang_js = (CmpTreevue_type_script_lang_js); 
// EXTERNAL MODULE: ./src/CmpTree.vue?vue&type=style&index=0&id=66496a22&prod&lang=css
var CmpTreevue_type_style_index_0_id_66496a22_prod_lang_css = __webpack_require__("b4c0");

// CONCATENATED MODULE: ./src/CmpTree.vue






/* normalize component */

var CmpTree_component = Object(componentNormalizer["a" /* default */])(
  src_CmpTreevue_type_script_lang_js,
  CmpTreevue_type_template_id_66496a22_render,
  CmpTreevue_type_template_id_66496a22_staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var CmpTree = (CmpTree_component.exports);
// CONCATENATED MODULE: ./src/PhoebePlugin.js
const PhoebePlugin = {
  getCmp: function (cmp) {
    return Vue.options.components[cmp];
  },
  getCmpOptions: function (cmp) {
    return this.getCmp().options;
  },
  getPropsFromCmp: function (cmp) {
    return this.getCmpOptions().props;
  }
};
PhoebePlugin.install = function (Vue, options) {
  // 1. add global method or property
  // Vue.myGlobalMethod = function () {
  // 	// some logic ...
  // }

  // 2. add a global asset
  // Vue.directive('my-directive', {
  // 	bind(el, binding, vnode, oldVnode) {
  // 		// some logic ...
  // 	}
  // })

  // 3. inject some component options
  Vue.mixin({
    props: {
      uuid: String
      //cmp: String,
      //_parent: String,
    },
    created: function () {
      // check if we have a _uuid
      // if we do - ensure the _uuid exists in the store
      // if not lets create a new uuid and add it as a reference
      // if (this.$props._uuid === undefined) {
      // 	const cmp = Object.assign({}, this.$props, {"_uuid": neon.uuid64(), "_type": this.$options.name});
      // 	Store.commit('createCmp', cmp);
      // }
    },
    computed: {}
  });

  // 4. add an instance method
  Vue.prototype.$uuid64 = function () {
    return neon.uuid64();
  };
  Vue.prototype.$createCmp = function (cmp) {
    return {
      uuid: neon.uuid64(),
      cmp: cmp
    };
  };
};
if (typeof window !== 'undefined') window.phoebe = PhoebePlugin;
neon.phoebe = neon.phoebe || {};
neon.phoebe.getCmpObject = function (cmpName) {
  if (Vue.component(cmpName)) return Vue.component(cmpName).options;
  // eslint-disable-next-line no-console
  console.error(cmpName + ' is not a global registered vue component');
  return {};
};
neon.phoebe.getCmpDefault = function (cmpName) {
  let defaults = {};
  console.log(this.getCmpObject(cmpName).props, 'default components');
  _.forEach(this.getCmpObject(cmpName).props, function (value, key) {
    defaults[key] = _.result(value, 'default');
  });
  console.log(this.getCmpObject(cmpName).props, defaults, 'default components');
  return defaults;
};
/* harmony default export */ var src_PhoebePlugin = (PhoebePlugin);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/Editor.vue?vue&type=script&lang=js
/**
 * The neon editor component is responsible for rendering components on the website when in edit mode.
 *
 * Withing smarty:
 * {editor}
 * {/editor}
 *
 * The editor component is only used during edit mode - otherwise server rendered content is returned.
 * Rendering the components dynamically allows us to control the user input and understand intentions.
 * @todo rename to ContentEditor
 */


//import _ from 'lodash';









external_commonjs_vue_commonjs2_vue_root_Vue_default.a.component('NeonCmpList', NeonCmpList);
external_commonjs_vue_commonjs2_vue_root_Vue_default.a.component('NeonCmp', NeonCmp);
external_commonjs_vue_commonjs2_vue_root_Vue_default.a.component('NeonCard', NeonCard);
external_commonjs_vue_commonjs2_vue_root_Vue_default.a.component('NeonComeTalkToUs', NeonComeTalkToUs);
external_commonjs_vue_commonjs2_vue_root_Vue_default.a.component('CmpTree', CmpTree);
external_commonjs_vue_commonjs2_vue_root_Vue_default.a.component('CmpTreeNode', CmpTreeNode);
src_PhoebePlugin.install(external_commonjs_vue_commonjs2_vue_root_Vue_default.a);
var depth = function (component) {
  let depth = 0,
    parent = component;
  while (parent !== undefined) {
    parent = parent.$parent;
    depth++;
  }
  return depth;
};
var indent = function (component) {
  return '  '.repeat(depth(component));
};
/* harmony default export */ var Editorvue_type_script_lang_js = ({
  name: 'NeonEditor',
  store: store["c" /* default */],
  provide: function () {
    return {
      editor: this
    };
  },
  props: {
    contentKey: {
      type: String
    },
    components: {
      type: Object
    }
  },
  created() {
    this.$store.subscribe((mutation, state) => {
      window.parent.postMessage({
        name: 'neonEditorState',
        state: state.editor,
        mutation
      }, '*');
    });
    this.$store.commit(store["b" /* MUTATIONS */].setComponents, this.components);
  },
  computed: {
    rootComponent() {
      return this.$store.getters[store["a" /* GETTERS */].rootComponent];
    },
    editMode: {
      get() {
        return this.$store.getters[store["a" /* GETTERS */].isEditMode];
      },
      set(value) {
        this.$store.commit(store["b" /* MUTATIONS */].setEditMode, value);
      }
    }
  },
  methods: {
    getCmpObject(cmpName) {
      if (external_commonjs_vue_commonjs2_vue_root_Vue_default.a.component(cmpName)) return external_commonjs_vue_commonjs2_vue_root_Vue_default.a.component(cmpName).options;
      // eslint-disable-next-line no-console
      console.error(cmpName + ' is not a global registered vue component');
      return {};
    },
    deleteSelected() {
      let uuids = this.$store.getters[store["a" /* GETTERS */].getSelected];
      this.$store.commit(store["b" /* MUTATIONS */].deleteComponents, {
        uuids
      });
    },
    getCmpDefaults(cmpName) {
      return neon.phoebe.getCmpDefault(cmpName);
    },
    html() {
      this.editMode = false;
      this.$store.state.selected = {};
      this.$store.state.hovering = '';
      return new Promise((resolve, reject) => {
        this.$nextTick(() => {
          var html = this.$el.innerHTML;
          html = html.replace(/<!---->/g, '');
          resolve(html);
          this.editMode = true;
        });
      });
    },
    save: async function () {
      // save to the db
      var html = await this.html();
      var props = {
        components: this.$store.getters[store["a" /* GETTERS */].components],
        contentKey: this.contentKey
      };
      var editorProps = JSON.stringify(props);
      var content = '<!-- ni:editor ' + editorProps + ' -->\n' + html + '\n<!-- /ni:editor -->';
      var data = {
        content: content,
        //JSON.stringify({html: this.html(), components: {}}),
        key: this.contentKey,
        pageId: null
      };
      $.ajax({
        method: "POST",
        url: neon.url('/cms/data/update-content-widget'),
        data: data,
        success: function (response) {
          alert('saved');
        }
      });
    }
  },
  mounted() {
    window.editor = this;
    // create toolbar
    const toolbarContainer = document.createElement('div');
    document.body.appendChild(toolbarContainer);
    new external_commonjs_vue_commonjs2_vue_root_Vue_default.a({
      parent: neon.app,
      render: h => h('neon-editor-toolbar')
    }).$mount(toolbarContainer);
    if (window.neon && window.neon.editor === undefined) {
      window.neon.editor = this;
    }

    // only create one for debug
    if (window.niEditor === undefined) window.niEditor = this;
  }
});
external_commonjs_vue_commonjs2_vue_root_Vue_default.a.component('neon-editor-toolbar', {
  inject: {
    'editor': {
      default: false
    }
  },
  template: `
			<div v-if="isEditMode" style="position:fixed;bottom:0;left:0;right:0;display:flex;background-color:#000;z-index:999999999999999;">
				<button @click="save" class="btn btn-primary btn-sm">Save</button>
				<button @click="deleteSelected" class="btn btn-danger btn-sm">Delete</button>
				<button @click="editPage">Edit</button>
				<button @click="exitEditMode">Exit</button>
				<div class="btn-group mr-2 btn-group-sm" role="group" aria-label="Undo group">
					<button class="btn btn-secondary btn-sm" :disabled="!canUndo" @click="undo">undo {{history.length - 1}}</button>
					<button class="btn btn-secondary btn-sm"  :disabled="!canRedo" @click="redo">redo {{(history.length - currentIndex) - 1}}</button>
				</div>
				<div class="badge">Components: {{componentCount}}</div>
			</div>
		`,
  created() {
    let firstState = _.cloneDeep(this.$store.state);
    this.addState(firstState);
    this.$store.subscribe((mutation, state) => {
      if (this.ignoreMutations.indexOf(mutation.type) === -1) {
        this.addState(_.cloneDeep(state));
      }
    });
  },
  mounted() {
    window.toolbar = this;
  },
  data() {
    return {
      history: [],
      // the number of history states to remember
      historyStates: 1000,
      currentIndex: -1,
      ignoreMutations: ['editor/hover']
    };
  },
  computed: {
    componentCount() {
      return Object.keys(this.$store.state.editor.components).length;
    },
    isEditMode() {
      return this.$store.getters[store["a" /* GETTERS */].isEditMode];
    },
    canUndo() {
      return this.history[this.currentIndex - 1] !== undefined;
    },
    canRedo() {
      return this.history[this.currentIndex + 1] !== undefined;
    }
  },
  methods: {
    addState(state) {
      // may be we have to remove redo steps
      if (this.currentIndex + 1 < this.history.length) {
        this.history.splice(this.currentIndex + 1);
      }
      this.history.push(state);
      this.currentIndex++;
      if (this.history.length === this.historyStates) {
        this.history.splice(0, 1);
      }
    },
    undo() {
      if (!this.canUndo) return;
      const prevState = this.history[this.currentIndex - 1];
      // take a copy of the history state
      // because it would be changed during store mutations
      // what would corrupt the undo-redo-history
      // (same on redo)
      this.$store.replaceState(_.cloneDeep(prevState));
      this.currentIndex--;
    },
    redo() {
      if (!this.canRedo) return;
      const nextState = this.history[this.currentIndex + 1];
      this.$store.replaceState(_.cloneDeep(nextState));
      this.currentIndex++;
    },
    editPage() {
      location.href = neon.url('cms/editor/index', {
        "id": 'styleguide'
      });
    },
    save() {
      window.niEditor.save();
    },
    deleteSelected() {
      window.niEditor.deleteSelected();
    },
    postMessage() {
      // origin
      //window.parent.postMessage('Hey there',  '*');
    },
    exitEditMode() {
      $.post(neon.url('cms/editor/exit-edit-mode')).done(function () {
        location.href = location.href.replace('edit=1', '');
      });
    }
  }
});

// only listen to parent messages if we have a parent
window.addEventListener("message", receiveParentMessage, false);
function receiveParentMessage(event) {
  if (event.data.name === 'neonEditorToFrame') {
    if (event.data.mutation.type !== 'editor/syncState') {
      neon.Store.commit(event.data.mutation.type, event.data.mutation.payload);
    }
  }
}
external_commonjs_vue_commonjs2_vue_root_Vue_default.a.component('NeonThing', {
  name: 'NeonThing',
  comments: true,
  props: {
    content: {
      type: String,
      default: 'HERE IS A THING'
    }
  },
  template: `<div>
			{{content}}
		</div>`,
  methods: {
    save() {
      return `${indent(this)}<!-- cmp:NeonThing -->\n${indent(this)}<div>${this.content}</div>\n${indent(this)}<!-- /cmp:NeonThing -->`;
    }
  }
});
external_commonjs_vue_commonjs2_vue_root_Vue_default.a.component('NeonColumns', {
  inject: {
    'editor': {
      default: false
    }
  },
  comments: true,
  props: {
    uuid: {
      type: String
    },
    columns: {
      type: Number,
      default: 2
    },
    children: {
      type: Array,
      default: function () {
        return [];
      }
    }
  },
  template: `<NeonCmpList class="row" :style="editor.editMode?'padding:16px;':''" :render="uuid"></NeonCmpList>`,
  methods: {
    save() {
      var children = this.$children.map(c => c.save()).join("\n");
      return `${indent(this)}<!-- cmp:NeonColumns -->\n${indent(this)}<div class="row" style="padding:10px;">\n${children}\n${indent(this)}</div>\n${indent(this)}<!-- /cmp:NeonColumns -->`;
    }
  }
});
external_commonjs_vue_commonjs2_vue_root_Vue_default.a.component('NeonColumn', {
  inject: {
    'editor': {
      default: false
    }
  },
  comments: true,
  props: {
    me: {},
    columns: {
      type: Number,
      default: 2
    },
    children: {
      type: Array,
      default: function () {
        return [];
      }
    }
  },
  template: `
			<NeonCmpList :render="uuid" class="col"></NeonCmpList>
		`,
  methods: {
    save() {
      var children = this.$children.map(c => c.save()).join("\n");
      return `${indent(this)}<!-- cmp:NeonColumn -->\n${indent(this)}<div class="col" style="padding:10px;">\n${children}\n${indent(this)}</div>\n${indent(this)}<!-- /cmp:NeonColumn -->`;
    }
  }
});
external_commonjs_vue_commonjs2_vue_root_Vue_default.a.component('NeonGrid', {
  inject: {
    'editor': {
      default: false
    }
  },
  props: {
    me: {},
    columns: {
      type: Number,
      default: 2
    },
    children: []
  },
  template: `<NeonCmpList class="row" style="border:1px solid black;padding:10px;" :render="uuid"></NeonCmpList>`,
  methods: {
    save() {
      return indent(this) + this._name + ' provide save function';
    }
  }
});
external_commonjs_vue_commonjs2_vue_root_Vue_default.a.component('NeonHeader', {
  props: {
    content: {
      type: String,
      default: 'Enter Heading text'
    }
  },
  template: `<h1>{{content}}</h1>`
});
external_commonjs_vue_commonjs2_vue_root_Vue_default.a.component('NeonParagraph', {
  props: {
    text: {
      type: String,
      default: 'Enter paragraph text'
    }
  },
  template: `<p>{{text}}</p>`
});
external_commonjs_vue_commonjs2_vue_root_Vue_default.a.component('NeonRoot', {
  props: {
    children: {
      type: Array,
      default: function () {
        return [];
      }
    }
  },
  template: `<NeonCmpList :children="children" ></NeonCmpList>`,
  methods: {
    save() {
      var children = this.$children.map(child => child.save()).join('\n');
      return `${indent(this)}<NeonRoot>\n${children}\n${indent(this)}</NeonRoot>`;
    }
  }
});

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  Theme specific and need to be loaded:
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

external_commonjs_vue_commonjs2_vue_root_Vue_default.a.component('NeonComeTalkToUsAgain', {
  template: `<NeonServerSideRender tag="section_come_talk_to_us"></NeonServerSideRender>`
});
external_commonjs_vue_commonjs2_vue_root_Vue_default.a.component('cta', {
  template: `<NeonServerSideRender tag="cta" :params="$props"></NeonServerSideRender>`
});
external_commonjs_vue_commonjs2_vue_root_Vue_default.a.component('in_depth', {
  template: `<NeonServerSideRender tag="in_depth" :params="$props"></NeonServerSideRender>`
});
external_commonjs_vue_commonjs2_vue_root_Vue_default.a.component('hero_banner', {
  props: {
    title: {
      type: String,
      default: ''
    },
    subTitle: {
      type: String,
      default: ''
    }
  },
  template: `<div><NeonServerSideRender tag="hero_banner" :params="$props"></NeonServerSideRender></div>`
});
external_commonjs_vue_commonjs2_vue_root_Vue_default.a.component('subscribe_form', {
  props: {},
  template: `<div><NeonServerSideRender tag="subscribe_form" :params="$props"></NeonServerSideRender></div>`
});

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

external_commonjs_vue_commonjs2_vue_root_Vue_default.a.component('NeonCmsCollection', {
  props: {
    info: {
      type: String,
      default: ''
    },
    cols: {
      type: Number,
      default: 3
    },
    // store override properties
    map: {
      type: Object,
      default: function () {
        return {};
      }
    },
    rows: {
      type: Array,
      default: function () {
        return [{
          title: 'my title'
        }, {
          title: 'other'
        }, {
          title: 'other'
        }];
      }
    }
  },
  template: `<div class="row">
 {{info}} <br>
 map:{{map}}
			<div v-for="row in rows" :class="column">
				{{row}}
				<NeonCmp v-for="uuid in childComponents" :key="uuid" :map-props="getMapProps(uuid, row)" :render="uuid" ></NeonCmp>
			</div>
<!--		<NeonCmp v-for="uuid in childComponents" :key="uuid" :render="uuid" ></NeonCmp>-->
			<NeonAdder :uuid="uuid"></NeonAdder>
		</div>`,
  methods: {
    getMapProps(uuid, row) {
      var mapProps = {};
      console.log(this.map, uuid, this.map[uuid], 'get map');
      var map = this.map[uuid];
      // map looks like:
      // [propName]: dataName
      // { "propKey": "dataKey" }
      // { "somethihng.title": "author.name" }
      // would create {something: {title: "value of row['author']['name']"}}
      console.log(uuid, 'UUID Map');
      console.log(map, 'MAP');
      if (map) {
        _.forEach(map, (rowKey, propKey) => {
          console.log('GET _.get(row, rowKey)', _.get(row, rowKey));
          _.set(mapProps, propKey, _.get(row, rowKey));
        });
      }
      console.log('MAP PROPS', mapProps);
      return mapProps;
    }
  },
  computed: {
    column() {
      return 'col-' + 12 / this.cols;
    },
    childComponents() {
      // get the child component uuids - children of cms collection are repeated
      let components = this.$store.getters[store["a" /* GETTERS */].getChildren](this.uuid);
      // loops through component and replace properties with maps.
      _.forEach(components, uuid => {
        if (this.map[uuid]) {}
      });
      console.log(components, 'components');
      return components;
    }
  }
});
external_commonjs_vue_commonjs2_vue_root_Vue_default.a.component('NeonHeroText', {
  props: {
    title: {
      type: String,
      default: 'Enter a title'
    },
    bgTitle: {
      type: String,
      default: 'BG Title'
    },
    subtitle: {
      type: String,
      default: 'Enter a sub title if required'
    },
    body: {
      type: String,
      default: "A summary of the main building blocks of the newicon site"
    }
  },
  template: `<div><NeonServerSideRender tag="hero_text" :params="$props"></NeonServerSideRender></div>`
});
external_commonjs_vue_commonjs2_vue_root_Vue_default.a.component('NeonServerSideRender', {
  props: {
    tag: '',
    params: {}
  },
  watch: {
    params: {
      // Will fire as soon as the component is created
      immediate: true,
      deep: true,
      handler() {
        this.update();
      }
    }
  },
  data() {
    return {
      template: 'Loading...'
    };
  },
  template: `<div v-html="template"></div>`,
  methods: {
    update() {
      var vm = this;
      $.ajax({
        method: "POST",
        url: neon.url('/cms/data/server-render'),
        data: {
          tag: this.tag,
          params: this.params
        },
        success: function (response) {
          vm.template = response;
        }
      });
    }
  }
});
// CONCATENATED MODULE: ./src/Editor.vue?vue&type=script&lang=js
 /* harmony default export */ var src_Editorvue_type_script_lang_js = (Editorvue_type_script_lang_js); 
// EXTERNAL MODULE: ./src/Editor.vue?vue&type=style&index=0&id=297d18db&prod&lang=css
var Editorvue_type_style_index_0_id_297d18db_prod_lang_css = __webpack_require__("5218");

// CONCATENATED MODULE: ./src/Editor.vue






/* normalize component */

var Editor_component = Object(componentNormalizer["a" /* default */])(
  src_Editorvue_type_script_lang_js,
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var Editor = __webpack_exports__["a"] = (Editor_component.exports);

/***/ }),

/***/ "2877":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "a", function() { return normalizeComponent; });
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


/***/ }),

/***/ "2e4b":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_NeonAdder_vue_vue_type_style_index_0_id_7a4aec86_prod_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("57aa");
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_NeonAdder_vue_vue_type_style_index_0_id_7a4aec86_prod_lang_css__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_NeonAdder_vue_vue_type_style_index_0_id_7a4aec86_prod_lang_css__WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */


/***/ }),

/***/ "38b3":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_InspectorPanel_vue_vue_type_style_index_0_id_16bcd466_prod_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("c32b");
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_InspectorPanel_vue_vue_type_style_index_0_id_16bcd466_prod_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_InspectorPanel_vue_vue_type_style_index_0_id_16bcd466_prod_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */


/***/ }),

/***/ "422a":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"3c8b867e-vue-loader-template"}!./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/babel-loader/lib!./node_modules/vue-loader/lib/loaders/templateLoader.js??ref--6!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/NeonAdder.vue?vue&type=template&id=7a4aec86
var render = function render() {
  var _vm = this,
    _c = _vm._self._c;
  return _c('div', {
    style: _vm.style
  }, [_vm.isEditMode ? _c('a', {
    ref: "cmpInsertButton",
    staticClass: "btn niEditor_btnAdd",
    on: {
      "click": _vm.showComponentMenuToggle
    }
  }, [_c('svg', {
    staticClass: "dashicon dashicons-insert",
    attrs: {
      "aria-hidden": "true",
      "role": "img",
      "focusable": "false",
      "xmlns": "http://www.w3.org/2000/svg",
      "width": "20",
      "height": "20",
      "viewBox": "0 0 20 20"
    }
  }, [_c('path', {
    attrs: {
      "d": "M10 1c-5 0-9 4-9 9s4 9 9 9 9-4 9-9-4-9-9-9zm0 16c-3.9 0-7-3.1-7-7s3.1-7 7-7 7 3.1 7 7-3.1 7-7 7zm1-11H9v3H6v2h3v3h2v-3h3V9h-3V6z"
    }
  })])]) : _vm._e(), _vm.isEditMode ? _c('div', {
    directives: [{
      name: "show",
      rawName: "v-show",
      value: _vm.componentMenu,
      expression: "componentMenu"
    }],
    ref: "cmpMenuPop"
  }, _vm._l(_vm.componentConfig, function (cmpConfig, cmpName) {
    return _c('a', {
      key: cmpName,
      staticClass: "btn btn-default",
      on: {
        "click": function ($event) {
          return _vm.addComponent(cmpName);
        }
      }
    }, [_vm._v(" " + _vm._s(cmpName) + " "), _c('span', {
      domProps: {
        "innerHTML": _vm._s(cmpConfig.icon)
      }
    })]);
  }), 0) : _vm._e()]);
};
var staticRenderFns = [];

// CONCATENATED MODULE: ./src/NeonAdder.vue?vue&type=template&id=7a4aec86

// EXTERNAL MODULE: ./src/store.js
var store = __webpack_require__("c0d6");

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/NeonAdder.vue?vue&type=script&lang=js

/* harmony default export */ var NeonAddervue_type_script_lang_js = ({
  name: "NeonAdder",
  data() {
    return {
      componentMenu: false,
      style: {
        top: '-10px',
        left: '-10px'
      }
    };
  },
  mounted() {
    // var bounds = this.$parent.$el.getBoundingClientRect();
    // var left = bounds.width / 2 + bounds.left;
    // var top = bounds.top;
    // this.style.position = 'absolute';
    // this.style.top = top+'px';
    // this.style.left = left+'px';
    // console.log(this.$parent.$el.getBoundingClientRect());
  },
  props: {
    /**
     * The component uuid that this adder will add components to
     */
    uuid: {
      type: String,
      required: true
    }
  },
  computed: {
    isEditMode() {
      return this.$store.getters[store["a" /* GETTERS */].isEditMode];
    },
    componentConfig() {
      return this.$store.getters[store["a" /* GETTERS */].getAvailableComponentsFor](this.uuid);
    }
  },
  methods: {
    showComponentMenuToggle() {
      this.componentMenu = !this.componentMenu;
    },
    addComponent(componentName, props = {}, children = []) {
      console.log(store["b" /* MUTATIONS */].addComponent, {
        componentName: componentName,
        props: props,
        children: children,
        parent: this.uuid
      });
      this.$store.commit(store["b" /* MUTATIONS */].addComponent, {
        componentName,
        props,
        children,
        parent: this.uuid
      });
      this.componentMenu = false;
    }
  }
});
// CONCATENATED MODULE: ./src/NeonAdder.vue?vue&type=script&lang=js
 /* harmony default export */ var src_NeonAddervue_type_script_lang_js = (NeonAddervue_type_script_lang_js); 
// EXTERNAL MODULE: ./src/NeonAdder.vue?vue&type=style&index=0&id=7a4aec86&prod&lang=css
var NeonAddervue_type_style_index_0_id_7a4aec86_prod_lang_css = __webpack_require__("2e4b");

// EXTERNAL MODULE: ./node_modules/vue-loader/lib/runtime/componentNormalizer.js
var componentNormalizer = __webpack_require__("2877");

// CONCATENATED MODULE: ./src/NeonAdder.vue






/* normalize component */

var component = Object(componentNormalizer["a" /* default */])(
  src_NeonAddervue_type_script_lang_js,
  render,
  staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* harmony default export */ var NeonAdder = __webpack_exports__["a"] = (component.exports);

/***/ }),

/***/ "5218":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Editor_vue_vue_type_style_index_0_id_297d18db_prod_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("98ff");
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Editor_vue_vue_type_style_index_0_id_297d18db_prod_lang_css__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Editor_vue_vue_type_style_index_0_id_297d18db_prod_lang_css__WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */


/***/ }),

/***/ "56d7":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(global) {/* harmony import */ var _Cobe_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("80bb");
/* harmony import */ var _Editor_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__("24d4");
/* harmony import */ var _NeonAdder_vue__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__("422a");




// Auto-install when vue is found (eg. in browser via <script> tag)
let GlobalVue = null;
if (typeof window !== 'undefined') {
  GlobalVue = window.Vue;
} else if (typeof global !== 'undefined') {
  GlobalVue = global.Vue;
}
if (GlobalVue) {
  GlobalVue.component('neon-editor', _Editor_vue__WEBPACK_IMPORTED_MODULE_1__[/* default */ "a"]);
  GlobalVue.component('cobe', _Cobe_vue__WEBPACK_IMPORTED_MODULE_0__[/* default */ "a"]);
  GlobalVue.component('NeonAdder', _NeonAdder_vue__WEBPACK_IMPORTED_MODULE_2__[/* default */ "a"]);
}
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__("c8ba")))

/***/ }),

/***/ "57aa":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "5d86":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "64b6":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_NeonCmpList_vue_vue_type_style_index_0_id_aa425d4e_prod_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("5d86");
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_NeonCmpList_vue_vue_type_style_index_0_id_aa425d4e_prod_lang_css__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_NeonCmpList_vue_vue_type_style_index_0_id_aa425d4e_prod_lang_css__WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */


/***/ }),

/***/ "722a":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "80bb":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"3c8b867e-vue-loader-template"}!./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/babel-loader/lib!./node_modules/vue-loader/lib/loaders/templateLoader.js??ref--6!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/Cobe.vue?vue&type=template&id=c348feda&scoped=true
var render = function render() {
  var _vm = this,
    _c = _vm._self._c;
  return _c('div', {
    staticClass: "cobe",
    style: 'grid-template-columns: minmax(200px, min-content) auto ' + _vm.inspectorWidth + 'px'
  }, [_c('div', {
    staticClass: "toolbar"
  }, [_c('a', {
    staticClass: "btn btn-default",
    attrs: {
      "href": '/cms/index/index'
    }
  }, [_c('span', {
    staticClass: "fa fa-chevron-left"
  }, [_vm._v(" ")]), _vm._v("Exit Edit Mode")]), _c('span', {
    staticClass: "workbenchHeader_title"
  }, [_vm._v(" Cobe CMS: Select a page below to edit its content.")])]), _c('div', {
    staticClass: "tools"
  }, [_c('div', [_c('ul', {
    staticClass: "tree"
  }, [_c('input', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.pageSearch,
      expression: "pageSearch"
    }],
    staticStyle: {
      "width": "100%",
      "border": "0",
      "padding-left": "4px"
    },
    attrs: {
      "placeholder": "Search"
    },
    domProps: {
      "value": _vm.pageSearch
    },
    on: {
      "input": function ($event) {
        if ($event.target.composing) return;
        _vm.pageSearch = $event.target.value;
      }
    }
  }), _vm._l(_vm.pagesOrdered, function (page) {
    return _c('li', {
      key: page.id,
      on: {
        "click": function ($event) {
          return _vm.onPageSelected(page);
        }
      }
    }, [_c('div', {
      staticClass: "niNode isPage",
      class: {
        'isSelected': _vm.onPage == page.nice_id
      }
    }, [_c('span', {
      staticClass: "niNode_icon"
    }, [page.collection == 1 ? _c('i', {
      staticClass: "fa fa-folder"
    }) : _vm._e(), page.collection == 0 ? _c('i', {
      staticClass: "fa fa-file-o"
    }) : _vm._e()]), _c('span', {
      staticClass: "niNode_label"
    }, [_c('span', {
      staticClass: "page_title"
    }, [_vm._v(_vm._s(page.name || page.title || 'Unamed'))])])])]);
  })], 2)]), _c('div', [_c('ul', {
    staticClass: "tree isRoot",
    staticStyle: {
      "margin": "0px"
    }
  }, [_c('cmp-tree-node', {
    attrs: {
      "cmp": _vm.rootComponent
    }
  })], 1)])]), _c('div', {
    staticClass: "stage"
  }, [_c('div', {
    staticClass: "resizeHandle",
    on: {
      "mousedown": function ($event) {
        return _vm.onResizeHandleMouseDownRight($event, 'inspectorWidth');
      }
    }
  }), _c('div', {
    staticClass: "stage_preview",
    attrs: {
      "id": "stagePreview"
    }
  }, [_c('stage-selector', {
    attrs: {
      "id": "stage_overlay"
    }
  }), _vm.onPage ? _c('iframe', {
    key: _vm.iframeSrc,
    ref: "siteFrame",
    attrs: {
      "id": "siteFrame",
      "frameborder": "0",
      "src": _vm.iframeSrc
    },
    on: {
      "load": function ($event) {
        return _vm.frameLoad();
      }
    }
  }) : _c('div', {
    attrs: {
      "id": "emptyFrame"
    }
  }, [_vm.pages.length != 0 ? _c('div', [_vm._v("Select a page from the left to begin editing!")]) : _c('div', [_vm._v("Create a page")])])], 1)]), _c('div', {
    staticClass: "inspector"
  }, [_c('PageSeo', {
    attrs: {
      "page-uuid": _vm.page ? _vm.page.id : ''
    }
  }), _c('InspectorPanel', {
    attrs: {
      "title": "Page Data",
      "startOpen": false
    }
  }, [_c('pre', [_vm._v(_vm._s(_vm.page))])]), _vm._l(_vm.selectedComponents, function (component, uuid) {
    return _c('pre', {
      key: uuid
    }, [_vm._v(_vm._s(uuid) + " " + _vm._s(component) + " ")]);
  }), _vm._l(_vm.selectedComponents, function (component, uuid) {
    return _c('textarea', {
      key: uuid + 'textarea',
      staticStyle: {
        "width": "100%"
      },
      attrs: {
        "rows": "10"
      },
      on: {
        "input": function ($event) {
          return _vm.onUpdateComponentProps(uuid, $event);
        }
      }
    }, [_vm._v(_vm._s(_vm.componentProps(component.cmp, component)))]);
  }), _c('br')], 2)]);
};
var staticRenderFns = [];

// CONCATENATED MODULE: ./src/Cobe.vue?vue&type=template&id=c348feda&scoped=true

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"3c8b867e-vue-loader-template"}!./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/babel-loader/lib!./node_modules/vue-loader/lib/loaders/templateLoader.js??ref--6!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/StageSelector.vue?vue&type=template&id=0b55d490&scoped=true
var StageSelectorvue_type_template_id_0b55d490_scoped_true_render = function render() {
  var _vm = this,
    _c = _vm._self._c;
  return _c('div', [_c('div', {
    ref: "selector"
  }), _c('div', {
    ref: "dragHandle",
    attrs: {
      "id": "responsiveHandle"
    },
    on: {
      "mousedown": _vm.handleMouseDown,
      "dragstart": _vm.handleDragStart
    }
  })]);
};
var StageSelectorvue_type_template_id_0b55d490_scoped_true_staticRenderFns = [];

// CONCATENATED MODULE: ./src/StageSelector.vue?vue&type=template&id=0b55d490&scoped=true

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/StageSelector.vue?vue&type=script&lang=js
/* harmony default export */ var StageSelectorvue_type_script_lang_js = ({
  name: "StageSelector",
  data() {
    return {
      target: null,
      previewBounds: {}
    };
  },
  template: `
			<div>
				<div ref="selector"></div>
				<div ref="dragHandle" id="responsiveHandle" @mousedown="handleMouseDown" @dragstart="handleDragStart"></div>
			</div>
		`,
  watch: {
    target: function () {
      console.log('tagrrte changes');
    }
  },
  mounted() {
    // setTimeout(() => {
    // 	$($('#siteFrame')[0].contentWindow.document.body).on('mousemove', e => {
    // 		this.target = e.target;
    // 		var rect = e.target.getBoundingClientRect();
    // 		$(this.$refs.selector).css({left:rect.left, position:'absolute', right:rect.right, top:rect.top, width:rect.width+'px', height: rect.height+'px', border:'1px solid #0000ff'})
    // 		$('#stage_overlay')[0]._target = e.target
    // 		console.log($('#stage_overlay')[0]);
    // 		window.overlay = $('#stage_overlay')[0];
    // 	})
    // }, 1000);
  },
  computed: {
    styles() {
      console.log('get styles', this.target);
      if (_.isUndefined(this.target.getBoundingClientRect)) return {};
      var rect = this.target.getBoundingClientRect();
      return {
        position: 'absolute',
        left: rect.left,
        right: rect.right,
        top: rect.top,
        width: rect.width + 'px',
        height: rect.height + 'px',
        border: '1px solid #0000ff'
      };
    }
  },
  methods: {
    handleMouseDown(event) {
      $('#stage_overlay')[0].style.pointerEvents = 'all';
      this.previewBounds = this.$el.getBoundingClientRect();
      var vm = this;
      vm.$el.style.cursor = 'col-resize';
      const stageResize = _.throttle(function onMouseMove(event) {
        const currentWidth = vm.previewBounds.width;
        const newWidth = event.clientX - vm.previewBounds.x;
        const nWidth = (newWidth - currentWidth) * 2;
        const w = currentWidth + nWidth;
        document.getElementById('stagePreview').style.width = w + 'px';
      }, 50);
      const mouseUp = function () {
        document.removeEventListener('mousemove', stageResize);
        document.removeEventListener('mouseup', mouseUp);
        $('#stage_overlay')[0].style.pointerEvents = 'none';
        vm.$el.style.cursor = '';
      };

      // handle global event listeners
      document.addEventListener('mousemove', stageResize);
      document.addEventListener('mouseup', mouseUp);
    },
    handleDragStart() {
      return false;
    }
  }
});
// CONCATENATED MODULE: ./src/StageSelector.vue?vue&type=script&lang=js
 /* harmony default export */ var src_StageSelectorvue_type_script_lang_js = (StageSelectorvue_type_script_lang_js); 
// EXTERNAL MODULE: ./node_modules/vue-loader/lib/runtime/componentNormalizer.js
var componentNormalizer = __webpack_require__("2877");

// CONCATENATED MODULE: ./src/StageSelector.vue





/* normalize component */

var component = Object(componentNormalizer["a" /* default */])(
  src_StageSelectorvue_type_script_lang_js,
  StageSelectorvue_type_template_id_0b55d490_scoped_true_render,
  StageSelectorvue_type_template_id_0b55d490_scoped_true_staticRenderFns,
  false,
  null,
  "0b55d490",
  null
  
)

/* harmony default export */ var StageSelector = (component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"3c8b867e-vue-loader-template"}!./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/babel-loader/lib!./node_modules/vue-loader/lib/loaders/templateLoader.js??ref--6!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/inspector/PageSeo.vue?vue&type=template&id=0342d716&scoped=true
var PageSeovue_type_template_id_0342d716_scoped_true_render = function render() {
  var _vm = this,
    _c = _vm._self._c;
  return _c('div', [_c('div', {
    staticClass: "stickyPanel"
  }, [_c('button', {
    staticClass: "btn btn-primary btn-sm",
    on: {
      "click": _vm.save
    }
  }, [_vm._v("Save")])]), _c('InspectorPanel', {
    attrs: {
      "title": "General"
    }
  }, [_c('div', {
    staticClass: "form-group"
  }, [_c('label', {
    staticClass: "control-label",
    staticStyle: {
      "display": "block"
    }
  }, [_vm._v("Page Name "), _c('code', [_vm._v(_vm._s(_vm.nice_id))])]), _c('input', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.name,
      expression: "name"
    }],
    staticClass: "form-control",
    attrs: {
      "name": "page[name]"
    },
    domProps: {
      "value": _vm.name
    },
    on: {
      "input": function ($event) {
        if ($event.target.composing) return;
        _vm.name = $event.target.value;
      }
    }
  })]), _c('div', {
    staticClass: "form-group"
  }, [_c('label', {
    staticClass: "control-label",
    staticStyle: {
      "display": "block"
    }
  }, [_vm._v("Status")]), _c('select', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.status,
      expression: "status"
    }],
    staticClass: "form-control",
    attrs: {
      "name": "page[status]"
    },
    on: {
      "change": function ($event) {
        var $$selectedVal = Array.prototype.filter.call($event.target.options, function (o) {
          return o.selected;
        }).map(function (o) {
          var val = "_value" in o ? o._value : o.value;
          return val;
        });
        _vm.status = $event.target.multiple ? $$selectedVal : $$selectedVal[0];
      }
    }
  }, [_c('option', {
    attrs: {
      "value": "PUBLISHED"
    }
  }, [_vm._v("Published")]), _c('option', {
    attrs: {
      "value": "DRAFT"
    }
  }, [_vm._v("Draft")])])]), _vm.url !== '/' ? _c('div', {
    staticClass: "form-group"
  }, [_c('label', {
    staticClass: "control-label",
    staticStyle: {
      "display": "block"
    }
  }, [_vm._v("Slug")]), _c('Slug', {
    staticClass: "form-control",
    attrs: {
      "disabled": _vm.pageIsCollection,
      "name": "url"
    },
    model: {
      value: _vm.url,
      callback: function ($$v) {
        _vm.url = $$v;
      },
      expression: "url"
    }
  }), _c('div', [_c('svg', {
    staticStyle: {
      "width": "16px",
      "height": "16px",
      "vertical-align": "middle",
      "display": "inline-block"
    },
    attrs: {
      "fill": "none",
      "stroke-linecap": "round",
      "stroke-linejoin": "round",
      "stroke-width": "2",
      "stroke": "currentColor",
      "viewBox": "0 0 24 24"
    }
  }, [_c('path', {
    attrs: {
      "d": "M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
    }
  })]), _c('span', {
    staticStyle: {
      "color": "#666"
    }
  }, [_vm._v(_vm._s(_vm.base))]), _c('strong', [_vm._v(_vm._s(_vm.url))])]), _vm.slugChanged ? _c('div', [_c('label', {
    staticStyle: {
      "display": "flex"
    }
  }, [_c('input', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.redirect,
      expression: "redirect"
    }],
    attrs: {
      "type": "checkbox"
    },
    domProps: {
      "checked": Array.isArray(_vm.redirect) ? _vm._i(_vm.redirect, null) > -1 : _vm.redirect
    },
    on: {
      "change": function ($event) {
        var $$a = _vm.redirect,
          $$el = $event.target,
          $$c = $$el.checked ? true : false;
        if (Array.isArray($$a)) {
          var $$v = null,
            $$i = _vm._i($$a, $$v);
          if ($$el.checked) {
            $$i < 0 && (_vm.redirect = $$a.concat([$$v]));
          } else {
            $$i > -1 && (_vm.redirect = $$a.slice(0, $$i).concat($$a.slice($$i + 1)));
          }
        } else {
          _vm.redirect = $$c;
        }
      }
    }
  }), _c('span', {
    staticStyle: {
      "margin-left": "4px"
    }
  }, [_vm._v("Permanently redirect the old url to this new one")])]), !_vm.redirect ? _c('div', {
    staticClass: "alert alert-warning"
  }, [_c('svg', {
    staticStyle: {
      "width": "16px",
      "height": "16px",
      "vertical-align": "middle",
      "display": "inline-block",
      "color": "#d27400"
    },
    attrs: {
      "fill": "none",
      "stroke-linecap": "round",
      "stroke-linejoin": "round",
      "stroke-width": "2",
      "stroke": "currentColor",
      "viewBox": "0 0 24 24"
    }
  }, [_c('path', {
    attrs: {
      "d": "M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
    }
  })]), _vm._v(" Changing the slug without a redirect will break any external links to this page. If you don't yet have any existing external websites linking to this page using the url "), _c('strong', [_vm._v(_vm._s(_vm.base + _vm.original[_vm.pageUuid].url))]), _vm._v(" then it's probably safe to change ")]) : _vm._e()]) : _vm._e()], 1) : _vm._e(), _c('div', {
    staticClass: "form-group"
  }, [_c('label', {
    staticClass: "control-label",
    staticStyle: {
      "display": "block"
    }
  }, [_vm._v("Page Template")]), _c('input', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.template,
      expression: "template"
    }],
    staticClass: "form-control",
    attrs: {
      "name": "page[template]"
    },
    domProps: {
      "value": _vm.template
    },
    on: {
      "input": function ($event) {
        if ($event.target.composing) return;
        _vm.template = $event.target.value;
      }
    }
  })]), _vm.pageIsCollection ? _c('div', [_vm.pageIsCollection ? _c('div', {
    staticClass: "alert alert-info"
  }, [_vm._v(" This page is a collection template and is responsible for all items of " + _vm._s(_vm.page().collection_class) + ". Changes here will affect all pages that use this template. ")]) : _vm._e()]) : _vm._e()]), _c('InspectorPanel', {
    attrs: {
      "title": "SEO Settings"
    }
  }, [_c('div', {
    staticClass: "form-group"
  }, [_c('label', {
    staticClass: "control-label",
    staticStyle: {
      "display": "block"
    },
    attrs: {
      "disabled": _vm.og_title_use_seo
    }
  }, [_vm._v("Exclude this page from search engines")]), _c('input', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.og_title,
      expression: "og_title"
    }],
    staticClass: "form-control",
    attrs: {
      "disabled": _vm.og_title_use_seo,
      "name": "page[og_title]"
    },
    domProps: {
      "value": _vm.og_title
    },
    on: {
      "input": function ($event) {
        if ($event.target.composing) return;
        _vm.og_title = $event.target.value;
      }
    }
  }), _c('label', [_c('input', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.og_title_use_seo,
      expression: "og_title_use_seo"
    }],
    attrs: {
      "type": "checkbox",
      "true-value": true,
      "false-value": false
    },
    domProps: {
      "checked": Array.isArray(_vm.og_title_use_seo) ? _vm._i(_vm.og_title_use_seo, null) > -1 : _vm.og_title_use_seo
    },
    on: {
      "change": function ($event) {
        var $$a = _vm.og_title_use_seo,
          $$el = $event.target,
          $$c = $$el.checked ? true : false;
        if (Array.isArray($$a)) {
          var $$v = null,
            $$i = _vm._i($$a, $$v);
          if ($$el.checked) {
            $$i < 0 && (_vm.og_title_use_seo = $$a.concat([$$v]));
          } else {
            $$i > -1 && (_vm.og_title_use_seo = $$a.slice(0, $$i).concat($$a.slice($$i + 1)));
          }
        } else {
          _vm.og_title_use_seo = $$c;
        }
      }
    }
  }), _vm._v(" Same as SEO Title")])]), _c('p', [_vm._v("Specify the page's title and description.")]), _c('div', {
    staticClass: "form-group"
  }, [_c('label', {
    staticClass: "control-label",
    staticStyle: {
      "display": "block"
    }
  }, [_vm._v("Title Tag")]), _c('small', [_vm._v("Title the page - the optimal title length is approximately 55 characters")]), _vm.pageIsCollection ? _c('a', {
    attrs: {
      "href": ""
    },
    on: {
      "click": function ($event) {
        $event.preventDefault();
      }
    }
  }, [_vm._v("+ Field")]) : _vm._e(), _vm.pageIsCollection ? _c('select', {
    staticClass: "form-control",
    staticStyle: {
      "font-size": "10px"
    },
    on: {
      "change": function ($event) {
        return _vm.addFieldToSeoTitle($event);
      }
    }
  }, [_c('option', {
    attrs: {
      "value": ""
    }
  }, [_vm._v("+ Field")]), _vm._l(_vm.members, function (member) {
    return _c('option', {
      domProps: {
        "value": member.member_ref
      }
    }, [_vm._v(_vm._s(member.member_ref) + " - " + _vm._s(member.label))]);
  })], 2) : _vm._e(), _c('input', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.title,
      expression: "title"
    }],
    staticClass: "form-control",
    attrs: {
      "name": "page[title]"
    },
    domProps: {
      "value": _vm.title
    },
    on: {
      "input": function ($event) {
        if ($event.target.composing) return;
        _vm.title = $event.target.value;
      }
    }
  })]), _c('div', {
    staticClass: "form-group"
  }, [_c('label', {
    staticClass: "control-label",
    staticStyle: {
      "display": "block"
    }
  }, [_vm._v("Meta Description")]), _c('small', [_vm._v("Describe the page - the optimal description length is 155 to 300 characters")]), _vm.pageIsCollection ? _c('a', {
    attrs: {
      "href": ""
    },
    on: {
      "click": function ($event) {
        $event.preventDefault();
      }
    }
  }, [_vm._v("+ Field")]) : _vm._e(), _c('textarea', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.description,
      expression: "description"
    }],
    staticClass: "form-control",
    attrs: {
      "name": "page[description]"
    },
    domProps: {
      "value": _vm.description
    },
    on: {
      "input": function ($event) {
        if ($event.target.composing) return;
        _vm.description = $event.target.value;
      }
    }
  })]), _c('div', {
    staticClass: "form-group"
  }, [_c('label', {
    staticClass: "control-label",
    staticStyle: {
      "display": "block"
    }
  }, [_vm._v("Preview: See how this will typically look in search engines below.")]), _vm.pageIsCollection ? _c('div', {
    staticClass: "dispay:inline-flex"
  }, [_c('a', {
    attrs: {
      "href": ""
    },
    on: {
      "click": function ($event) {
        $event.preventDefault();
        return _vm.prev.apply(null, arguments);
      }
    }
  }, [_vm._v(" << ")]), _vm._v(" "), _c('a', {
    attrs: {
      "href": ""
    },
    on: {
      "click": function ($event) {
        $event.preventDefault();
        return _vm.next.apply(null, arguments);
      }
    }
  }, [_vm._v(" >> ")])]) : _vm._e(), _c('div', {
    staticClass: "seoPreview"
  }, [_c('div', {
    staticClass: "seoPreview_title"
  }, [_vm._v(_vm._s(_vm._f("truncate")(_vm.replaceFields(_vm.title), 55, '...')))]), _c('div', {
    staticClass: "seoPreview_url"
  }, [_vm._v(_vm._s(_vm.fullUrl))]), _c('div', {
    staticClass: "seoPreview_desc"
  }, [_vm._v(_vm._s(_vm._f("truncate")(_vm.replaceFields(_vm.description), 160, '...')))])]), _c('div', [_vm._v("This uses the typical character limits for Google search result pages on desktop. Search engines do experiment with their character limits, and may decide to show different content.")])])]), _c('InspectorPanel', {
    attrs: {
      "title": "Open Graph Settings"
    }
  }, [_c('p', [_vm._v("The info that shows up when sharing content on Facebook, Twitter, LinkedIn, Pinterest, and Google+.")]), _c('div', {
    staticClass: "form-group"
  }, [_c('label', {
    staticClass: "control-label",
    staticStyle: {
      "display": "block"
    },
    attrs: {
      "disabled": _vm.og_title_use_seo
    }
  }, [_vm._v("Open Graph Title")]), _c('input', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.og_title,
      expression: "og_title"
    }],
    staticClass: "form-control",
    attrs: {
      "disabled": _vm.og_title_use_seo,
      "name": "page[og_title]"
    },
    domProps: {
      "value": _vm.og_title
    },
    on: {
      "input": function ($event) {
        if ($event.target.composing) return;
        _vm.og_title = $event.target.value;
      }
    }
  }), _c('label', [_c('input', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.og_title_use_seo,
      expression: "og_title_use_seo"
    }],
    attrs: {
      "type": "checkbox",
      "true-value": true,
      "false-value": false
    },
    domProps: {
      "checked": Array.isArray(_vm.og_title_use_seo) ? _vm._i(_vm.og_title_use_seo, null) > -1 : _vm.og_title_use_seo
    },
    on: {
      "change": function ($event) {
        var $$a = _vm.og_title_use_seo,
          $$el = $event.target,
          $$c = $$el.checked ? true : false;
        if (Array.isArray($$a)) {
          var $$v = null,
            $$i = _vm._i($$a, $$v);
          if ($$el.checked) {
            $$i < 0 && (_vm.og_title_use_seo = $$a.concat([$$v]));
          } else {
            $$i > -1 && (_vm.og_title_use_seo = $$a.slice(0, $$i).concat($$a.slice($$i + 1)));
          }
        } else {
          _vm.og_title_use_seo = $$c;
        }
      }
    }
  }), _vm._v(" Same as SEO Title")])]), _c('div', {
    staticClass: "form-group"
  }, [_c('label', {
    staticClass: "control-label",
    staticStyle: {
      "display": "block"
    },
    attrs: {
      "disabled": _vm.og_description_use_seo
    }
  }, [_vm._v("Open Graph Description")]), _c('textarea', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.og_description,
      expression: "og_description"
    }],
    staticClass: "form-control",
    attrs: {
      "disabled": _vm.og_description_use_seo,
      "name": "page[og_description]"
    },
    domProps: {
      "value": _vm.og_description
    },
    on: {
      "input": function ($event) {
        if ($event.target.composing) return;
        _vm.og_description = $event.target.value;
      }
    }
  }), _c('label', [_c('input', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.og_description_use_seo,
      expression: "og_description_use_seo"
    }],
    attrs: {
      "type": "checkbox"
    },
    domProps: {
      "checked": Array.isArray(_vm.og_description_use_seo) ? _vm._i(_vm.og_description_use_seo, null) > -1 : _vm.og_description_use_seo
    },
    on: {
      "change": function ($event) {
        var $$a = _vm.og_description_use_seo,
          $$el = $event.target,
          $$c = $$el.checked ? true : false;
        if (Array.isArray($$a)) {
          var $$v = null,
            $$i = _vm._i($$a, $$v);
          if ($$el.checked) {
            $$i < 0 && (_vm.og_description_use_seo = $$a.concat([$$v]));
          } else {
            $$i > -1 && (_vm.og_description_use_seo = $$a.slice(0, $$i).concat($$a.slice($$i + 1)));
          }
        } else {
          _vm.og_description_use_seo = $$c;
        }
      }
    }
  }), _vm._v(" Same as SEO Description")])]), _c('div', {
    staticClass: "form-group"
  }, [_c('label', {
    staticClass: "control-label",
    staticStyle: {
      "display": "block"
    }
  }, [_vm._v("Open Graph Image URL")]), !_vm.pageIsCollection ? _c('div', [_c('small', {
    staticStyle: {
      "display": "block"
    }
  }, [_vm._v("Make sure your images are at least 1200px by 630px and have a 1.91:1 aspect ratio.")]), _c('button', {
    staticClass: "btn btn-default",
    on: {
      "click": _vm.setOgImage
    }
  }, [_vm._v("Select Image")])]) : _c('div', [_vm._v(" Select an image field from " + _vm._s(_vm.classData.definition.label) + " table "), _c('select', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.og_image,
      expression: "og_image"
    }],
    staticClass: "form-control",
    on: {
      "change": function ($event) {
        var $$selectedVal = Array.prototype.filter.call($event.target.options, function (o) {
          return o.selected;
        }).map(function (o) {
          var val = "_value" in o ? o._value : o.value;
          return val;
        });
        _vm.og_image = $event.target.multiple ? $$selectedVal : $$selectedVal[0];
      }
    }
  }, [_c('option', {
    attrs: {
      "disabled": "",
      "value": ""
    }
  }, [_vm._v("Select an image field")]), _vm._l(_vm.classImageFields, function (imageField) {
    return _c('option', {
      domProps: {
        "value": '{{' + imageField.member_ref + '}}'
      }
    }, [_vm._v(_vm._s(imageField.label))]);
  })], 2)])]), _vm.pageIsCollection ? _c('div', {
    staticClass: "dispay:inline-flex"
  }, [_c('a', {
    attrs: {
      "href": ""
    },
    on: {
      "click": function ($event) {
        $event.preventDefault();
        return _vm.prev.apply(null, arguments);
      }
    }
  }, [_vm._v(" << ")]), _vm._v(" "), _c('a', {
    attrs: {
      "href": ""
    },
    on: {
      "click": function ($event) {
        $event.preventDefault();
        return _vm.next.apply(null, arguments);
      }
    }
  }, [_vm._v(" >> ")])]) : _vm._e(), _c('div', {
    staticClass: "seoPreview"
  }, [_c('img', {
    staticStyle: {
      "width": "100%"
    },
    attrs: {
      "src": _vm.ogImageUrl
    }
  }), _c('div', {
    staticClass: "ogPreview_title",
    staticStyle: {
      "margin-top": "5px",
      "line-height": "18px",
      "overflow-wrap": "break-word"
    }
  }, [_vm._v(_vm._s(_vm._f("truncate")(_vm.replaceFields(_vm.og_title), 90, '...')))]), _c('div', {
    staticClass: "ogPreview_desc",
    staticStyle: {
      "margin-top": "4px",
      "font-size": "12px",
      "line-height": "14px",
      "overflow-wrap": "break-word"
    }
  }, [_vm._v(_vm._s(_vm._f("truncate")(_vm.replaceFields(_vm.og_description), 300, '...')))]), _c('div', {
    staticClass: "ogPreview_url",
    staticStyle: {
      "margin-top": "4px",
      "color": "#8A8A8A",
      "font-size": "12px",
      "line-height": "12px",
      "overflow-wrap": "break-word"
    }
  }, [_vm._v(_vm._s(_vm.og_url))])]), _c('div', [_vm._v("Open graph preview - note that if no image is defined then one will most likely be taken from the content on the page")])]), _c('InspectorPanel', {
    attrs: {
      "title": "Custom Code"
    }
  }, [_c('p', [_vm._v("The code included here will only apply to this page, and will be appear after any site-wide custom code defined in settings.")]), _c('div', {
    staticClass: "form-group"
  }, [_c('label', {
    staticClass: "control-label"
  }, [_vm._v("Inside the <head> tag")]), _c('neon-input-ide', {
    attrs: {
      "lang": "html"
    },
    model: {
      value: _vm.code_head,
      callback: function ($$v) {
        _vm.code_head = $$v;
      },
      expression: "code_head"
    }
  }), _c('div', {
    staticClass: "alert alert-warning",
    staticStyle: {
      "padding": "4px",
      "margin": "4px 0 4px 0",
      "line-height": "normal"
    }
  }, [_c('small', [_vm._v("Custom code is not validated. Incorrect code could break the website")]), _c('br'), _c('small', [_vm._v("Make sure the code is not a security risk - code here could expose sensitive information")])])], 1), _c('div', {
    staticClass: "form-group"
  }, [_c('label', {
    staticClass: "control-label"
  }, [_vm._v("Before the </body> tag")]), _c('neon-input-ide', {
    attrs: {
      "lang": "html"
    },
    model: {
      value: _vm.code_body,
      callback: function ($$v) {
        _vm.code_body = $$v;
      },
      expression: "code_body"
    }
  }), _c('div', {
    staticClass: "alert alert-warning",
    staticStyle: {
      "padding": "4px",
      "margin": "4px 0 4px 0",
      "line-height": "normal"
    }
  }, [_c('small', [_vm._v("Custom code is not validated. Incorrect code could break the website")]), _c('br'), _c('small', [_vm._v("Make sure the code is not a security risk - code here could expose sensitive information")])])], 1)])], 1);
};
var PageSeovue_type_template_id_0342d716_scoped_true_staticRenderFns = [];

// CONCATENATED MODULE: ./src/inspector/PageSeo.vue?vue&type=template&id=0342d716&scoped=true

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"3c8b867e-vue-loader-template"}!./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/babel-loader/lib!./node_modules/vue-loader/lib/loaders/templateLoader.js??ref--6!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/inspector/InspectorPanel.vue?vue&type=template&id=16bcd466&scoped=true
var InspectorPanelvue_type_template_id_16bcd466_scoped_true_render = function render() {
  var _vm = this,
    _c = _vm._self._c;
  return _c('div', [_c('div', {
    staticClass: "inspector_heading",
    on: {
      "click": function ($event) {
        _vm.open = !_vm.open;
      }
    }
  }, [_vm._v(" " + _vm._s(_vm.title) + " ")]), _c('div', {
    directives: [{
      name: "show",
      rawName: "v-show",
      value: _vm.open,
      expression: "open"
    }],
    staticClass: "inspector_panel"
  }, [_vm._t("default")], 2)]);
};
var InspectorPanelvue_type_template_id_16bcd466_scoped_true_staticRenderFns = [];

// CONCATENATED MODULE: ./src/inspector/InspectorPanel.vue?vue&type=template&id=16bcd466&scoped=true

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/inspector/InspectorPanel.vue?vue&type=script&lang=js
/* harmony default export */ var InspectorPanelvue_type_script_lang_js = ({
  name: 'InspectorPanel',
  props: {
    startOpen: {
      type: Boolean,
      default: true
    },
    title: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      open: this.startOpen
    };
  }
});
// CONCATENATED MODULE: ./src/inspector/InspectorPanel.vue?vue&type=script&lang=js
 /* harmony default export */ var inspector_InspectorPanelvue_type_script_lang_js = (InspectorPanelvue_type_script_lang_js); 
// EXTERNAL MODULE: ./src/inspector/InspectorPanel.vue?vue&type=style&index=0&id=16bcd466&prod&scoped=true&lang=css
var InspectorPanelvue_type_style_index_0_id_16bcd466_prod_scoped_true_lang_css = __webpack_require__("38b3");

// CONCATENATED MODULE: ./src/inspector/InspectorPanel.vue






/* normalize component */

var InspectorPanel_component = Object(componentNormalizer["a" /* default */])(
  inspector_InspectorPanelvue_type_script_lang_js,
  InspectorPanelvue_type_template_id_16bcd466_scoped_true_render,
  InspectorPanelvue_type_template_id_16bcd466_scoped_true_staticRenderFns,
  false,
  null,
  "16bcd466",
  null
  
)

/* harmony default export */ var InspectorPanel = (InspectorPanel_component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"3c8b867e-vue-loader-template"}!./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/babel-loader/lib!./node_modules/vue-loader/lib/loaders/templateLoader.js??ref--6!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/inspector/Slug.vue?vue&type=template&id=502217f6&scoped=true
var Slugvue_type_template_id_502217f6_scoped_true_render = function render() {
  var _vm = this,
    _c = _vm._self._c;
  return _c('input', {
    attrs: {
      "type": "text"
    },
    domProps: {
      "value": _vm.update
    },
    on: {
      "blur": _vm.trim,
      "input": function ($event) {
        _vm.update = $event.target.value;
      }
    }
  });
};
var Slugvue_type_template_id_502217f6_scoped_true_staticRenderFns = [];

// CONCATENATED MODULE: ./src/inspector/Slug.vue?vue&type=template&id=502217f6&scoped=true

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/inspector/Slug.vue?vue&type=script&lang=js
/* harmony default export */ var Slugvue_type_script_lang_js = ({
  name: "Slug",
  props: {
    value: {
      type: String,
      default: ''
    }
  },
  mounted: function () {
    this.isNew = this.modelValue ? false : true;
    this.startValue = this.modelValue;
  },
  methods: {
    trim: function () {
      if (this.value.endsWith('/')) {
        this.update = this.value.slice(0, -1);
      }
    }
  },
  computed: {
    update: {
      get: function () {
        return this.value;
      },
      set: function (value) {
        if (value[0] !== '/') value = '/' + value;
        this.$emit('input', value.replace(/\s+/g, '-').toLowerCase());
      }
    }
  }
});
// CONCATENATED MODULE: ./src/inspector/Slug.vue?vue&type=script&lang=js
 /* harmony default export */ var inspector_Slugvue_type_script_lang_js = (Slugvue_type_script_lang_js); 
// CONCATENATED MODULE: ./src/inspector/Slug.vue





/* normalize component */

var Slug_component = Object(componentNormalizer["a" /* default */])(
  inspector_Slugvue_type_script_lang_js,
  Slugvue_type_template_id_502217f6_scoped_true_render,
  Slugvue_type_template_id_502217f6_scoped_true_staticRenderFns,
  false,
  null,
  "502217f6",
  null
  
)

/* harmony default export */ var Slug = (Slug_component.exports);
// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");
var external_commonjs_vue_commonjs2_vue_root_Vue_default = /*#__PURE__*/__webpack_require__.n(external_commonjs_vue_commonjs2_vue_root_Vue_);

// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/inspector/PageSeo.vue?vue&type=script&lang=js



/* harmony default export */ var PageSeovue_type_script_lang_js = ({
  name: 'PageSeo',
  components: {
    InspectorPanel: InspectorPanel,
    Slug: Slug
  },
  props: {
    pageUuid: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      // store original page objected indexed by uuid
      original: {},
      redirect: true,
      // the index in data we are using for the preview
      previewIndex: 0,
      classData: {
        // list of all rows
        data: [],
        // the class definition with members
        definition: {
          members: {}
        }
      }
    };
  },
  watch: {
    pageUuid: function () {
      this.getClassFields();
      if (!this.original[this.pageUuid]) {
        external_commonjs_vue_commonjs2_vue_root_Vue_default.a.set(this.original, this.pageUuid, Object.assign({}, this.page()));
      }
    },
    previewIndex: function () {
      window.cobe.iframeSrc = this.demoObject.slug;
    }
  },
  mounted() {
    window.pageseo = this;
  },
  methods: {
    addFieldToSeoTitle(event) {
      this.title = this.title + ' ' + '{{' + event.target.value + '}}';
    },
    prev() {
      if (this.previewIndex === 0) this.previewIndex = this.classData.data.length - 2;else this.previewIndex = this.previewIndex - 1;
    },
    next() {
      if (this.previewIndex === this.classData.data.length - 2) this.previewIndex = 0;else this.previewIndex = this.previewIndex + 1;
    },
    setOgImage: function () {
      FIREFLY.picker('setOgImage', file => {
        var settings = {
          cropAuto: true,
          cropWidth: 1.91,
          cropHeight: 1
        };
        FIREFLY.edit(file, settings, newFile => {
          this.og_image = newFile.id;
          // make compatible with v-model
        });
        this.og_image = file.id;
      });
    },
    save() {
      //save the page data
      this.$store.dispatch('editor/updatePage', {
        page: this.page(),
        redirect: this.redirect
      }).then(result => {
        this.original[result.id] = Object.assign({}, result);
      });
    },
    page() {
      if (!this.$store.state.editor.pages[this.pageUuid]) {
        return {
          title: '',
          description: ''
        };
      }
      return this.$store.state.editor.pages[this.pageUuid];
    },
    getClassFields() {
      if (!this.page().collection) return [];
      if (!_.has(this.page(), 'collection_class')) return [];
      this.loadClass().then(data => {
        this.classData = data;
      });
      // load class definition
    },
    loadClass() {
      return new Promise((resolve, reject) => {
        let url = neon.url('/daedalus/api/class/list', {
          class: this.page().collection_class
        });
        $.ajax({
          type: "POST",
          url: url,
          dataType: 'json'
        }).done(resolve).fail(reject);
      });
    },
    replaceFields: function (str) {
      if (!this.page().collection) return str;
      if (_.isEmpty(str)) return str;
      var demoObject = this.demoObject;
      // replace tags in string
      // this needs to be the post
      _.each(demoObject, (value, key) => {
        if (_.isString(value)) {
          str = str.replace(new RegExp('{{' + key + '}}', 'g'), value);
        }
      });
      return str;
    }
  },
  computed: {
    members() {
      return this.classData.definition.members;
    },
    classImageFields() {
      return _.filter(this.classData.definition.members, function (member) {
        return member.data_type_ref === 'image_ref';
      });
    },
    pageIsCollection() {
      return this.page().collection == 1;
    },
    demoObject() {
      return this.classData.data[this.previewIndex];
    },
    slugChanged() {
      if (_.isUndefined(this.original[this.pageUuid])) return false;
      return this.original[this.pageUuid].url !== this.url;
    },
    nice_id: {
      set: function (val) {
        this.$store.commit('editor/updatePageAttr', {
          uuid: this.pageUuid,
          name: 'nice_id',
          value: val
        });
      },
      get: function () {
        return this.page().nice_id;
      }
    },
    status: {
      set: function (val) {
        this.$store.commit('editor/updatePageAttr', {
          uuid: this.pageUuid,
          name: 'status',
          value: val
        });
      },
      get: function () {
        return this.page().status;
      }
    },
    name: {
      set: function (val) {
        this.$store.commit('editor/updatePageAttr', {
          uuid: this.pageUuid,
          name: 'name',
          value: val
        });
      },
      get: function () {
        return this.page().name;
      }
    },
    template: {
      set: function (val) {
        this.$store.commit('editor/updatePageAttr', {
          uuid: this.pageUuid,
          name: 'template',
          value: val
        });
      },
      get: function () {
        return this.page().template;
      }
    },
    url: {
      set: function (val) {
        this.$store.commit('editor/updatePageAttr', {
          uuid: this.pageUuid,
          name: 'url',
          value: val
        });
      },
      get: function () {
        return this.page().url;
      }
    },
    title: {
      set: function (val) {
        this.$store.commit('editor/updatePageTitle', {
          uuid: this.pageUuid,
          title: val
        });
      },
      get: function () {
        return this.page().title;
      }
    },
    description: {
      set: function (val) {
        this.$store.commit('editor/updatePageDescription', {
          uuid: this.pageUuid,
          description: val
        });
      },
      get: function () {
        return this.page().description;
      }
    },
    og_title: {
      set: function (val) {
        this.$store.commit('editor/updatePageAttr', {
          uuid: this.pageUuid,
          name: 'og_title',
          value: val
        });
      },
      get: function () {
        return this.og_title_use_seo ? this.page().title : this.page().og_title;
      }
    },
    og_description: {
      set: function (val) {
        this.$store.commit('editor/updatePageAttr', {
          uuid: this.pageUuid,
          name: 'og_description',
          value: val
        });
      },
      get: function () {
        return this.og_description_use_seo ? this.page().description : this.page().og_description;
      }
    },
    og_image: {
      set: function (val) {
        this.$store.commit('editor/updatePageAttr', {
          uuid: this.pageUuid,
          name: 'og_image',
          value: val
        });
      },
      get: function () {
        return this.page().og_image;
      }
    },
    og_description_use_seo: {
      set: function (val) {
        this.$store.commit('editor/updatePageAttr', {
          uuid: this.pageUuid,
          name: 'og_description_use_seo',
          value: val
        });
      },
      get: function () {
        return this.page().og_description_use_seo;
      }
    },
    og_title_use_seo: {
      set: function (val) {
        this.$store.commit('editor/updatePageAttr', {
          uuid: this.pageUuid,
          name: 'og_title_use_seo',
          value: val
        });
      },
      get: function () {
        return this.page().og_title_use_seo;
      }
    },
    code_head: {
      set: function (val) {
        this.$store.commit('editor/updatePageAttr', {
          uuid: this.pageUuid,
          name: 'code_head',
          value: val
        });
      },
      get: function () {
        return this.page().code_head;
      }
    },
    code_body: {
      set: function (val) {
        this.$store.commit('editor/updatePageAttr', {
          uuid: this.pageUuid,
          name: 'code_body',
          value: val
        });
      },
      get: function () {
        return this.page().code_body;
      }
    },
    og_url() {
      return this.fullUrl;
    },
    ogImageUrl() {
      return FIREFLY.getImageUrl(this.replaceFields(this.page().og_image));
    },
    fullUrl() {
      let url = '';
      if (this.pageIsCollection) {
        if (_.isUndefined(this.demoObject)) return '';
        url = this.demoObject.slug;
      } else if (this.url) {
        url = this.url;
      }
      return neon.url(url, false, true).replace(/\/$/, "");
    },
    base() {
      return neon.url('', undefined, true).replace(/\/$/, "");
    }
  },
  filters: {
    truncate: function (text, length, suffix) {
      if (typeof text === 'string') {
        if (text.length <= length) return text;
        return text.substring(0, length) + suffix;
      }
      return '';
    }
  }
});
// CONCATENATED MODULE: ./src/inspector/PageSeo.vue?vue&type=script&lang=js
 /* harmony default export */ var inspector_PageSeovue_type_script_lang_js = (PageSeovue_type_script_lang_js); 
// EXTERNAL MODULE: ./src/inspector/PageSeo.vue?vue&type=style&index=0&id=0342d716&prod&scoped=true&lang=css
var PageSeovue_type_style_index_0_id_0342d716_prod_scoped_true_lang_css = __webpack_require__("88dc");

// CONCATENATED MODULE: ./src/inspector/PageSeo.vue






/* normalize component */

var PageSeo_component = Object(componentNormalizer["a" /* default */])(
  inspector_PageSeovue_type_script_lang_js,
  PageSeovue_type_template_id_0342d716_scoped_true_render,
  PageSeovue_type_template_id_0342d716_scoped_true_staticRenderFns,
  false,
  null,
  "0342d716",
  null
  
)

/* harmony default export */ var PageSeo = (PageSeo_component.exports);
// CONCATENATED MODULE: ./node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/vue-loader/lib??vue-loader-options!./src/Cobe.vue?vue&type=script&lang=js




// noinspection JSAnnotator
// const pages = <?= json_encode($pages) ?>;

const devices = {
  768: ['iPad Pro (7.9")', 'iPad 2017/2018', 'iPad Air/Air 2', 'iPad 4th gen (and earlier)', 'iPad mini (all models)', 'Microsoft Surface'],
  800: ['Galaxy Note 10.1', 'Galaxy Tab'],
  1024: ['iPad Pro (12.9")', 'Galaxy Tab S3', 'Surface Pro 3 (Portrait)'],
  1280: ['Retina MacBook (12")', 'MacBook Pro (early 13" models)']
};

// Sync the stores.
// Set up two way messaging between the iframe website in the stage and the parent windows store
// window.addEventListener("message", receiveMessage, false);
//
// function receiveMessage(event) {
// 	if (event.origin !== NEON_DATA.host.replace(NEON_DATA.base, ''))
// 		return;
// 	if (event.data.name === 'neonEditorState') {
// 		console.log('received' , event.data);
// 		neon.Store.commit('editor/syncState', _.cloneDeep(event.data.state));
// 	}
// }

// set up by the view file that uses this component

/* harmony default export */ var Cobevue_type_script_lang_js = ({
  name: "Cobe",
  store: neon.Store,
  components: {
    StageSelector: StageSelector,
    PageSeo: PageSeo,
    InspectorPanel: InspectorPanel
  },
  props: {
    startPage: {
      type: String,
      default: ''
    },
    // uuid of the page to start on
    loadPages: {
      type: Object,
      default() {
        return {};
      }
    } // collection indexed by page uuid of page objects
  },
  data() {
    return {
      // page nice_id
      onPage: '',
      iframeSrc: '',
      previewBounds: {},
      inspectorWidth: 300,
      pageSearch: ''
    };
  },
  watch: {
    onPage: function (newVal, oldVal) {
      this.iframeSrc = this.pageUrl;
    }
  },
  computed: {
    rootComponent() {
      return this.$store.getters['editor/rootComponent'];
    },
    components() {
      return this.$store.getters['editor/components'];
    },
    selectedComponents() {
      var res = {};
      _.forEach(this.$store.getters['editor/getSelected'], uuid => {
        res[uuid] = this.$store.getters['editor/getComponent'](uuid);
      });
      return res;
    },
    pagesOrdered() {
      return _.sortBy(this.pages, 'url');
    },
    pages() {
      if (this.pageSearch) {
        let q = _.toLower(this.pageSearch);
        return _.filter(this.$store.getters['editor/pages'], page => {
          return _.toLower(page.name).search(q) >= 0;
        });
      }
      return this.$store.getters['editor/pages'];
    },
    page() {
      return _.find(this.pages, {
        nice_id: this.onPage
      });
    },
    page_description: {
      set: function (val) {
        this.page.description = val;
      },
      get: function () {
        return this.page.description;
      }
    },
    page_title: {
      set: function (val) {
        this.page.title = val;
      },
      get: function () {
        return this.page.title;
      }
    },
    fields() {
      return [{
        class: 'text',
        name: 'nice_id',
        id: 'nice_id',
        label: 'ID',
        value: this.page.nice_id,
        readOnly: true
      }, {
        class: 'text',
        name: 'id',
        id: 'id',
        label: 'UUID',
        value: this.page.id,
        readOnly: true
      }, {
        class: 'text',
        name: 'title',
        id: 'title',
        label: 'Title',
        value: this.page.title
      }, {
        class: 'textarea',
        name: 'description',
        id: 'description',
        label: 'SEO Description',
        value: this.page.title
      }];
    },
    pageUrl() {
      return neon.url(_.get(this.page, 'url', null));
    }
  },
  methods: {
    onPageSelected: function (page) {
      this.onPage = page.nice_id;
    },
    onResizeHandleMouseDownRight: function (e, prop) {
      const vm = this;
      const mousemove = function (e) {
        if (e.clientX <= 100) return;
        if (e.clientX >= window.innerWidth - 200) return;
        console.log(e);
        vm[prop] = window.innerWidth - e.clientX;
        if (e.stopPropagation) e.stopPropagation();
        if (e.preventDefault) e.preventDefault();
        e.cancelBubble = true;
        e.returnValue = false;
        return false;
      };
      const mouseup = function () {
        document.removeEventListener('mousemove', mousemove);
        document.removeEventListener('mouseup', this);
      };
      document.addEventListener('mousemove', mousemove);
      document.addEventListener('mouseup', mouseup);
    },
    onResizeHandleMouseDown: function (e, prop) {
      const vm = this;
      const mousemove = function (e) {
        if (e.clientX <= 100) return;
        if (e.clientX >= window.innerWidth - 200) return;
        vm[prop] = e.clientX;
        if (e.stopPropagation) e.stopPropagation();
        if (e.preventDefault) e.preventDefault();
        e.cancelBubble = true;
        e.returnValue = false;
        return false;
      };
      const mouseup = function () {
        document.removeEventListener('mousemove', mousemove);
        document.removeEventListener('mouseup', this);
      };
      document.addEventListener('mousemove', mousemove);
      document.addEventListener('mouseup', mouseup);
    },
    onUpdateComponentProps: function (uuid, $event) {
      let props;
      try {
        props = JSON.parse($event.target.value);
      } catch (e) {
        return false;
      }
      this.$store.commit('editor/updateComponentProps', {
        uuid: uuid,
        props: props
      });
    },
    componentProps(cmp, component) {
      var c = _.cloneDeep(component);
      _.defaults(c.props, neon.phoebe.getCmpDefault(cmp));
      return c;
    },
    homePage() {
      return _.find(this.pages, {
        url: '/'
      });
    },
    frameLoad() {
      var frameUrl = this.$refs.siteFrame.contentWindow.location.pathname.replace(neon.base(), '');
      let page = _.find(this.pages, {
        url: frameUrl
      });
      if (page) this.onPage = page.nice_id;
      return true;
    }
  },
  created() {
    this.$store.commit('editor/setPages', {
      pages: this.loadPages
    });
  },
  mounted() {
    this.$store.subscribe((mutation, state) => {
      console.log(state.editor, mutation);
      var m = JSON.parse(JSON.stringify(mutation));
      this.$refs.siteFrame.contentWindow.postMessage({
        name: 'neonEditorToFrame',
        state: state.editor,
        m
      }, '*');
    });
    this.onPage = this.startPage;
    // load the homepage if it exists and no id is defined
    if (this.onPage === '') {
      let homePage = this.homePage();
      if (homePage) {
        this.onPage = homePage.nice_id;
        this.iframeSrc = this.pageUrl;
      }
    }
    window.cobe = this;
    var vm = this;

    // apply a hover effect to elements in stage window:
    // currently under development
    // works well but requires scroll position to be handled
  }
});
// CONCATENATED MODULE: ./src/Cobe.vue?vue&type=script&lang=js
 /* harmony default export */ var src_Cobevue_type_script_lang_js = (Cobevue_type_script_lang_js); 
// EXTERNAL MODULE: ./src/Cobe.vue?vue&type=style&index=0&id=c348feda&prod&scoped=true&lang=css
var Cobevue_type_style_index_0_id_c348feda_prod_scoped_true_lang_css = __webpack_require__("ca4d");

// CONCATENATED MODULE: ./src/Cobe.vue






/* normalize component */

var Cobe_component = Object(componentNormalizer["a" /* default */])(
  src_Cobevue_type_script_lang_js,
  render,
  staticRenderFns,
  false,
  null,
  "c348feda",
  null
  
)

/* harmony default export */ var Cobe = __webpack_exports__["a"] = (Cobe_component.exports);

/***/ }),

/***/ "8875":
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;// addapted from the document.currentScript polyfill by Adam Miller
// MIT license
// source: https://github.com/amiller-gh/currentScript-polyfill

// added support for Firefox https://bugzilla.mozilla.org/show_bug.cgi?id=1620505

(function (root, factory) {
  if (true) {
    !(__WEBPACK_AMD_DEFINE_ARRAY__ = [], __WEBPACK_AMD_DEFINE_FACTORY__ = (factory),
				__WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ?
				(__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__)) : __WEBPACK_AMD_DEFINE_FACTORY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
  } else {}
}(typeof self !== 'undefined' ? self : this, function () {
  function getCurrentScript () {
    var descriptor = Object.getOwnPropertyDescriptor(document, 'currentScript')
    // for chrome
    if (!descriptor && 'currentScript' in document && document.currentScript) {
      return document.currentScript
    }

    // for other browsers with native support for currentScript
    if (descriptor && descriptor.get !== getCurrentScript && document.currentScript) {
      return document.currentScript
    }
  
    // IE 8-10 support script readyState
    // IE 11+ & Firefox support stack trace
    try {
      throw new Error();
    }
    catch (err) {
      // Find the second match for the "at" string to get file src url from stack.
      var ieStackRegExp = /.*at [^(]*\((.*):(.+):(.+)\)$/ig,
        ffStackRegExp = /@([^@]*):(\d+):(\d+)\s*$/ig,
        stackDetails = ieStackRegExp.exec(err.stack) || ffStackRegExp.exec(err.stack),
        scriptLocation = (stackDetails && stackDetails[1]) || false,
        line = (stackDetails && stackDetails[2]) || false,
        currentLocation = document.location.href.replace(document.location.hash, ''),
        pageSource,
        inlineScriptSourceRegExp,
        inlineScriptSource,
        scripts = document.getElementsByTagName('script'); // Live NodeList collection
  
      if (scriptLocation === currentLocation) {
        pageSource = document.documentElement.outerHTML;
        inlineScriptSourceRegExp = new RegExp('(?:[^\\n]+?\\n){0,' + (line - 2) + '}[^<]*<script>([\\d\\D]*?)<\\/script>[\\d\\D]*', 'i');
        inlineScriptSource = pageSource.replace(inlineScriptSourceRegExp, '$1').trim();
      }
  
      for (var i = 0; i < scripts.length; i++) {
        // If ready state is interactive, return the script tag
        if (scripts[i].readyState === 'interactive') {
          return scripts[i];
        }
  
        // If src matches, return the script tag
        if (scripts[i].src === scriptLocation) {
          return scripts[i];
        }
  
        // If inline source matches, return the script tag
        if (
          scriptLocation === currentLocation &&
          scripts[i].innerHTML &&
          scripts[i].innerHTML.trim() === inlineScriptSource
        ) {
          return scripts[i];
        }
      }
  
      // If no match, return null
      return null;
    }
  };

  return getCurrentScript
}));


/***/ }),

/***/ "88dc":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_PageSeo_vue_vue_type_style_index_0_id_0342d716_prod_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("96ef");
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_PageSeo_vue_vue_type_style_index_0_id_0342d716_prod_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_PageSeo_vue_vue_type_style_index_0_id_0342d716_prod_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */


/***/ }),

/***/ "8bbf":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE__8bbf__;

/***/ }),

/***/ "8cce":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_NeonCmp_vue_vue_type_style_index_0_id_2e4cc226_prod_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("e146");
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_NeonCmp_vue_vue_type_style_index_0_id_2e4cc226_prod_lang_css__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_NeonCmp_vue_vue_type_style_index_0_id_2e4cc226_prod_lang_css__WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */


/***/ }),

/***/ "941d":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_CmpTreeNode_vue_vue_type_style_index_0_id_73b94e4d_prod_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("722a");
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_CmpTreeNode_vue_vue_type_style_index_0_id_73b94e4d_prod_lang_css__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_CmpTreeNode_vue_vue_type_style_index_0_id_73b94e4d_prod_lang_css__WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */


/***/ }),

/***/ "96ef":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "98ff":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "b4c0":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_CmpTree_vue_vue_type_style_index_0_id_66496a22_prod_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("ce43");
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_CmpTree_vue_vue_type_style_index_0_id_66496a22_prod_lang_css__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_CmpTree_vue_vue_type_style_index_0_id_66496a22_prod_lang_css__WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */


/***/ }),

/***/ "bd04":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "c0d6":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "a", function() { return GETTERS; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "b", function() { return MUTATIONS; });
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("8bbf");
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);

const GETTERS = {
  isSelected: 'editor/isSelected',
  isHovering: 'editor/isHovering',
  getSelected: 'editor/getSelected',
  components: 'editor/components',
  getComponent: 'editor/getComponent',
  isEditMode: 'editor/isEditMode',
  getChildren: 'editor/getChildren',
  getAvailableComponents: 'editor/getAvailableComponents',
  getAvailableComponentsFor: 'editor/getAvailableComponentsFor',
  getComponentConfig: 'editor/getComponentConfig',
  rootComponent: 'editor/rootComponent'
};
const MUTATIONS = {
  deleteComponents: 'editor/deleteComponents',
  setComponents: 'editor/setComponents',
  addComponent: 'editor/addComponent',
  setEditMode: 'editor/setEditMode',
  removeChild: 'editor/removeChild',
  updateComponentProps: 'editor/updateComponentProps'
};
const initialState = function () {
  return {
    pages: {},
    root: 'root',
    /**
     * Indexed list of components
     * indexed by components uuid
     */
    components: {},
    componentTree: {},
    /**
     * indexed list of selected components:
     * ```js
     * { "cDoeMj5S12Aprbcu45fDsw": true }
     * ```
     * {Object}
     */
    selected: {},
    /**
     * indexed list of hover components
     * { "cDoeMj5S12Aprbcu45fDsw": true }
     */
    hover: {},
    /**
     * Whether we are in edit mode
     * @type Boolean
     */
    editMode: true,
    /**
     * List of component configurations
     * available for use with the editor
     */
    availableComponents: {
      'Root': {
        icon: '<svg width="24" height="24" data-icon="sitemap" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M128 352H32c-17.67 0-32 14.33-32 32v96c0 17.67 14.33 32 32 32h96c17.67 0 32-14.33 32-32v-96c0-17.67-14.33-32-32-32zm-24-80h192v48h48v-48h192v48h48v-57.59c0-21.17-17.23-38.41-38.41-38.41H344v-64h40c17.67 0 32-14.33 32-32V32c0-17.67-14.33-32-32-32H256c-17.67 0-32 14.33-32 32v96c0 17.67 14.33 32 32 32h40v64H94.41C73.23 224 56 241.23 56 262.41V320h48v-48zm264 80h-96c-17.67 0-32 14.33-32 32v96c0 17.67 14.33 32 32 32h96c17.67 0 32-14.33 32-32v-96c0-17.67-14.33-32-32-32zm240 0h-96c-17.67 0-32 14.33-32 32v96c0 17.67 14.33 32 32 32h96c17.67 0 32-14.33 32-32v-96c0-17.67-14.33-32-32-32z"></path></svg>'
      },
      // Layout
      "NeonSection": {
        category: 'layout'
      },
      'NeonContainer': {
        category: 'layout'
      },
      'NeonColumns': {
        title: 'Columns',
        icon: '<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true" focusable="false"><path fill="none" d="M0 0h24v24H0V0z"></path><g><path d="M21 4H3L2 5v14l1 1h18l1-1V5l-1-1zM8 18H4V6h4v12zm6 0h-4V6h4v12zm6 0h-4V6h4v12z"></path></g></svg>',
        category: 'layout',
        only: ['NeonColumn']
      },
      'NeonColumn': {
        title: 'Column',
        icon: '<svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" role="img" aria-hidden="true" focusable="false"><path fill="none" d="M0 0h24v24H0V0z"></path><path d="M11.99 18.54l-7.37-5.73L3 14.07l9 7 9-7-1.63-1.27zM12 16l7.36-5.73L21 9l-9-7-9 7 1.63 1.27L12 16zm0-11.47L17.74 9 12 13.47 6.26 9 12 4.53z"></path></svg>',
        category: 'layout',
        parent: 'NeonColumns'
      },
      'NeonGrid': {
        category: 'layout'
      },
      // Basic
      'NeonCard': {
        category: 'basic'
      },
      'NeonComeTalkToUs': {
        category: 'basic'
      },
      'NeonHeroText': {
        category: 'basic'
      },
      'NeonComeTalkToUsAgain': {
        category: 'basic'
      },
      'NeonThing': {
        category: 'basic'
      },
      'NeonParagraph': {
        icon: '',
        category: 'basic'
      },
      'cta': {
        category: 'basic'
      },
      'hero_banner': {
        category: 'basic'
      },
      'in_depth': {
        category: 'basic'
      },
      'subscribe_form': {
        category: 'basic'
      },
      'NeonHeader': {
        icon: '<svg data-icon="index" aria-hidden="true" focusable="false" width="60" height="32" viewBox="0 0 60 32" class="bem-Svg" style="display: block; transform: translate(0px, 0px);"><path opacity=".4" d="M6.9 5.5H4V1.2H.4V13H4V9h2.9v4h3.7V1.2H6.9v4.3zm26.6-1.8c-.4-.2-.9-.2-1.4-.2-1.3 0-2.4.4-3.3 1.3-.5.5-.9 1.1-1.1 1.8-.1-1-.5-1.7-1.2-2.2-.7-.6-1.7-.9-2.8-.9-1.4 0-2.6.4-3.7 1.3l-.7.6.6.8.7 1-.2.1c-.2.1-.3.2-.4.3-.1-1.2-.5-2.2-1.4-3-.9-.8-2-1.2-3.2-1.2-1.3 0-2.4.4-3.3 1.3-1 .9-1.4 2.1-1.4 3.5 0 1.5.5 2.6 1.4 3.5.9.9 2 1.3 3.4 1.3 1.6 0 2.9-.5 3.9-1.6l.3-.3V11c.2.4.4.8.8 1 .7.6 1.6.9 2.6.9.5 0 1-.1 1.5-.2v.3H28v-2.9c-.1-.2-.1-.4-.1-.5.2.9.6 1.6 1.2 2.2.9.9 2 1.4 3.2 1.4.6 0 1.1-.1 1.5-.3v.1H37V.6h-3.6v3.1zm-14.4 6.4L19 10h.1v-.3c0 .2 0 .4.1.6 0 0-.1-.1-.1-.2zm4.2-.3c-.3 0-.5-.1-.6-.1.1 0 .3-.1.7-.1h.6c-.3.1-.5.2-.7.2zm9.8-.4c-.2.3-.5.4-.8.4-.4 0-.6-.1-.9-.4-.3-.3-.4-.6-.4-1 0-.5.1-.8.4-1.1.3-.3.5-.4.9-.4.3 0 .6.1.8.4.3.3.4.6.4 1.1 0 .4-.1.7-.4 1zM40.5 1c-.4-.4-.9-.6-1.4-.6-.5 0-1 .2-1.4.6-.4.4-.6.9-.6 1.4 0 .4.1.8.4 1.2h-.2V13h3.6V3.6h-.2c.3-.3.4-.7.4-1.2 0-.6-.2-1.1-.6-1.4zm15 2.6v.1c-.4-.2-.9-.2-1.4-.2-1.3 0-2.3.5-3.2 1.4-.5.5-.8 1.1-1 1.8-.1-.8-.5-1.5-1-2.1-.7-.7-1.6-1.1-2.7-1.1-.5 0-1 .1-1.5.3v-.2h-3.6V13h3.6V8c0-.6.2-.8.3-.8.2-.2.5-.3.7-.3.4 0 .7 0 .7 1.1v5H50V9.8c-.1-.2-.1-.3-.1-.5.2.8.5 1.5 1.1 2.1.1.2.3.3.5.4l-.3.5-.7 1.2-.5.8.7.6c1.1.8 2.3 1.3 3.7 1.3 1.4 0 2.5-.4 3.4-1.3.9-.9 1.4-2.1 1.4-3.7V3.6h-3.7zm-.3 5.3c-.2.2-.5.3-.8.3-.4 0-.6-.2-.8-.3-.2-.3-.3-.6-.3-.9 0-.4.1-.7.3-1 .2-.2.4-.3.7-.3.4 0 .6.1.8.3.2.3.3.6.3 1 .1.4 0 .7-.2.9z"></path><g fill="currentColor"><path d="M1.4 12V2.2H3v4.2h4.9V2.2h1.7V12H7.9V8H3v4H1.4z"></path><path d="M1.4 12V2.2H3v4.2h4.9V2.2h1.7V12H7.9V8H3v4H1.4zm17.5-3h-5.8c0 .5.3 1 .7 1.3.5.3 1 .5 1.6.5.9 0 1.6-.3 2.1-.9l.9 1c-.8.8-1.8 1.2-3.1 1.2-1 0-1.9-.3-2.7-1s-1.1-1.6-1.1-2.8c0-1.2.4-2.1 1.1-2.8.7-.7 1.6-1 2.6-1s1.9.3 2.6.9 1.1 1.5 1.1 2.5V9zm-5.8-1.3h4.3c0-.6-.2-1.1-.6-1.4-.4-.3-.9-.5-1.4-.5s-1.2.2-1.6.5c-.5.4-.7.8-.7 1.4z"></path><path d="M18.9 9h-5.8c0 .5.3 1 .7 1.3.5.3 1 .5 1.6.5.9 0 1.6-.3 2.1-.9l.9 1c-.8.8-1.8 1.2-3.1 1.2-1 0-1.9-.3-2.7-1s-1.1-1.6-1.1-2.8c0-1.2.4-2.1 1.1-2.8.7-.7 1.6-1 2.6-1s1.9.3 2.6.9 1.1 1.5 1.1 2.5V9zm-5.8-1.3h4.3c0-.6-.2-1.1-.6-1.4-.4-.3-.9-.5-1.4-.5s-1.2.2-1.6.5c-.5.4-.7.8-.7 1.4zM26.7 12h-1.4v-1c-.6.7-1.4 1.1-2.5 1.1-.8 0-1.4-.2-1.9-.7s-.8-1-.8-1.8.3-1.3.8-1.6c.5-.4 1.3-.5 2.2-.5h2v-.3c0-1-.6-1.5-1.7-1.5-.7 0-1.4.3-2.2.8l-.7-1c.9-.7 1.9-1.1 3.1-1.1.9 0 1.6.2 2.1.7s.8 1.1.8 2.1V12zm-1.6-2.8v-.6h-1.8c-1.1 0-1.7.4-1.7 1.1 0 .4.1.6.4.8.3.2.7.3 1.2.3s.9-.1 1.3-.4c.4-.3.6-.7.6-1.2z"></path><path d="M26.7 12h-1.4v-1c-.6.7-1.4 1.1-2.5 1.1-.8 0-1.4-.2-1.9-.7s-.8-1-.8-1.8.3-1.3.8-1.6c.5-.4 1.3-.5 2.2-.5h2v-.3c0-1-.6-1.5-1.7-1.5-.7 0-1.4.3-2.2.8l-.7-1c.9-.7 1.9-1.1 3.1-1.1.9 0 1.6.2 2.1.7s.8 1.1.8 2.1V12zm-1.6-2.8v-.6h-1.8c-1.1 0-1.7.4-1.7 1.1 0 .4.1.6.4.8.3.2.7.3 1.2.3s.9-.1 1.3-.4c.4-.3.6-.7.6-1.2zm4.4 1.8c-.7-.7-1-1.6-1-2.8s.4-2.1 1.1-2.8c.7-.7 1.6-1 2.6-1s1.8.4 2.4 1.3V1.6H36V12h-1.6v-1.1c-.6.8-1.4 1.2-2.5 1.2-.9 0-1.7-.3-2.4-1.1zm.5-2.7c0 .7.2 1.3.7 1.7.5.4 1 .7 1.6.7.6 0 1.1-.2 1.6-.7.4-.5.6-1 .6-1.7s-.2-1.3-.6-1.8c-.4-.5-1-.7-1.6-.7s-1.2.2-1.7.7c-.4.5-.6 1.1-.6 1.8z"></path><path d="M29.5 11c-.7-.7-1-1.6-1-2.8s.4-2.1 1.1-2.8c.7-.7 1.6-1 2.6-1s1.8.4 2.4 1.3V1.6H36V12h-1.6v-1.1c-.6.8-1.4 1.2-2.5 1.2-.9 0-1.7-.3-2.4-1.1zm.5-2.7c0 .7.2 1.3.7 1.7.5.4 1 .7 1.6.7.6 0 1.1-.2 1.6-.7.4-.5.6-1 .6-1.7s-.2-1.3-.6-1.8c-.4-.5-1-.7-1.6-.7s-1.2.2-1.7.7c-.4.5-.6 1.1-.6 1.8zm8.4-5.2c-.2-.2-.3-.4-.3-.7s.1-.5.3-.7c.2-.2.4-.3.7-.3s.5.1.7.3.3.4.3.7-.1.5-.3.7c-.2.2-.4.3-.7.3s-.5-.1-.7-.3zm1.5 8.9h-1.6V4.6h1.6V12z"></path><path d="M38.4 3.1c-.2-.2-.3-.4-.3-.7s.1-.5.3-.7c.2-.2.4-.3.7-.3s.5.1.7.3.3.4.3.7-.1.5-.3.7c-.2.2-.4.3-.7.3s-.5-.1-.7-.3zm1.5 8.9h-1.6V4.6h1.6V12zm3.8-4v4h-1.6V4.6h1.6V6c.3-.5.6-.8 1-1.1.4-.3.9-.4 1.4-.4.8 0 1.5.3 2 .8.6.4.9 1.2.9 2.1V12h-1.6V7.9c0-1.4-.6-2.1-1.7-2.1-.5 0-1 .2-1.4.5-.4.5-.6 1-.6 1.7z"></path><path d="M43.7 8v4h-1.6V4.6h1.6V6c.3-.5.6-.8 1-1.1.4-.3.9-.4 1.4-.4.8 0 1.5.3 2 .8.6.4.9 1.2.9 2.1V12h-1.6V7.9c0-1.4-.6-2.1-1.7-2.1-.5 0-1 .2-1.4.5-.4.5-.6 1-.6 1.7zm14.4-3.4V11c0 1.3-.4 2.3-1.1 3s-1.6 1-2.8 1-2.1-.3-3-1l.7-1.2c.7.6 1.5.8 2.2.8.7 0 1.3-.2 1.8-.6s.7-1 .7-1.8v-1c-.2.4-.6.8-1 1.1-.4.3-.9.4-1.5.4-1 0-1.8-.3-2.4-1-.6-.7-1-1.5-1-2.6 0-1 .3-1.9 1-2.6.6-.7 1.4-1 2.4-1s1.8.4 2.4 1.2V4.6h1.6zM52.2 8c0 .6.2 1.1.6 1.6s.9.7 1.5.7 1.2-.2 1.6-.6c.4-.4.6-1 .6-1.6 0-.6-.2-1.2-.6-1.6s-.9-.8-1.6-.8-1.1.2-1.5.7c-.4.4-.6 1-.6 1.6z"></path><path d="M58.1 4.6V11c0 1.3-.4 2.3-1.1 3s-1.6 1-2.8 1-2.1-.3-3-1l.7-1.2c.7.6 1.5.8 2.2.8.7 0 1.3-.2 1.8-.6s.7-1 .7-1.8v-1c-.2.4-.6.8-1 1.1-.4.3-.9.4-1.5.4-1 0-1.8-.3-2.4-1-.6-.7-1-1.5-1-2.6 0-1 .3-1.9 1-2.6.6-.7 1.4-1 2.4-1s1.8.4 2.4 1.2V4.6h1.6zM52.2 8c0 .6.2 1.1.6 1.6s.9.7 1.5.7 1.2-.2 1.6-.6c.4-.4.6-1 .6-1.6 0-.6-.2-1.2-.6-1.6s-.9-.8-1.6-.8-1.1.2-1.5.7c-.4.4-.6 1-.6 1.6z"></path></g><path fill="currentColor" d="M1 17v2h57v-2H1zm55 4H1v2h55v-2zM1 27h56v-2H1v2zm0 4h27v-2H1v2z" opacity=".25"></path></svg>'
      },
      'NeonCmsCollection': {
        icon: '<svg data-icon="index" aria-hidden="true" focusable="false" width="45" height="37" viewBox="0 0 45 37" class="bem-Svg" style="display: block; transform: translate(0px, 0px);"><path d="M42 1a2 2 0 0 1 1.12.34 2 2 0 0 1 .73.88A2 2 0 0 1 44 3v31a2 2 0 0 1-2 2H3a2 2 0 0 1-1.12-.34 2 2 0 0 1-.73-.88A2 2 0 0 1 1 34V3a2 2 0 0 1 2-2h39m0-1H3a3 3 0 0 0-3 3v31a3 3 0 0 0 .24 1.17 3 3 0 0 0 1.09 1.32A3 3 0 0 0 3 37h39a3 3 0 0 0 3-3V3a3 3 0 0 0-.24-1.17A3 3 0 0 0 43.68.51 3 3 0 0 0 42 0z" opacity=".4"></path><path d="M43.84 2.22a2 2 0 0 0-.73-.88A2 2 0 0 0 42 1H3a2 2 0 0 0-2 2v31a2 2 0 0 0 .16.78 2 2 0 0 0 .73.88A2 2 0 0 0 3 36h39a2 2 0 0 0 2-2V3a2 2 0 0 0-.16-.78zM3 3h39v9H3zm39 31H3v-9h39zm0-11H3v-9h39z" fill="currentColor"></path><path fill="currentColor" opacity=".2" d="M3 3h39v31H3z"></path></svg>'
      }
      // 'theme/button': {
      // 	loadFromServer: true,
      // 	category: 'basic',
      // },
    }
  };
};
const Store = {
  namespaced: true,
  state: initialState(),
  mutations: {
    setPages: function (state, {
      pages
    }) {
      state.pages = pages;
    },
    setPage: function (state, {
      page
    }) {
      state.pages[page.id] = page;
    },
    updatePageAttr: function (state, {
      uuid,
      name,
      value
    }) {
      if (state.pages[uuid]) vue__WEBPACK_IMPORTED_MODULE_0___default.a.set(state.pages[uuid], name, value);
    },
    updatePageTitle: function (state, {
      uuid,
      title
    }) {
      if (state.pages[uuid]) state.pages[uuid].title = title;
    },
    updatePageDescription: function (state, {
      uuid,
      description
    }) {
      if (state.pages[uuid]) state.pages[uuid].description = description;
    },
    deleteAll: function (state) {
      var uuids = Object.keys(state.components);
      uuids.forEach(function (uuid) {
        // we may just want to delete the child instead of the root definition
        // delete references
        // let children = state.components[state.components[uuid]._parent].children;
        // children.splice(children.indexOf(uuid), 1);
        vue__WEBPACK_IMPORTED_MODULE_0___default.a.delete(state.components, uuid);
      });
    },
    syncState: function (state, payload) {
      state.components = payload.components;
      state.selected = payload.selected;
      state.hover = payload.hover;
      state.editMode = payload.editMode;
    },
    setComponents: function (state, payload) {
      state.components = payload || {
        'root': {
          cmp: 'Root',
          children: [],
          props: {}
        }
      };
    },
    /**
     *
     * @param state
     * @param componentName
     * @param props
     * @param children
     * @param to
     */
    addComponent: function (state, {
      componentName,
      props,
      children,
      parent
    }) {
      console.log(componentName, props, children, parent);
      var cmp = {}; //_.defaults(props, neon.editor.getCmpDefaults(componentName));
      cmp.cmp = componentName;
      cmp.uuid = neon.uuid64();
      cmp.children = children;
      cmp.props = _.cloneDeep(_.defaults(props, neon.editor.getCmpDefaults(componentName)));
      vue__WEBPACK_IMPORTED_MODULE_0___default.a.set(state.components, cmp.uuid, cmp);
      state.components[parent].children.push(cmp.uuid);
    },
    select: function (state, {
      uuid
    }) {
      // reset selected object to a single item
      state.selected = {};
      if (uuid) vue__WEBPACK_IMPORTED_MODULE_0___default.a.set(state.selected, uuid, true);
    },
    hover: function (state, {
      uuid
    }) {
      // reset selected object to a single item
      state.hover = uuid;
    },
    deleteComponents: function (state, {
      uuids
    }) {
      uuids = _.isString(uuids) ? [uuids] : uuids;
      uuids.forEach(function (uuid) {
        // we may just want to delete the child instead of the root definition
        // delete references
        // let children = state.components[state.components[uuid]._parent].children;
        // children.splice(children.indexOf(uuid), 1);
        vue__WEBPACK_IMPORTED_MODULE_0___default.a.delete(state.components, uuid);
      });
    },
    setEditMode: function (state, mode) {
      state.editMode = mode;
    },
    /**
     * Remove a child from a parent
     */
    removeChild: function (state, {
      parent,
      child
    }) {
      let children = state.components[parent].children;
      let indexOfChild = children.indexOf(child);
      if (indexOfChild !== -1) children.splice(indexOfChild);
    },
    updateComponentProps: function (state, {
      uuid,
      props
    }) {
      // ignore uuid fields
      delete props.uuid;
      Object.keys(props).forEach(function (key) {
        vue__WEBPACK_IMPORTED_MODULE_0___default.a.set(state.components[uuid], key, props[key]);
      });
    }
  },
  getters: {
    pages: state => {
      return state.pages;
    },
    components: state => {
      return state.components;
    },
    rootComponent: state => {
      return state.root;
    },
    getComponent: state => uuid => {
      return state.components[uuid];
    },
    isEditMode: state => {
      return state.editMode;
    },
    getSelected: state => {
      return Object.keys(state.selected);
    },
    isSelected: state => uuid => {
      return state.selected[uuid] === true;
    },
    isHovering: state => uuid => {
      return state.hover === uuid;
    },
    getChildren: state => uuid => {
      // for Neill:
      // return state.componentsTree[uuid].children
      return state.components[uuid].children;
    },
    getAvailableComponents: state => {
      return state.availableComponents;
    },
    /**
     * Get available components that can be added to the component specified by the uuid
     * @param state
     */
    getAvailableComponentsFor: (state, getters) => uuid => {
      const cmpConfigList = getters.getAvailableComponents;
      const components = getters.components;
      // get parent component config
      const parent = components[uuid] || {};
      const parentConfig = cmpConfigList[parent.cmp] || {};
      // if the parent component defines an only - then we should only return the specified components allowed
      if (!_.isUndefined(parentConfig.only)) {
        return _.pick(cmpConfigList, parentConfig.only);
      }
      // return all components except those that do not match an explicitly stated parent
      return _.pickBy(cmpConfigList, c => {
        // return all components with any parent allowed
        if (_.isUndefined(c.parent) || _.isEmpty(c.parent) || c.parent === '*') return true;
        return c.parent === parentConfig.cmp;
      });
    },
    /**
     * @typedef ComponentConfig
     * @property {String} name - Unique component name
     * @property {String} title - Display title for this component
     * @property {String} description - A component description - this will be shown in the component inspector sidebar
     * @property {String} icon - This can be a string class name or an svg element string
     * @property {String} category - The component category
     * @property {Array} keywords - Comma separated list of keywords relevant to the component
     * @property {Array} parent - Array of parent components, when specified this component will only be available
     *                            as a direct child
     */

    /**
     * Get the component configuration information - this is NOT the component props.
     * the config includes the props plus information about the component such as
     *
     * @param state
     * @returns {function(type): ComponentConfig}
     */
    getComponentConfig: state => type => {
      return state.availableComponents[type];
    }
  },
  actions: {
    updatePage: function (context, {
      page,
      redirect
    }) {
      return new Promise((resolve, reject) => {
        let url = neon.url('/cms/editor/page-update');
        $.ajax({
          type: "POST",
          url: url,
          dataType: 'json',
          data: {
            "CmsPage": page,
            redirect: redirect
          }
        }).done(function (result) {
          context.commit('setPage', {
            page: result
          });
          resolve(result);
        }).fail(function (response) {
          console.error('error', response);
          reject(response);
        });
      });
    }
  }
};
neon.Store.registerModule('editor', Store);
/* harmony default export */ __webpack_exports__["c"] = (neon.Store);

/***/ }),

/***/ "c32b":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "c8ba":
/***/ (function(module, exports) {

var g;

// This works in non-strict mode
g = (function() {
	return this;
})();

try {
	// This works if eval is allowed (see CSP)
	g = g || new Function("return this")();
} catch (e) {
	// This works if the window reference is available
	if (typeof window === "object") g = window;
}

// g can still be undefined, but nothing to do about it...
// We return undefined, instead of nothing here, so it's
// easier to handle this case. if(!global) { ...}

module.exports = g;


/***/ }),

/***/ "ca4d":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Cobe_vue_vue_type_style_index_0_id_c348feda_prod_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("f711");
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Cobe_vue_vue_type_style_index_0_id_c348feda_prod_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Cobe_vue_vue_type_style_index_0_id_c348feda_prod_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */


/***/ }),

/***/ "ce43":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "e146":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "f711":
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "fae3":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js
// This file is imported into lib/wc client bundles.

if (typeof window !== 'undefined') {
  var currentScript = window.document.currentScript
  if (true) {
    var getCurrentScript = __webpack_require__("8875")
    currentScript = getCurrentScript()

    // for backward compatibility, because previously we directly included the polyfill
    if (!('currentScript' in document)) {
      Object.defineProperty(document, 'currentScript', { get: getCurrentScript })
    }
  }

  var src = currentScript && currentScript.src.match(/(.+\/)[^/]+\.js(\?.*)?$/)
  if (src) {
    __webpack_require__.p = src[1] // eslint-disable-line
  }
}

// Indicate to webpack that this file can be concatenated
/* harmony default export */ var setPublicPath = (null);

// EXTERNAL MODULE: ./src/main.js
var main = __webpack_require__("56d7");

// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js




/***/ }),

/***/ "fc28":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_NeonCard_vue_vue_type_style_index_0_id_e7fa22fc_prod_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__("bd04");
/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_NeonCard_vue_vue_type_style_index_0_id_e7fa22fc_prod_lang_css__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_ref_7_oneOf_1_0_node_modules_css_loader_dist_cjs_js_ref_7_oneOf_1_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_7_oneOf_1_2_node_modules_cache_loader_dist_cjs_js_ref_1_0_node_modules_vue_loader_lib_index_js_vue_loader_options_NeonCard_vue_vue_type_style_index_0_id_e7fa22fc_prod_lang_css__WEBPACK_IMPORTED_MODULE_0__);
/* unused harmony reexport * */


/***/ })

/******/ });
});
//# sourceMappingURL=neon-editor.umd.js.map