Vue.component('neon-dialog', {
	template: `
	<neon-modal 
		name="dialog" 
		:classes="['neonModal', 'vue-dialog', this.params.class]" 
		:width="params.width"
		:height="params.height"
		:pivot-y="0.3"
		:adaptive="true"
		:clickToClose="clickToClose"
		:transition="transition"
		@before-open="beforeOpened"
		@before-close="beforeClosed"
		@opened="$emit('opened', $event)"
		@closed="$emit('closed', $event)"
	>
		<div class="dialog-content">
			{{params}} {{width}}
			<div class="dialog-c-title" v-if="params.title" v-html="params.title || ''"></div>
			<component v-if="params.component" v-bind="params.props" :is="params.component"></component>
			<div v-else class="dialog-c-text" v-html="params.text || ''"></div>
		</div>
		<div class="neonModal_buttons" v-if="buttons">
			<button v-for="(button, i) in buttons" type="button"
				:class="button.class || 'neonModal_button'" 
				:style="buttonStyle" :key="i" v-html="button.title" @click.stop="click(i, $event)">
				{{button.title}}
			</button>
		</div>
		<div v-else class="neonModal_buttonsNone vue-dialog-buttons-none"></div>
	</neon-modal>
	`,
	created() {
		neon.modal._dialog = this;
	},
	props: {
		height: {type: [Number, String], default: 'auto'},
		width: {type: [Number, String], default: 400},
		clickToClose: {type: Boolean, default: true},
		transition: {type: String, default: 'fade'}
	},
	data () {
		return {
			params: {},
			defaultButtons: [{ title: 'CLOSE' }]
		}
	},
	computed: {
		buttons () {
			return this.params.buttons || this.defaultButtons
		},
		/**
		 * Returns FLEX style with correct width for arbitrary number of
		 * buttons.
		 */
		buttonStyle () {
			return {
				flex: `1 1 ${100 / this.buttons.length}%`
			}
		}
	},
	methods: {
		beforeOpened (event) {
			window.addEventListener('keyup', this.onKeyUp)
			this.params = event.params || {}
			this.$emit('before-opened', event)
		},
		beforeClosed (event) {
			window.removeEventListener('keyup', this.onKeyUp)
			this.params = {}
			this.$emit('before-closed', event)
		},
		click (i, event, source = 'click') {
			const button = this.buttons[i]
			if (button && typeof button.handler === 'function') {
				button.handler(i, event, { source })
			} else {
				neon.modal.hide('dialog')
			}
		},
		onKeyUp (event) {
			if (event.which === 13 && this.buttons.length > 0) {
				const buttonIndex =
					this.buttons.length === 1
						? 0
						: this.buttons.findIndex(button => button.default)
				if (buttonIndex !== -1) {
					this.click(buttonIndex, event, 'keypress')
				}
			}
		}
	}
});