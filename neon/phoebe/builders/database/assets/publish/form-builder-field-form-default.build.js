"use strict";

Vue.component('form-builder-field-form-default', {
  props: {
    field: {
      type: Object,
      required: true,
      default: function _default() {
        return {
          classLabel: 'Unknown',
          label: 'Label',
          required: false,
          hint: 'A handy hint',
          placeholder: 'Placeholder text'
        };
      }
    }
  },
  template: "\n\t\t<div>\n\t\t\t<div class=\"hr-text\">{{field.classLabel}}</div>\n\t\t\t<form-builder-field-form-element-name :field=\"field\"></form-builder-field-form-element-name>\n\t\t\t<!--<form-builder-field-form-element-label :field=\"field\"></form-builder-field-form-element-label>-->\n\t\t\t<form-builder-edit-property property=\"label\" :field=\"field\" \n\t\t\t\teditor=\"neon-core-form-fields-text\"\n\t\t\t\t:editor-props=\"{label:'Label', placeholder: 'Set the label for the field'}\"></form-builder-edit-property>\n\t\t\t<form-builder-edit-property property=\"hint\" :field=\"field\" \n\t\t\t\teditor=\"neon-core-form-fields-text\"\n\t\t\t\t:editor-props=\"{label:'Guide Text', placeholder: 'Add additional guide text'}\"></form-builder-edit-property>\n\t\t\t<form-builder-edit-property property=\"placeholder\" :field=\"field\" \n\t\t\t\teditor=\"neon-core-form-fields-text\"\n\t\t\t\t:editor-props=\"{label:'Placeholder', placeholder: 'Add helper placeholder text'}\"></form-builder-edit-property>\n\t\t\t<form-builder-field-form-element-required :field=\"field\"></form-builder-field-form-element-required>\n\t\t\t<div class=\"form-group\"><form-builder-field-form-element-delete :field=\"field\"></form-builder-field-form-element-delete></div>\n\t\t</div>\n\t"
});