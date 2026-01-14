"use strict";

neon.modal = {
  show: function show(component, options) {},
  dialog: function dialog(title, text, buttons) {}
};
Vue.component('neon-dynamic-component', {
  props: {
    /**
     * {string} component.name - The name of the component to create inside the modal
     * {Object} component.props - Optional props to pass to the component
     */
    component: {
      type: Object,
      default: function _default() {
        return {
          name: '',
          props: {}
        };
      }
    }
  },
  render: function render(createElement) {
    return createElement(this.component.name, {
      props: this.component.props
    });
  }
});
/**
 * ------------------------
 * <firefly-modal>
 * ------------------------
 */

Vue.component('neon-modal', {
  props: {
    component: {
      type: Object,
      default: function _default() {
        return {
          name: '',
          props: {}
        };
      }
    }
  },
  template: "\n\t\t<neon-dynamic-component :component=\"component\" ></neon-dynamic-component>\n\t"
});
/**
 * ------------------------
 * <firefly-modal-wrap>
 * ------------------------
 */

Vue.component('neon-modal-wrap', {
  template: "\n\t<transition name=\"modal\">\n\t\t<div class=\"fireflyModal\" @click.self=\"$emit('close')\" >\n\t\t\t<div class=\"fireflyModal_wrapper\" @click.self=\"$emit('close')\">\n\t\t\t\t<div class=\"fireflyModal_container\">\n\t\t\t\t\t<button style=\"position: absolute; top: 0; right: 0; z-index: 10000;\" type=\"button\" class=\"close pull-right\" @click.prevent=\"$emit('close')\" aria-label=\"Close\"><span aria-hidden=\"true\">\xD7</span></button>\n\t\t\t\t\t<div class=\"fireflyModal_header\" >\n\t\t\t\t\t\t<slot name=\"header\"></slot>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div name=\"body\" class=\"fireflyModal_body\" >\n\t\t\t\t\t\t<slot></slot>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"fireflyModal_footer fireflyModal_footerButtons\">\n\t\t\t\t\t\t<slot name=\"footer\">\n\t\t\t\t\t\t\t<button class=\"btn btn-default\" @click.prevent=\"$emit('close')\">Close</button>\n\t\t\t\t\t\t</slot>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t</transition>\n\t"
});