"use strict";

/**
 * ------------------------
 * <firefly-editor-image>
 * ------------------------
 */
Vue.component('firefly-editor-null', {
  props: {
		/**
		 * The firefly media item object representing the image
		 */
		item: Object,
	},
	mounted: function() {
		FIREFLY.editor = this;
	},
	methods: {
		save() {
			this.$emit('on-saved', this.item);
		}
	},
  data: function data() {
    return {};
  },
  template: "<div>There is no supported editor for this file</div>"
});