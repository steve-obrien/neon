FIREFLY.ACTIONS = {

	/**
	 * Create a new picker with the specified name
	 *
	 * @param {Object} context - The store
	 * @param {Object} payload - The payload object containing the data for the action
	 * @param {String} payload.name - The required name of the new picker
	 * @constructor
	 */
	PICKER: function (context, payload) {
		_.validate(payload, {name: {type: String, required: true}}, 'FIREFLY_ACTIONS.PICKER');
		context.commit('CREATE_PICKER', payload);
	},

	/**
	 * Load items for a specific path for a specified picker
	 *
	 * @param {Object} context - The store
	 * @param {object} payload - The payload object containing the data for the action
	 * @param {String} payload.name - The name of the picker (the pickers state object)
	 * @param {String} payload.path - The firefly media path to load items for
	 * @param {String} payload.pushState - if false prevents pushing browser history
	 * @constructor
	 */
	LOAD_ITEMS: function(context, payload) {
		_.validate(payload, {
			name: {type: String, required: true},
			path: {type: String, required: true}}, 'FIREFLY_ACTIONS.LOAD_ITEMS');

		// remove the hash
		if (payload.path[0]==='#') payload.path = payload.path.substr(1);
		if (context.state.pickers[payload.name].browserHistory !== false && payload.pushState!==false)
			window.history.pushState(null, null, window.location.pathname +'#'+payload.path);


		context.commit('LOADING', {loading: true, name: payload.name});

		// $.post(neon.url('/firefly/api/media/list', {path: payload.path, deleted: 1}));
		$.ajax({
			url: neon.url('/firefly/api/media/list', {path: payload.path, deleted: 1}),
			type: 'GET',
			cache: false,
			data: {path: payload.path, deleted: 1},
			headers: {},
			dataType: 'json',
		})
		.done(function (response) {
			var data = response.data;
			context.commit('SET_ITEMS', {path: data.path, items: data.items, name: payload.name});
		})
		.always(function () {
			context.commit('LOADING', {loading: false, name: payload.name});
		});
	},

	/**
	 * Create a new directory with an unique autho generated name unititled name
	 *
	 * @param {Object} context - The store
	 * @param {object} payload - The payload object containing the data for the action
	 * @param {Object} payload.name - The name of the picker (the pickers state object)
	 * @constructor
	 */
	CREATE_DIRECTORY: function(context, payload) {
		return new Promise((resolve, reject) => {
			_.validate(payload, {
				name: {type: String, required: true},
				path: {type: String, required: true}}, 'FIREFLY_ACTIONS.CREATE_DIRECTORY');

			var newPath = context.getters.getUniqueDirName(payload.name);

			var url = neon.url('/firefly/api/media/create-directory');
			var currentPath = context.getters.getPath(payload.name);
			$.ajax({method: 'post', url: url, data: {path: newPath}, dataType: 'json'})
				.done(function(response) {
					// the directory has just been created - ensure the deleted key exists and is false
					response[0].deleted = 0;
					response[0].file = null;
					console.log(response[0], 'response');
					context.commit('ADD_ITEM', {name: payload.name, item: response[0]});
					resolve(response[0]);
				})
				.fail(function (error) {
					console.error(error);
					reject(error);
				})
		})
	},

	/**
	 * payload object should contain an id property of the item to delete
	 *
	 * @param {Object} context - The store
	 * @param {object} payload - The payload object containing the data for the action
	 * @param {String} payload.id - The id of the item to delete
	 * @param {String} payload.name - The name of the current picker
	 */
	DELETE_ITEM: function(context, payload) {
		_.validate(payload, {
			name: {type: String, required: true},
			id: {type: String, required: true}}, 'FIREFLY.ACTIONS.DELETE_ITEM');

		var url = neon.url('/firefly/api/media/delete', {id: payload.id});
		$.ajax({type: "DELETE", url: url, dataType: 'json'})
			.done(function() { context.commit('DELETE_ITEM', payload); })
			.fail(function (response) {_.error(response);});
	},

	/**
	 * payload object should contain an id property of the item to delete
	 *
	 * @param {Object} context - The store
	 * @param {object} payload - The payload object containing the data for the action
	 * @param {String} payload.id - The id of the item to delete
	 * @param {String} payload.name - The name of the current picker
	 */
	DELETE_SELECTED: function(context, payload) {
		_.validate(payload, {
			name: {type: String, required: true},
		}, 'FIREFLY.ACTIONS.DELETE_ITEM');

		let selectedItems = context.getters.getSelectedItems(payload.name);
		_.each(selectedItems, function(item) {
			var url = neon.url('/firefly/api/media/delete', {id: item.id});
			$.ajax({type: "DELETE", url: url, dataType: 'json'})
				.done(function () {
					context.commit('DELETE_ITEM', {name: payload.name, id: item.id});
				})
				.fail(function (response) {
					_.error(response);
				});
		});

	},

/*
|------------------------------------------------------------------------------
| ACTIONS related to editing an items name
|------------------------------------------------------------------------------
*/

	/**
	 * This action simply marks an item as having its name in edit mode
	 *
	 * @param {Object} context - The store
	 * @param {object} payload - The payload object containing the data for the action
	 * @param {Object} payload.id - The id of the item being edited
	 * @constructor
	 */
	ITEM_NAME_EDIT_START: function(context, payload) {
		_.validate(payload, {
			id: {type: String, required: true},
		}, 'FIREFLY.ACTIONS.ITEM_NAME_EDIT_START');

		context.commit('ITEM_NAME_EDIT_START', {id: payload.id});
	},

	/**
	 * Update the name of a media item
	 *
	 * @param {Object} context - The store
	 * @param {object} payload - The payload object containing the data for the action
	 * @param {Object} payload.item - The firefly media item object whose name we wish to change
	 * @param {String} payload.name - The new name of the firefly media item
	 * @constructor
	 */
	ITEM_NAME_EDIT: function(context, payload) {
		_.validate(payload, {
			item: {type: Object, required: true},
			name: {type: String, required: true}
		}, 'FIREFLY.ACTIONS.ITEM_NAME_EDIT');

		var url = neon.url('/firefly/api/media/rename', {id: payload.item.id, name: payload.name});

		// don't update if the name is the same as the current name:
		if (payload.name === '') {
			context.commit('ITEM_NAME_EDIT_STOP');
			return;
		}

		if (FIREFLY.getItemName(payload.item) !== payload.name) {
			$.ajax({type: "POST", url: url, dataType: 'json'})
				.done(() => { context.commit('ITEM_NAME_EDIT', {item: payload.item, name: payload.name}); context.commit('ITEM_NAME_EDIT_STOP'); })
				.fail(() => { context.commit('ITEM_NAME_EDIT_STOP'); });
		} else {
			// there is no need to update the item so lets cancel editing
			context.commit('ITEM_NAME_EDIT_STOP');
		}

	},

	/**
	 * This action simply marks globally that editing has stopped
	 *
	 * @param {Object} context - The store
	 * @param {object} payload - The payload object containing the data for the action
	 * @constructor
	 */
	ITEM_NAME_EDIT_STOP: function(context, payload) {
		context.commit('ITEM_NAME_EDIT_STOP');
	},

	/**
	 * Promotes an upload file placeholder to a proper file
	 *
	 * @param {Object} context - The store
	 * @param {object} payload - The payload object containing the data for the action
	 * @param payload.file - The firefly media file object as returned by the server on successful upload
	 * @param payload.uploadId - The temporary upload id
	 * @param payload.name - The picker name
	 * @constructor
	 */
	FILE_UPLOADED: function(context, payload) {
		context.commit('FILE_UPLOADED', payload)
	},

	/**
	 * Thr action to call when items are dropped over a drop zone
	 * Note: this does not relate to drag and drop from desktop or for files - only existing firefly media items - for
	 * example rearranging and reordering or most typically dropping items into a folder.
	 *
	 * @param {Object} context - The store
	 * @param {object} payload - The payload object containing the data for the action
	 * @param {object} payload.into - The uuid or path of the item the items have been dropped into
	 * @param {object} payload.items[] - An array of the item objects dropped
	 * @param {string} payload.name - The picker name
	 *
	 * ```js
	 * // example payload
	 * {
	 *     into: 'qwertyuiopasdfghjklzxc' // or could be a path like '/my folder/things'
	 *     items: [
	 *         {id: '1234567890qwertyuiopas', ...},
	 *     ],
	 *     name: 'media'
	 * }
	 * ```
	 *
	 * @constructor
	 */
	ITEMS_DROPPED: function(context, payload) {
		// for now lets remove the items
		var ids = _.map(payload.items, function(item){return item.id});

		var url = neon.url('/firefly/api/media/move');
		$.ajax({type: "POST", data:{items:ids, into: payload.into}, url: url, dataType: 'json'})
			.done((result) => {
				console.log(result);
				_.each(ids, (id) => {
					// if the response
					var success = _.get(result, [id, 'success']);
					if (_.isDefined(id) && success)
						context.commit('DELETE_ITEM', {name: payload.name, id: id})
				})
			})
			.fail((response) => { _.error(response) });
	},

	/**
	 *
	 * @param context
	 * @param id - the uuid of the firefly file manager file
	 * @constructor
	 */
	FILE_INFO: function(context, {id, url}) {
		var sanitisedUrl = neon.url((url || '/firefly/api/file/meta'), {id: id});
		return new Promise(function (resolve, reject) {
			$.ajax({type: "GET", url: sanitisedUrl, dataType: 'json'})
				.done(function (data) {
					context.commit('FILE_INFO', {id: id, info: data});
					resolve(data);
				})
				.fail((response) => {
					_.error(response);
					reject(response);
				});
		});
	},

	/**
	 *
	 * @param context
	 * @param id - the uuid of the firefly file manager file
	 * @constructor
	 */
	FIND_REFERENCES: function(context, {uuid}) {
		var sanitisedUrl = neon.url(('/firefly/api/media/find-references'), {uuid: uuid});
		return new Promise(function (resolve, reject) {
			$.ajax({type: "GET", url: sanitisedUrl, dataType: 'json'})
				.done(function (data) {
					resolve(data);
				})
				.fail((response) => {
					_.error(response);
					reject(response);
				});
		});
	}

};
