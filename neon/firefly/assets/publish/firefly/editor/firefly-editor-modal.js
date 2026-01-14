/**
 * ------------------------
 * <firefly-editor-modal>
 * ------------------------
 * This component houses a firefly file editor in a modal
 * Depends on neon modal component
 */
Vue.component('firefly-editor-modal', {
	props: {
		item: {type: Object, required:true},
        settings: {type: Object, required:false},
	},
	template: `
			<component :is="getEditorComponent" :item="item" :settings="settings" @on-saved="saved"></component>
			<!--<button class="btn btn-primary" @click="doSave" >Save</button>-->
			<!--<button class="btn btn-default" @click="$emit('close')">Close</button>-->
	`,
	computed: {
		getEditorComponent: function() {
			return this.$store.getters['Firefly/getEditorForItem'](this.item);
		}
	},
	methods: {
        // Tell the editor to save
        doSave: function() {
            FIREFLY.editor.save();
        },
        saved: function(edited) {
            FIREFLY.events.$emit('edited', edited);
        }
	}
});
