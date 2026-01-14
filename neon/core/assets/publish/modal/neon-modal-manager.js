Vue.component('neon-modal-manager', {
	template: `
		<div id="neonModalContainer">
			<neon-modal
				v-for="modal in modals"
				:key="modal.name"
				v-bind="modal.modalAttrs"
				v-on="modal.modalListeners"
				@closed="remove(modal.name)"
				@close="hide(modal.modalAttrs.name)"
			>
				<component style=""
					:is="modal.component"
					v-bind="modal.componentAttrs"
					v-on="$listeners"
					@close="hide(modal.modalAttrs.name)"
				/>
			</neon-modal>
		</div>
	`,
	data () {
		return {
			modals: []
		}
	},
	created () {
		neon.modal._manager = this
	},
	methods: {
		hide(modalName) {
			neon.modal.hide(modalName)
		},
		add(component, componentAttrs = {}, modalAttrs = {}, modalListeners) {
			const id = neon.modal.helpers.generateId();
			const name = modalAttrs.name || id
			let foundModal = _.find(this.modals, function(m) {return m.name === name});
			if (foundModal) {
				Object.assign(foundModal, {
					componentAttrs: componentAttrs,
					modalAttrs: modalAttrs,
					modalListeners: modalListeners
				});
				neon.modal.show(name);
				return;
			}
			var modal = {
				name,
				modalAttrs: {...modalAttrs, name},
				modalListeners,
				component,
				componentAttrs
			};
			this.modals.push(modal);
			this.$nextTick(() => {
				neon.modal.show(name)
			})
		},
		remove(name) {

		}
	}
});