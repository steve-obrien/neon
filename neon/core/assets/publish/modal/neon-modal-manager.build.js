"use strict";

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

Vue.component('neon-modal-manager', {
  template: "\n\t\t<div id=\"neonModalContainer\">\n\t\t\t<neon-modal\n\t\t\t\tv-for=\"modal in modals\"\n\t\t\t\t:key=\"modal.name\"\n\t\t\t\tv-bind=\"modal.modalAttrs\"\n\t\t\t\tv-on=\"modal.modalListeners\"\n\t\t\t\t@closed=\"remove(modal.name)\"\n\t\t\t\t@close=\"hide(modal.modalAttrs.name)\"\n\t\t\t>\n\t\t\t\t<component style=\"\"\n\t\t\t\t\t:is=\"modal.component\"\n\t\t\t\t\tv-bind=\"modal.componentAttrs\"\n\t\t\t\t\tv-on=\"$listeners\"\n\t\t\t\t\t@close=\"hide(modal.modalAttrs.name)\"\n\t\t\t\t/>\n\t\t\t</neon-modal>\n\t\t</div>\n\t",
  data: function data() {
    return {
      modals: []
    };
  },
  created: function created() {
    neon.modal._manager = this;
  },
  methods: {
    hide: function hide(modalName) {
      neon.modal.hide(modalName);
    },
    add: function add(component) {
      var componentAttrs = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var modalAttrs = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
      var modalListeners = arguments.length > 3 ? arguments[3] : undefined;
      var id = neon.modal.helpers.generateId();
      var name = modalAttrs.name || id;

      var foundModal = _.find(this.modals, function (m) {
        return m.name === name;
      });

      if (foundModal) {
        Object.assign(foundModal, {
          componentAttrs: componentAttrs,
          modalAttrs: modalAttrs,
          modalListeners: modalListeners
        });
        neon.modal.show(name);
        return;
      }

      var modal = {
        name: name,
        modalAttrs: _objectSpread({}, modalAttrs, {
          name: name
        }),
        modalListeners: modalListeners,
        component: component,
        componentAttrs: componentAttrs
      };
      this.modals.push(modal);
      this.$nextTick(function () {
        neon.modal.show(name);
      });
    },
    remove: function remove(name) {}
  }
});