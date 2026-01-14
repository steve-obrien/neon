"use strict";

Vue.component('form-builder-field-form-element-decimal', Vue.extend({
  props: {
    field: Object
  },
  template: "\n\t\t\t<div class=\"form-group\">\n\t\t\t\t<label class=\"control-label\" for=\"data-name\">Decimal Places</label>\n\t\t\t\t<div class=\"hint-block hint-top  \">Set the max number of decimal places</div>\n\t\t\t\t<input class=\"form-control\" :value=\"field.decimal\" @change=\"update($event.target.value)\" type=\"number\" min=0 max=10 id=\"data-decimal\" name=\"decimal\"  />\n\t\t\t</div>\n\t\t",
  methods: {
    update: function update(value) {
      this.field.decimal = value; // override the default implementation and toggle the value

      this.$store.commit('phoebe/updateField', {
        field: this.field
      });
    }
  }
}));