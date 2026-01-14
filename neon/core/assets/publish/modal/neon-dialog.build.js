"use strict";

Vue.component('neon-dialog', {
  template: "\n\t<neon-modal \n\t\tname=\"dialog\" \n\t\t:classes=\"['neonModal', 'vue-dialog', this.params.class]\" \n\t\t:width=\"params.width\"\n\t\t:height=\"params.height\"\n\t\t:pivot-y=\"0.3\"\n\t\t:adaptive=\"true\"\n\t\t:clickToClose=\"clickToClose\"\n\t\t:transition=\"transition\"\n\t\t@before-open=\"beforeOpened\"\n\t\t@before-close=\"beforeClosed\"\n\t\t@opened=\"$emit('opened', $event)\"\n\t\t@closed=\"$emit('closed', $event)\"\n\t>\n\t\t<div class=\"dialog-content\">\n\t\t\t{{params}} {{width}}\n\t\t\t<div class=\"dialog-c-title\" v-if=\"params.title\" v-html=\"params.title || ''\"></div>\n\t\t\t<component v-if=\"params.component\" v-bind=\"params.props\" :is=\"params.component\"></component>\n\t\t\t<div v-else class=\"dialog-c-text\" v-html=\"params.text || ''\"></div>\n\t\t</div>\n\t\t<div class=\"neonModal_buttons\" v-if=\"buttons\">\n\t\t\t<button v-for=\"(button, i) in buttons\" type=\"button\"\n\t\t\t\t:class=\"button.class || 'neonModal_button'\" \n\t\t\t\t:style=\"buttonStyle\" :key=\"i\" v-html=\"button.title\" @click.stop=\"click(i, $event)\">\n\t\t\t\t{{button.title}}\n\t\t\t</button>\n\t\t</div>\n\t\t<div v-else class=\"neonModal_buttonsNone vue-dialog-buttons-none\"></div>\n\t</neon-modal>\n\t",
  created: function created() {
    neon.modal._dialog = this;
  },
  props: {
    height: {
      type: [Number, String],
      default: 'auto'
    },
    width: {
      type: [Number, String],
      default: 400
    },
    clickToClose: {
      type: Boolean,
      default: true
    },
    transition: {
      type: String,
      default: 'fade'
    }
  },
  data: function data() {
    return {
      params: {},
      defaultButtons: [{
        title: 'CLOSE'
      }]
    };
  },
  computed: {
    buttons: function buttons() {
      return this.params.buttons || this.defaultButtons;
    },

    /**
     * Returns FLEX style with correct width for arbitrary number of
     * buttons.
     */
    buttonStyle: function buttonStyle() {
      return {
        flex: "1 1 ".concat(100 / this.buttons.length, "%")
      };
    }
  },
  methods: {
    beforeOpened: function beforeOpened(event) {
      window.addEventListener('keyup', this.onKeyUp);
      this.params = event.params || {};
      this.$emit('before-opened', event);
    },
    beforeClosed: function beforeClosed(event) {
      window.removeEventListener('keyup', this.onKeyUp);
      this.params = {};
      this.$emit('before-closed', event);
    },
    click: function click(i, event) {
      var source = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'click';
      var button = this.buttons[i];

      if (button && typeof button.handler === 'function') {
        button.handler(i, event, {
          source: source
        });
      } else {
        neon.modal.hide('dialog');
      }
    },
    onKeyUp: function onKeyUp(event) {
      if (event.which === 13 && this.buttons.length > 0) {
        var buttonIndex = this.buttons.length === 1 ? 0 : this.buttons.findIndex(function (button) {
          return button.default;
        });

        if (buttonIndex !== -1) {
          this.click(buttonIndex, event, 'keypress');
        }
      }
    }
  }
});