FIREFLY.STATE = {
	pickers: {},
	editingItemNameId: false,
	// store file data details indexed by their uuid
	// this is essentially a per session cache
	files: {}
	// collection of items indexed by id
};

FIREFLY.STORE = {
	namespaced: true,
	strict: false,
	state: FIREFLY.STATE,
	mutations: FIREFLY.MUTATIONS,
	getters: FIREFLY.GETTERS,
	actions: FIREFLY.ACTIONS
};

neon.Store.registerModule('Firefly', FIREFLY.STORE);