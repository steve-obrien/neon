"use strict";

/**
 *
 */
(function () {
  var pickerShortcuts = {
    shortcuts: [{
      text: 'Last 3 months',
      seconds: -3600 * 1000 * 24 * 90
    }, {
      text: 'Last month',
      seconds: -3600 * 1000 * 24 * 30
    }, {
      text: 'Last week',
      seconds: -3600 * 1000 * 24 * 7
    }, {
      text: 'Next week',
      seconds: 3600 * 1000 * 24 * 7
    }, {
      text: 'Next month',
      seconds: 3600 * 1000 * 24 * 30
    }],

    /**
     * Generate the shortcut objects from the shortcuts array with times and text
     * @returns {Array}
     */
    getShortcuts: function getShortcuts() {
      var s = [];

      _.forEach(this.shortcuts, function (data) {
        var end = new Date();
        var start = new Date();
        s.push({
          text: data.text,
          onClick: function onClick(picker) {
            var end = new Date();
            var start = new Date(); // in the future in the past

            if (data.seconds < 0) {
              start.setTime(start.getTime() + data.seconds);
            } else {
              end.setTime(end.getTime() + data.seconds);
            }

            picker.$emit('pick', [start, end]);
          }
        });
      });

      return s;
    }
  };
  var neonCoreFormFieldsElDatetimerangeTemplate = {
    extends: Vue.component('neon-core-form-fields-base'),
    data: function data() {
      return {
        pickerOptions: {
          shortcuts: pickerShortcuts.getShortcuts()
        }
      };
    },
    template: "\n\t\t\t<neon-core-form-field v-bind=\"fieldProps\" class=\"neonFormDatetimerange\">\n\t\t\t\t<el-date-picker v-model=\"rangeValue\" :format=\"format\" :value-format=\"valueFormat\" :name=\"rangeName\" :picker-options=\"pickerOptions\" range-separator=\"To\" start-placeholder=\"Start date\" end-placeholder=\"End date\" :type=\"type\" :readOnly=\"isReadOnly\"></el-date-picker>\n\t\t\t\t<input :value=\"modelValue['from']\" :name=\"inputName+'[from]'\" :id=\"id\" />\n\t\t\t\t<input :value=\"modelValue['to']\" :name=\"inputName+'[to]'\" :id=\"id\" />\n\t\t\t</neon-core-form-field>\n\t\t",
    computed: {
      rangeValue: {
        set: function set(value) {
          if (_.isNull(value)) {
            this.modelValue = value;
          } else {
            this.modelValue = {
              'from': value[0],
              'to': value[1]
            };
          }
        },
        get: function get() {
          if (_.isNull(this.modelValue) || _.isUndefined(this.modelValue)) return []; // must be empty string if null

          return [this.modelValue['from'] || '', this.modelValue['to'] || ''];
        }
      },
      rangeName: function rangeName() {
        return [this.inputName + "[from]", this.inputName + "[to]"];
      }
    }
  };

  var neonCoreFormFieldsElDatetimerange = _.cloneDeep(neonCoreFormFieldsElDatetimerangeTemplate);

  neonCoreFormFieldsElDatetimerange.props = {
    value: [Array, Object],
    type: {
      type: String,
      default: 'datetimerange'
    },
    format: {
      type: String,
      default: "dd/MM/yyyy HH:mm"
    },
    valueFormat: {
      type: String,
      default: "yyyy-MM-dd HH:mm:ss"
    }
  };

  var neonCoreFormFieldsElDaterange = _.cloneDeep(neonCoreFormFieldsElDatetimerangeTemplate);

  neonCoreFormFieldsElDaterange.props = {
    value: [Array, Object],
    type: {
      type: String,
      default: 'daterange'
    },
    format: {
      type: String,
      default: 'dd/MM/yyyy'
    },
    valueFormat: {
      type: String,
      default: "yyyy-MM-dd"
    }
  };
  /**
   * Field components specific to the element.io library
   */

  Vue.component('neon-core-form-fields-el-datetimerange', neonCoreFormFieldsElDatetimerange);
  Vue.component('neon-core-form-fields-el-daterange', neonCoreFormFieldsElDaterange);
  Vue.component('neon-core-form-fields-el-datetime', {
    extends: Vue.component('neon-core-form-fields-base'),
    props: {
      value: [Date, String],
      format: {
        type: String,
        default: 'dd/MM/yyyy HH:mm'
      },
      pickerOptions: {
        type: Object,
        default: function _default() {
          return {
            firstDayOfWeek: '1'
          };
        },
        editor: {
          component: 'neon-core-form-form',
          fields: {
            // Should this be a component itself - DayOfWeek Selector component
            firstDayOfWeek: {
              label: 'First day of the week',
              name: 'firstDayOfWeek',
              class: 'neon\\core\\form\\fields\\Select',
              items: {
                '1': 'Monday',
                '2': 'Tuesday',
                '3': 'Wednesday',
                '4': 'Thursday',
                '5': 'Friday',
                '6': 'Saturday',
                '7': 'Sunday'
              },
              value: '1',
              allowClear: false
            }
          }
        }
      }
    },
    computed: {
      picker: function picker() {
        var pickerOptions = _.clone(this.pickerOptions);

        pickerOptions.firstDayOfWeek = parseInt(this.pickerOptions.firstDayOfWeek);
        return pickerOptions;
      }
    },
    template: "\n\t\t\t<neon-core-form-field v-bind=\"fieldProps\">\n\t\t\t\t<el-date-picker prefix-icon=\"el-icon-date\" v-model=\"modelValue\" :format=\"format\" value-format=\"yyyy-MM-dd HH:mm:ss\" type=\"datetime\" :placeholder=\"placeholder\" :readonly=\"isReadOnly\" :picker-options=\"picker\"></el-date-picker>\n\t\t\t\t<input type=\"hidden\" :value=\"modelValue\" :name=\"inputName\" :id=\"id\" />\n\t\t\t</neon-core-form-field>\n\t\t"
  });
  Vue.component('neon-core-form-fields-el-date', {
    extends: Vue.component('neon-core-form-fields-base'),
    props: {
      value: [Date, String],
      format: {
        type: String,
        default: 'dd/MM/yyyy'
      },
      pickerOptions: {
        type: Object,
        default: function _default() {
          return {
            firstDayOfWeek: '1'
          };
        },
        editor: {
          component: 'neon-core-form-form',
          fields: {
            // Should this be a component itself - DayOfWeek Selector component
            firstDayOfWeek: {
              label: 'First day of the week',
              name: 'firstDayOfWeek',
              class: 'neon\\core\\form\\fields\\Select',
              items: {
                '1': 'Monday',
                '2': 'Tuesday',
                '3': 'Wednesday',
                '4': 'Thursday',
                '5': 'Friday',
                '6': 'Saturday',
                '7': 'Sunday'
              },
              value: '1',
              allowClear: false
            }
          }
        }
      }
    },
    computed: {
      picker: function picker() {
        var pickerOptions = _.clone(this.pickerOptions);

        pickerOptions.firstDayOfWeek = parseInt(this.pickerOptions.firstDayOfWeek);
        return pickerOptions;
      }
    },
    template: "\n\t\t\t<neon-core-form-field v-bind=\"fieldProps\">\n\t\t\t\t<el-date-picker prefix-icon=\"el-icon-date\" v-model=\"modelValue\" :format=\"format\" value-format=\"yyyy-MM-dd\" type=\"date\" :placeholder=\"placeholder\" :readonly=\"isReadOnly\" :picker-options=\"picker\"></el-date-picker>\n\t\t\t\t<input type=\"hidden\" :value=\"modelValue\" :name=\"inputName\" :id=\"id\" />\n\t\t\t</neon-core-form-field>\n\t\t"
  });
  Vue.component('neon-core-form-fields-el-switchinput', {
    extends: Vue.component('neon-core-form-fields-base'),
    props: {
      value: Boolean
    },
    template: "\n\t\t\t<neon-core-form-field v-bind=\"fieldProps\">\n\t\t\t\t<div v-if=\"isPrintOnly\">{{value}}</div>\n\t\t\t\t<el-switch v-else :true-value=\"1\" v-model=\"modelValue\" :readonly=\"isReadOnly\"></el-switch><input type=\"hidden\" :name=\"inputName\" :value=\"modelValue?1:0\" />\n\t\t\t</neon-core-form-field>\n\t\t"
  });
  Vue.component('neon-core-form-fields-el-slider', {
    extends: Vue.component('neon-core-form-fields-base'),
    props: {
      value: [Number, Array, String],
      vertical: {
        type: Boolean,
        default: false
      },
      height: {
        type: Number,
        default: 100
      },
      // only applicable if vertical = true
      min: {
        type: Number
      },
      max: {
        type: Number
      }
    },
    template: "\n\t\t\t<neon-core-form-field v-bind=\"fieldProps\">\n\t\t\t\t<div v-if=\"isPrintOnly\">{{value}}</div>\n\t\t\t\t<div v-else>\n\t\t\t\t\t<input type=\"hidden\" :name=\"inputName\" :value=\"modelValue\" :readonly=\"isReadOnly\"/>\n\t\t\t\t\t<el-slider v-bind=\"$props\" v-model=\"modelValue\" :vertical=\"vertical\" :readonly=\"isReadOnly\" :height=\"height + 'px'\"></el-slider>\n\t\t\t\t</div>\n\t\t\t</neon-core-form-field>\n\t\t"
  });
  Vue.component('neon-core-form-fields-el-number', {
    extends: Vue.component('neon-core-form-fields-base'),
    props: {
      value: Number,
      String: String
    },
    template: "\n\t\t\t<neon-core-form-field v-bind=\"fieldProps\">\n\t\t\t\t<div v-if=\"isPrintOnly\">{{value}}</div>\n\t\t\t\t<div v-else>\n\t\t\t\t\t<el-input-number v-model=\"modelValue\" :name=\"inputName\" :readonly=\"isReadOnly\"></el-input-number>\n\t\t\t\t</div>\n\t\t\t</neon-core-form-field>\n\t\t"
  });
  Vue.component('neon-core-form-fields-el-color', {
    extends: Vue.component('neon-core-form-fields-base'),
    props: {
      value: String
    },
    template: "\n\t\t\t<neon-core-form-field v-bind=\"fieldProps\">\n\t\t\t\t<div v-if=\"isPrintOnly\">{{value}}</div>\n\t\t\t\t<div v-else>\n\t\t\t\t\t<el-color-picker show-alpha v-model=\"modelValue\" :name=\"inputName\" :readonly=\"isReadOnly\"></el-color-picker>\n\t\t\t\t</div>\n\t\t\t</neon-core-form-field>\n\t\t"
  });
})();