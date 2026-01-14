const PhoebePlugin = {

	getCmp: function (cmp) {
		return Vue.options.components[cmp];
	},
	getCmpOptions: function (cmp) {
		return this.getCmp().options
	},

	getPropsFromCmp: function (cmp) {
		return this.getCmpOptions().props
	}

};

PhoebePlugin.install = function (Vue, options) {
	// 1. add global method or property
	// Vue.myGlobalMethod = function () {
	// 	// some logic ...
	// }

	// 2. add a global asset
	// Vue.directive('my-directive', {
	// 	bind(el, binding, vnode, oldVnode) {
	// 		// some logic ...
	// 	}
	// })

	// 3. inject some component options
	Vue.mixin({
		props: {
			uuid: String,
			//cmp: String,
			//_parent: String,
		},
		created: function () {
			// check if we have a _uuid
			// if we do - ensure the _uuid exists in the store
			// if not lets create a new uuid and add it as a reference
			// if (this.$props._uuid === undefined) {
			// 	const cmp = Object.assign({}, this.$props, {"_uuid": neon.uuid64(), "_type": this.$options.name});
			// 	Store.commit('createCmp', cmp);
			// }
		},
		computed: {},
	});

	// 4. add an instance method
	Vue.prototype.$uuid64 = function () {
		return neon.uuid64();
	};
	Vue.prototype.$createCmp = function(cmp) {
		return {uuid: neon.uuid64(), cmp: cmp}
	};
};


if (typeof window !== 'undefined')
	window.phoebe = PhoebePlugin;

neon.phoebe = neon.phoebe || {};

neon.phoebe.getCmpObject = function(cmpName) {
	if (Vue.component(cmpName))
		return Vue.component(cmpName).options;
	// eslint-disable-next-line no-console
	console.error(cmpName + ' is not a global registered vue component');
	return {};
}

neon.phoebe.getCmpDefault = function(cmpName) {
	let defaults = {};
	console.log(this.getCmpObject(cmpName).props, 'default components');
	_.forEach(this.getCmpObject(cmpName).props, function(value, key) {
		defaults[key] = _.result(value, 'default');
	});
	console.log(this.getCmpObject(cmpName).props, defaults, 'default components');
	return defaults;
}

export default PhoebePlugin;