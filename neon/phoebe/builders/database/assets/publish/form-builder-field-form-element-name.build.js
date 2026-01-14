"use strict";

Vue.component('form-builder-field-form-element-name', Vue.extend({
  props: {
    field: Object,
    parent: String
  },
  template: "\n\t\t\t<div class=\"form-group form-group--code-label \">\n\t\t\t\t<label class=\"control-label\" for=\"data-name\">Data name</label>\n\t\t\t\t<div class=\"hint-block hint-top  \">The name used inside templates and via the data API to access this field</div>\n\t\t\t\t<input :value=\"field.name\"  @change=\"update\" v-ddsformat type=\"text\" id=\"data-name\" class=\"form-control\" name=\"name\"  />\n\t\t\t</div>\n\t\t",
  methods: {
    update: function update(event) {
      var inputValue = event.target.value;
      var newName = inputValue.toLowerCase().replace(/ +/g, '_').replace(/[^a-z0-9_]/g, '');
      var currentName = this.field.name;

      if (FormBuilderApp.helpers.isNameUnique(newName)) {
        // only update the store if the name is unique
        this.$store.commit('phoebe/updateFieldName', {
          field: this.field,
          name: newName
        });
        this.$store.commit('FORMS/updateFieldName', {
          parentPath: this.parent,
          newName: newName,
          currentName: currentName
        });
      } else {
        alert('A field with the name "' + newName + '" already exists.');
        this.field.name = currentName;
        $(event.target).val(currentName);
      }
    }
  }
}));