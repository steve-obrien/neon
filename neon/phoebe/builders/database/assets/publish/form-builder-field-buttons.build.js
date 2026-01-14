"use strict";

/**
 * -------------------------------------------------------------------------------------------------------------
 * Field Buttons
 * -------------------------------------------------------------------------------------------------------------
 */
Vue.component('form-builder-field-buttons', {
  template: "\n\t\t<div>\n\t\t\t<div v-for=\"(buttons, group) in groups\">\n\t\t\t\t<div class=\"hr-text\">{{group}}</div>\n\t\t\t\t<div v-for=\"btn in buttons\" :title=\"btn.name\" class=\"btn btn-default btn--component-icon\" :data-component=\"btn.component\">\n\t\t\t\t\t<i :class=\"btn.icon\"></i> {{ btn.name }}\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t",
  computed: {
    groups: function groups() {
      return _.groupBy(_.orderBy(neon.Store.state.phoebe.widgetComponents, 'order'), 'group');
    }
  }
});