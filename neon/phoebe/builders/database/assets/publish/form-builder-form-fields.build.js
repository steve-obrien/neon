"use strict";

/* -------------------------------------------------------------------------------------------------------------
 * Fields List
 * -------------------------------------------------------------------------------------------------------------
 */
Vue.component('form-builder-form-fields', {
  template: "\n\t\t<div class=\"formBuilder_fields\" ref=\"drop\">\n\t\t\t<div v-if=\"form.fields.length==0\" class=\"form-group\"><p>Drag and drop fields here</p></div>\n\t\t\t<div v-for=\"field in form.fields\" @click.native=\"select(field)\"\n\t\t\t\t:is=\"widget(field)\"\n\t\t\t\tv-bind=\"field\"\n\t\t\t\tv-if=\"!(field.deleted == '1')\"\n\t\t\t\t:key=\"field.name\"\n\t\t\t\t:data-ref=\"field.name\"\n\t\t\t\t:ref=\"field.name\"\n\t\t\t\tclass=\"formBuilder_field\"\n\t\t\t\t:class=\"{ 'm_selected': isSelected(field) }\">\n\t\t\t\t<div class=\"form-group_controls\" >Error loading this widget. No component exists with the name: <code>{{widget(field)}}</code></div>\n\t\t\t</div>\n\t\t</div>\n\t\t",
  props: {
    form: {
      type: Object,
      required: true
    }
  },
  mounted: function mounted() {
    $(this.$refs.drop).sortable({
      helper: 'clone',
      axis: "y",
      forcePlaceholderSize: true,
      placeholder: 'component-drop-marker',
      stop: this.stop,
      start: function start(e, ui) {
        ui.placeholder.height(ui.item.outerHeight());
      },
      over: function over() {},
      receive: this.receive
    }).disableSelection();
  },
  methods: {
    select: function select(field) {
      this.$store.commit('phoebe/setEditingField', {
        field: field,
        edit_part: 'label'
      });
    },
    isSelected: function isSelected(field) {
      return this.$store.state.phoebe.editingField.name === field.name;
    },
    widget: function widget(field) {
      return field.class.replace(/\\/g, '-').toLowerCase();
    },
    receive: function receive(event, ui) {
      var type = ui.item.attr('data-component');
      var $field = $('<div class="formBuilder_field .m_loading">Loading...</div>');
      var index = $(ui.helper).index();
      $(ui.helper).replaceWith($field); // could: go off and get the field html via ajax
      // Add a new field to the form

      this.$store.commit('phoebe/addField', {
        form: this.form,
        fieldType: type,
        at: index,
        forceEdit: true
      });
      $field.remove();
      var that = this;
      Vue.nextTick(function () {
        // DOM updated
        that.updateFieldOrder();
      });
    },

    /**
     * Update function called after sorting occurs
     * @param e
     * @param ui
     * @returns {boolean}
     */
    stop: function stop(e, ui) {
      this.updateFieldOrder();
      return true;
    },
    updateFieldOrder: function updateFieldOrder() {
      // eww get the item from the DOM
      var that = this;
      $('.formBuilder_field').each(function (index, item) {
        var name = $(item).attr('data-ref');

        var field = _.find(that.form.fields, function (item) {
          return item.name == name;
        }); // field.order = index;


        that.$store.commit('phoebe/updateFieldProps', {
          field: field,
          update: {
            order: index
          }
        });
      });
    }
  }
});