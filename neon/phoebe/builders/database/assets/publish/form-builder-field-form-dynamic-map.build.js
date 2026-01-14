"use strict";

Vue.component('form-builder-field-form-dynamic-map', Vue.extend({
  props: {
    field: Object
  },
  template: "\n\t\t\t<div>\n\t\t\t\t<div class=\"form-group\" >\n\t\t\t\t\t<label class=\"control-label\" for=\"placeholder\">Data Map Provider</label>\n\t\t\t\t\t<neon-select v-model=\"dataMapProvider\" :allow-clear=\"false\">\n\t\t\t\t\t\t<option v-for=\"provider in providers\" :value=\"provider.key\">\n\t\t\t\t\t\t\t{{ provider.name }}\n\t\t\t\t\t\t</option>\n\t\t\t\t\t</neon-select>\n\t\t\t\t\t<neon-select v-model=\"dataMapKey\" :allow-clear=\"false\" :items=\"maps\"></neon-select>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t",
  computed: {
    dataMapProvider: {
      get: function get() {
        return this.field.dataMapProvider;
      },
      set: function set(value) {
        // set the mapkey to the first one available so it always has a value
        var map = Object.keys(this.providers[value]['maps'])[0];
        this.$store.commit('phoebe/updateFieldProps', {
          field: this.field,
          update: {
            dataMapProvider: value,
            dataMapKey: map
          }
        });
      }
    },
    dataMapKey: {
      get: function get() {
        return this.field.dataMapKey;
      },
      set: function set(value) {
        this.$store.commit('phoebe/updateFieldProps', {
          field: this.field,
          update: {
            dataMapKey: value
          }
        });
      }
    },
    providers: function providers() {
      return this.$store.state.phoebe.dds.data_map_info;
    },
    maps: function maps() {
      if (this.providers[this.field.dataMapProvider]) {
        var maps = this.providers[this.field.dataMapProvider]['maps'];
        return maps;
      }

      return [];
    }
  }
}));