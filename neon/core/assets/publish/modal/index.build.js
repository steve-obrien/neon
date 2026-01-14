"use strict";

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

neon.modal = {
  zIndex: 999,

  /**
   *
   * @param modal
   * @param props
   * @param params
   * @param events
   */
  show: function show(modal, props, params, events) {
    this.zIndex++;

    if (typeof modal === 'string') {
      neon.modal.event.$emit('toggle', modal, true, props);
      return;
    }

    var manager = neon.modal.getModalManager();

    if (manager) {
      manager.add(modal, props, params, events);
      return;
    }

    console.warn(neon.modal._unmountedModalManager);
  },

  /**
   *
   * @param name
   * @param params
   */
  hide: function hide(name, params) {
    neon.modal.event.$emit('toggle', name, false, params);
  },

  /**
   * Modal event bus
   */
  event: new Vue(),

  /**
   * Error message if mounting the modal manager is unsuccessful
   */
  _unmountedModalManager: '[neon-modal] In order to render dynamic modals, a <neon-modal-manager> component must be present on the page',

  /**
   * Store a NeonModalManager component instance
   */
  _manager: undefined,

  /**
   * Modal specific helper functions
   */
  helpers: {}
};

neon.modal.getModalManager = function () {
  if (_.isUndefined(neon.modal._manager)) {
    var modalManager = document.createElement('div');
    document.body.appendChild(modalManager);
    new Vue({
      parent: neon.app,
      render: function render(h) {
        return h(Vue.component('neon-modal-manager'));
      }
    }).$mount(modalManager);
  }

  return neon.modal._manager;
}; // private


neon.modal.helpers.getType = function (value) {
  var floatRegexp = '[-+]?[0-9]*.?[0-9]+';
  var types = [{
    name: 'px',
    regexp: new RegExp("^".concat(floatRegexp, "px$"))
  }, {
    name: '%',
    regexp: new RegExp("^".concat(floatRegexp, "%$"))
  }, {
    name: 'px',
    regexp: new RegExp("^".concat(floatRegexp, "$"))
  } // If no suffix specified, assigning "px"
  ];
  if (value === 'auto') return {
    type: value,
    value: 0
  };

  for (var i = 0; i < types.length; i++) {
    var type = types[i];
    if (type.regexp.test(value)) return {
      type: type.name,
      value: parseFloat(value)
    };
  }

  return {
    type: '',
    value: value
  };
};

neon.modal.helpers.parseNumber = function (value) {
  switch (_typeof(value)) {
    case 'number':
      return {
        type: 'px',
        value: value
      };

    case 'string':
      return neon.modal.helpers.getType(value);

    default:
      return {
        type: '',
        value: value
      };
  }
};

neon.modal.helpers.validateNumber = function (value) {
  if (typeof value === 'string') {
    var _value = neon.modal.helpers.parseNumber(value);

    return (_value.type === '%' || _value.type === 'px') && _value.value > 0;
  }

  return value >= 0;
};

neon.modal.createEvent = function () {
  var args = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  return _objectSpread({
    id: neon.modal.helpers.generateId(),
    timestamp: Date.now(),
    canceled: false
  }, args);
};

neon.modal.helpers.generateId = function () {
  return neon.uuid64();
};

neon.modal.helpers.getMutationObserver = function () {
  if (typeof window !== 'undefined') {
    var prefixes = ['', 'WebKit', 'Moz', 'O', 'Ms'];

    for (var i = 0; i < prefixes.length; i++) {
      var name = prefixes[i] + 'MutationObserver';

      if (name in window) {
        return window[name];
      }
    }
  }

  return false;
};

neon.modal.getModalDialog = function (Vue) {
  if (_.isUndefined(neon.modal._dialog)) {
    var modalContainer = document.createElement('div');
    document.body.appendChild(modalContainer);
    new Vue({
      parent: neon.app,
      render: function render(h) {
        return h('neon-dialog');
      }
    }).$mount(modalContainer);
  }

  return neon.modal._dialog;
};