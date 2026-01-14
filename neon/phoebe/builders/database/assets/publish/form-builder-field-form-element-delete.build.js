"use strict";

Vue.component('form-builder-field-form-element-delete', Vue.extend({
  props: {
    field: Object,
    wrapperClass: String,
    btnClass: String
  },
  data: function data() {
    return {
      isDeleting: false
    };
  },
  template: "\n\t\t<button @click=\"click\" class=\"btn btn-danger\" :class=\"btnClass\"><i class=\"fa fa-trash\"></i><span v-if=\"isDeleting\">Deleting...</span></button>\n\t",
  methods: {
    click: function click(e) {
      var that = this;
      that.isDeleting = true;
      this.$store.dispatch('phoebe/deleteField', {
        field: this.field
      }).then(function () {
        that.isDeleting = false;
      }, function () {
        that.isDeleting = false;
      });
      e.preventDefault();
      return false;
    }
  }
}));