"use strict";

Vue.component('form-builder-field-form-element-list-items', Vue.extend({
  props: {
    field: Object
  },
  template: "\n\t\t\t<div class=\"form-group\">\n\t\t\t\t<label class=\"control-label\" for=\"form-text\">Options <button type=\"button\" @click=\"addOption()\" class=\"btn btn-default\" style=\"border-radius:100px;\">+</button></label>\n\t\t\t\t<draggable v-model=\"choicesList\" :options=\"dragOptions\">\n\t\t\t\t\t<div class=\"input-group\" v-for=\"(item, index) in choicesList\" style=\"padding:5px 0;\">\n\t\t\t\t\t\t<div class=\"dragGrip\" style=\"display: table-cell;\"></div>\n\t\t\t\t\t\t<input placeholder=\"Key, e.g GB\" :value=\"item.key\" @input=\"updateOptionKey($event, item, index)\" type=\"text\" name=\"options\" class=\"form-control\" style=\"width:50%\">\n\t\t\t\t\t\t<input placeholder=\"Description, e.g Great Britain\" :value=\"item.value\" @input=\"updateOptionValue($event, item, index)\" type=\"text\" name=\"options\" class=\"form-control\" style=\"width:50%\">\n\t\t\t\t\t\t<span :disabled=\"field.items.length == 1\" @click=\"removeOption(item, index)\" data-remove-btn=\"\" class=\"btn btn-default input-group-addon\" style=\"padding:8px 8px;\">-</span>\n\t\t\t\t\t\t<span @click=\"addOption(item, index)\" data-add-btn=\"\" class=\"btn btn-default input-group-addon \" style=\"padding:8px 8px;border-radius: 0 3px 3px 0;\">+</span>\n\t\t\t\t\t</div>\n\t\t\t\t</draggable>\n\t\t\t</div>\n\t\t",
  computed: {
    dragOptions: function dragOptions() {
      return {
        animation: 100,
        //group: 'description',
        //disabled: !this.editable,
        ghostClass: 'ghost'
      };
    },

    /**
     * gets the list of choices as an array of
     * [{key:'the key', value:'the value}, ...]
     */
    choicesList: {
      get: function get() {
        var items = [];

        _.each(this.field.items, function (value, key) {
          items.push({
            key: key,
            value: value
          });
        });

        return items;
      },
      set: function set(list) {
        this.$store.commit('phoebe/updateFieldProps', {
          field: this.field,
          update: {
            items: this.convertItemsArrayToObject(list)
          }
        });
      }
    }
  },
  methods: {
    addOption: function addOption(item, index) {
      var items = this.choicesList;
      var newNumber = items.length + 1;
      var newItem = {
        key: "Key " + newNumber,
        value: "Option " + newNumber
      };

      if (_.isUndefined(item) && _.isUndefined(index)) {
        items.unshift(newItem);
      } else {
        items.splice(index + 1, 0, newItem);
      } //'phoebe/updateFieldAddItem'


      this.$store.commit('phoebe/updateFieldProps', {
        field: this.field,
        update: {
          items: this.convertItemsArrayToObject(items)
        }
      });
    },
    removeOption: function removeOption(item, index) {
      var items = this.choicesList;
      if (items.length > 1) items.splice(index, 1); //'phoebe/updateFieldRemoveItem'

      this.$store.commit('phoebe/updateFieldProps', {
        field: this.field,
        update: {
          items: this.convertItemsArrayToObject(items)
        }
      });
    },

    /**
     * Convert from array of objects to a single keyed object
     * @param items
     * @returns {Object}
     */
    convertItemsArrayToObject: function convertItemsArrayToObject(items) {
      var newItems = {};

      _.each(items, function (item) {
        newItems[item.key] = item.value;
      });

      return newItems;
    },
    updateOptionKey: function updateOptionKey(event, item, index) {
      var items = this.choicesList;
      items[index].key = event.target.value; // 'phoebe/updateFieldItemKey'

      this.$store.commit('phoebe/updateFieldProps', {
        field: this.field,
        update: {
          items: this.convertItemsArrayToObject(items)
        }
      });
    },
    updateOptionValue: function updateOptionValue(event, item, index) {
      var items = this.choicesList;
      items[index].value = event.target.value; // 'phoebe/updateFieldItemValue'

      this.$store.commit('phoebe/updateFieldProps', {
        field: this.field,
        update: {
          items: this.convertItemsArrayToObject(items)
        }
      });
    }
  }
}));