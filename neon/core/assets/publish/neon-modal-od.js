neon.modal = {
	show: function(component, options) {

	},
	dialog: function(title, text, buttons) {

	}
};

Vue.component('neon-dynamic-component', {
	props: {
		/**
		 * {string} component.name - The name of the component to create inside the modal
		 * {Object} component.props - Optional props to pass to the component
		 */
		component: {
			type: Object,
			default: function() {
				return {
					name: '',
					props: {}
				}
			}
		},
	},
	render: function(createElement) {
		return createElement(this.component.name, {props: this.component.props});
	}
});
/**
 * ------------------------
 * <firefly-modal>
 * ------------------------
 */
Vue.component('neon-modal', {
	props: {
		component: {
			type: Object,
			default: function () {
				return {name: '', props:{}}
			}
		}
	},
	template: `
		<neon-dynamic-component :component="component" ></neon-dynamic-component>
	`
});

/**
 * ------------------------
 * <firefly-modal-wrap>
 * ------------------------
 */
Vue.component('neon-modal-wrap', {
	template: `
	<transition name="modal">
		<div class="fireflyModal" @click.self="$emit('close')" >
			<div class="fireflyModal_wrapper" @click.self="$emit('close')">
				<div class="fireflyModal_container">
					<button style="position: absolute; top: 0; right: 0; z-index: 10000;" type="button" class="close pull-right" @click.prevent="$emit('close')" aria-label="Close"><span aria-hidden="true">×</span></button>
					<div class="fireflyModal_header" >
						<slot name="header"></slot>
					</div>
					<div name="body" class="fireflyModal_body" >
						<slot></slot>
					</div>
					<div class="fireflyModal_footer fireflyModal_footerButtons">
						<slot name="footer">
							<button class="btn btn-default" @click.prevent="$emit('close')">Close</button>
						</slot>
					</div>
				</div>
			</div>
		</div>
	</transition>
	`
});

