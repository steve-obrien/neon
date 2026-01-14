"use strict";

/**
 * ------------------------
 * <firefly-editor-modal>
 * ------------------------
 * This component houses a firefly file editor in a modal
 * Depends on neon modal component
 */
Vue.component('firefly-editor-modal', {
  props: {
    item: {
      type: Object,
      required: true
    },
    settings: {
      type: Object,
      required: false
    }
  },
  template: "\n\t\t\t<component :is=\"getEditorComponent\" :item=\"item\" :settings=\"settings\" @on-saved=\"saved\"></firefly-editor-image>\n\t\t\t<!--<button class=\"btn btn-primary\" @click=\"doSave\" >Save</button>-->\n\t\t\t<!--<button class=\"btn btn-default\" @click=\"$emit('close')\">Close</button>-->\n\t",
  computed: {
		getEditorComponent: function() {
			return this.$store.getters['Firefly/getEditorForItem'](this.item);
		}
	},
  methods: {
    // Tell the editor to save
    doSave: function doSave() {
      FIREFLY.editor.save();
    },
    saved: function saved(edited) {
      FIREFLY.events.$emit('edited', edited);
    }
  }
});