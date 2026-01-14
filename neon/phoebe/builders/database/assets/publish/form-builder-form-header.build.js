"use strict";

Vue.component('form-builder-form-header', {
  props: {
    form: Object
  },
  data: function data() {
    return {
      showJson: false
    };
  },
  template: "\n\t\t<div class=\"form-group form-group__header\" :class=\"{ 'is-selected' : state.editingForm }\">\n            <div @click=\"select\">\n                <h3 class=\"form-group__title\">{{ form.label ? form.label : 'Untitled Content' }} - <code>{{ form.name }}</code></h3>\n\t\t\t\t<div class=\"hint-block\">{{ form.hint ? form.hint : 'This is a description of your content - click here to edit'}}</div>\n\t\t\t\t<div v-if=\"state.editingForm\">\n\t\t\t\t\t<div class=\"form-group\">\n\t\t\t\t\t\t<input :value=\"form.label\" @input=\"updateLabel\"> - <input :value=\"form.name\" @input=\"updateName\" v-ddsformat> <br>\n\t\t\t\t\t\t<textarea :value=\"form.hint\" @input=\"updateHint\"></textarea>\n\t\t\t\t\t\t<label>Set map field</label>\n\t\t\t\t\t\t<select name=\"mapField\" @change=\"updateMapField($event.target.value)\">\n\t\t\t\t\t\t\t<aoption>-- Select a map field --</aoption>\n\t\t\t\t\t\t\t<option id=\"field.name\" v-for=\"field in availableMapFields\" :selected=\"field.mapField\">{{field.name}}</option>\n\t\t\t\t\t\t</select>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<button @click=\"showJson = !showJson\" class=\"btn btn-default\">{}</button>\n\t\t\t<span v-if=\"deletedFieldCount\" ><span class=\"text-danger\">({{ deletedFieldCount }})</span> deleted fields</span>\n\t\t\t<div v-if=\"showJson\">\n\t\t\t\t<pre>{{form}}</pre>\n\t\t\t</div>\n\t\t</div>\n\t",
  methods: {
    showJsonClick: function showJsonClick() {
      this.showJson = !this.showJson;
    },
    updateMapField: function updateMapField(value) {
      this.$store.dispatch('phoebe/updateMapField', {
        mapField: value
      });
    },
    updateLabel: function updateLabel(e) {
      this.form.label = e.target.value;
      this.$store.commit('phoebe/updateForm', {
        form: this.form
      });
    },
    updateName: function updateName(e) {
      this.form.name = e.target.value;
      this.$store.commit('phoebe/updateForm', {
        form: this.form
      });
    },
    updateHint: function updateHint(e) {
      this.form.hint = e.target.value;
      this.$store.commit('phoebe/updateForm', {
        form: this.form
      });
    },
    select: function select() {
      // this.$store.commit('phoebe/setEditingField', {field: this.form, edit_part: 'label'});
      this.$store.commit('phoebe/editForm');
    }
  },
  computed: {
    availableMapFields: function availableMapFields() {
      var fields = [];

      _.forEach(this.state.form.fields, function (item) {
        fields.push({
          name: item.name
        });
      });

      fields.push({
        name: '_uuid'
      });
      return fields;
    },
    deletedFieldCount: function deletedFieldCount() {
      return this.$store.getters['phoebe/deletedFields']().length;
    },
    state: function state() {
      return this.$store.state.phoebe;
    },
    type: function type() {
      return this.$store.state.phoebe.type;
    }
  }
});