neon.modal = {
	zIndex: 999,
	/**
	 *
	 * @param modal
	 * @param props
	 * @param params
	 * @param events
	 */
	show: function(modal, props, params, events) {
		this.zIndex ++;
		if (typeof modal === 'string') {
			neon.modal.event.$emit('toggle', modal, true, props)
			return
		}
		const manager = neon.modal.getModalManager();
		if (manager) {
			manager.add(modal, props, params, events)
			return
		}
		console.warn(neon.modal._unmountedModalManager)
	},

	/**
	 *
	 * @param name
	 * @param params
	 */
	hide: function(name, params) {
		neon.modal.event.$emit('toggle', name, false, params)
	},

	/**
	 * Modal event bus
	 */
	event: new Vue(),

	/**
	 * Error message if mounting the modal manager is unsuccessful
	 */
	_unmountedModalManager: '[neon-modal] In order to render dynamic modals, a <neon-modal-manager> component must be present on the page',

	/**
	 * Store a NeonModalManager component instance
	 */
	_manager: undefined,

	/**
	 * Modal specific helper functions
	 */
	helpers: {}
};

neon.modal.getModalManager = function () {
	if (_.isUndefined(neon.modal._manager)) {
		const modalManager = document.createElement('div')
		document.body.appendChild(modalManager)
		new Vue({
			parent: neon.app,
			render: h => h(Vue.component('neon-modal-manager'))
		}).$mount(modalManager)
	}
	return neon.modal._manager;
};


// private
neon.modal.helpers.getType = value => {
	const floatRegexp = '[-+]?[0-9]*.?[0-9]+';
	const types = [
		{name: 'px', regexp: new RegExp(`^${floatRegexp}px$`)},
		{name: '%', regexp: new RegExp(`^${floatRegexp}%$`)},
		{name: 'px', regexp: new RegExp(`^${floatRegexp}$`)} // If no suffix specified, assigning "px"
	];

	if (value === 'auto')
		return {type: value, value: 0}
	for (var i = 0; i < types.length; i++) {
		const type = types[i]
		if (type.regexp.test(value))
			return {type: type.name, value: parseFloat(value)}
	}
	return {type: '', value: value}
}


neon.modal.helpers.parseNumber = value => {
	switch (typeof value) {
		case 'number': return { type: 'px', value }
		case 'string': return neon.modal.helpers.getType(value)
		default: return { type: '', value }
	}
}

neon.modal.helpers.validateNumber = (value) => {
	if (typeof value === 'string') {
		let _value = neon.modal.helpers.parseNumber(value)
		return (_value.type === '%' || _value.type === 'px') && _value.value > 0
	}
	return value >= 0
}

neon.modal.createEvent = (args = {}) => {
	return {
		id: neon.modal.helpers.generateId(),
		timestamp: Date.now(),
		canceled: false,
		...args
	}
};

neon.modal.helpers.generateId = function() {
	return neon.uuid64();
}

neon.modal.helpers.getMutationObserver = () => {
	if (typeof window !== 'undefined') {
		const prefixes = ['', 'WebKit', 'Moz', 'O', 'Ms']
		for (let i = 0; i < prefixes.length; i++) {
			let name = prefixes[i] + 'MutationObserver'
			if (name in window) {
				return window[name]
			}
		}
	}
	return false
}



neon.modal.getModalDialog = function (Vue) {
	if (_.isUndefined(neon.modal._dialog)) {
		const modalContainer = document.createElement('div')
		document.body.appendChild(modalContainer)
		new Vue({
			parent: neon.app,
			render: h => h('neon-dialog')
		}).$mount(modalContainer)
	}
	return neon.modal._dialog;
};
