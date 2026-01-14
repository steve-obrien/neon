FIREFLY.MUTATIONS = {
	/**
	 * Sets up the initial state for the picker
	 *
	 * @param state
	 * @param payload
	 * @constructor
	 */
	CREATE_PICKER: function(state, payload) {
		_.validate(payload, 'FIREFLY.MUTATIONS.CREATE_PICKER', {
			name:{type:String, required:true}
		});

		// The default state to start the picker with
		var defaults = {
			path: '/',
			startPath:'/',
			// The browser will attempt to show the file uuid in the browser
			// this will override the
			// focusFileId:null, (todo!)

			//selectedItem: {}, // to be deprecated in favor of selected array

			selected: [], // an array of selected items (multiple select) (should superseed selectedItem)
			items: [],
			items_loading: false,
			// store an array of currently uploading items
			// this lets you navigate around
			itemsUploading: [],

			/**
			 * uploadStats
			 * http://www.plupload.com/docs/v2/QueueProgress
			 */
			uploadStats: {
				size: 0, // Total queue file size.
				loaded: 0, // Total bytes uploaded.
				uploaded: 0, // Number of files uploaded.
				failed: 0, // Number of files failed to upload.
				queued: 0, // Number of files yet to be uploaded.
				percent: 0, // Total percent of the uploaded bytes.
				bytesPerSec: 0 // Bytes uploaded per second.
			},
			// The id of the item that has items dragging over it
			// if we do not know the id this may be set to the string path if it is a folder
			dropOverItemId: false,
			/**
			 * Whether to use browser history to record the position of the browser
			 * this enables back and forward browser button as well as direct links to sub folders
			 * with #/path/to/folder attached
			 * This should most likely be false for popups
			 */
			browserHistory:false,
		};

		Vue.set(state.pickers, payload.name, Object.assign({}, defaults, payload));
	},

	/**
	 * Indicates the media items are loading or not - toggles the loading state property
	 *
	 * @param state
	 * @param payload expects a loading property as a boolean value
	 * @constructor
	 */
	LOADING: function(state, payload){
		_.validate(payload, 'FIREFLY.MUTATIONS.LOADING', {
			name: {type:String, required:true},
			loading: {type:Boolean, required:true}
		});

		state.pickers[payload.name].items_loading = payload.loading;
	},

	/**
	 * Set the items to display
	 *
	 * @param state
	 * @param payload
	 * @param payload.name - The picker name
	 * @constructor
	 */
	SET_ITEMS: function(state, payload) {
		_.validate(payload, 'FIREFLY.MUTATIONS.SET_ITEMS', {
			name: {type: String, required: true},
			path: {type: String, required: true},
			items: {type: Array, required: true}
		});

		state.pickers[payload.name].path = payload.path;
		state.pickers[payload.name].items = payload.items;

	},

	/**
	 * Mark a firefly media item as currently selected
	 *
	 * @param state
	 * @param {Object} payload
	 * @param {String} payload.name
	 * @param {Object|String} payload.item the uuid or the object of the item
	 * @param {boolean} [payload.multiple] optional whether this is a multy selection (shift key or comd key ckicled for example)
	 * @param {boolean} [payload.toggle] optional whether it will toggle to unslected if the item is already selected
	 * @constructor
	 */
	SELECT_ITEM: function(state, payload) {

		if (_.isString(payload.item)) {
			// assume the uuid of the item
			var item = _.find(state.pickers[payload.name].items, {id: payload.item});
			if (_.isUndefined(item)) throw 'Cannot find item with id "' + payload.item + '"';
			payload.item = item;
		}

		if (_.isDefined(payload.multiple) && payload.multiple == true) {
			// Do not add again to the selected list if it is already in there.
			var index = _.findIndex(state.pickers[payload.name].selected, payload.item);
			if (index === -1) {
				// not in the list so lets add it to the selection
				state.pickers[payload.name].selected.push(payload.item);
			} else {
				if (payload.toggle === false)
					return;
				state.pickers[payload.name].selected.splice(index, 1)
			}
		} else {
			state.pickers[payload.name].selected = [payload.item];
		}
	},

	/**
	 * Select all items for a specified path
	 *
	 * @param state
	 * @param payload
	 * @param payload.name - The picker name
	 * @param payload.path - The path within which we want to select all items
	 * @constructor
	 */
	SELECT_ALL: function(state, payload) {
		_.validate(payload, 'FIREFLY.MUTATIONS.SELECT_ALL_BETWEEN', {
			name: {type: String, required: true},
			path: {type: String, required: true},
		});

		// get all items in the path
		var items = state.pickers[payload.name].items;
		var selected = state.pickers[payload.name].selected;
		_.each(items, function(item){selected.push(item)});
	},

	/**
	 * Select all items between the currently selected to the newly selected (payload.item) item
	 *
	 * @param state
	 * @param payload
	 * @param payload.name - The picker name
	 * @param payload.item - The item selected
	 * @constructor
	 */
	SELECT_ALL_BETWEEN: function(state, payload) {
		_.validate(payload, 'FIREFLY.MUTATIONS.SELECT_ALL_BETWEEN', {
			name: {type: String, required: true},
			item: {type: Object, required: true},
		});
		var selectedList = state.pickers[payload.name].selected;
		var items = state.pickers[payload.name].items;
		// If there is currently no selected items then we should just select this item
		if (selectedList.length === 0) {
			selectedList = [payload.item];
			return;
		}
		// there is currently selected items - we need to select all items between the last selected in the current selected
		// list up to the current selected.
		var newIndex = _.findIndex(items, payload.item);
		var currentIndex = _.findIndex(items, selectedList[selectedList.length - 1]);
		var step = (newIndex < currentIndex) ? -1 : 1;
		// remove the current selected list
		_.each(_.range(currentIndex, newIndex, step), function(i) {
			// don't add the item twice to the list
			if (_.findIndex(selectedList, items[i]) === -1)
				selectedList.push(items[i]);
		});
		if (_.findIndex(selectedList, items[newIndex]) === -1)
			selectedList.push(items[newIndex]);
	},

	/**
	 *
	 * @param state
	 * @param payload
	 * @constructor
	 */
	SELECT_NEXT_ITEM: function(state, payload) {
		_.validate(payload, 'FIREFLY.MUTATIONS.SELECT_NEXT_ITEM', {
			name: {type: String, required: true},
		});

		var pickerData = state.pickers[payload.name];
		var items = pickerData.items;
		var selectedItem = pickerData.selected[pickerData.selected.length - 1];
		// if there are no items then return
		if (items.length === 0) return;
		var currentIndex = items.indexOf(selectedItem);

		// if at the end of the array loop around back to the beginning
		// otherwise move forward one
		var selectItem = (currentIndex == items.length-1) ? items[0] : items[currentIndex + 1];
		if (typeof(selectItem) === 'object') {
			if (payload.shift)
				state.pickers[payload.name].selected.push(selectItem);
			else
				Vue.set(state.pickers[payload.name], 'selected', [selectItem]);
		}
	},

	/**
	 *
	 * @param state
	 * @param payload
	 * @constructor
	 */
	SELECT_PREVIOUS_ITEM: function(state, payload) {
		_.validate(payload, 'FIREFLY.MUTATIONS.SELECT_PREVIOUS_ITEM', {
			name: {type: String, required: true},
		});

		var pickerData = state.pickers[payload.name];
		var items = pickerData.items;
		var selectedItem = pickerData.selected[pickerData.selected.length - 1];

		// if there are no items then return
		if (items.length === 0) return;
		var currentIndex = items.indexOf(selectedItem);

		// if at beginning of the array loop back and go to the end
		var selectItem = (currentIndex === 0) ? items[items.length - 1] : items[currentIndex - 1];
		if (typeof(selectItem) === 'object') {
			if (payload.shift)
				state.pickers[payload.name].selected.push(selectItem);
			else
				Vue.set(state.pickers[payload.name], 'selected', [selectItem]);
		}
	},

	/**
	 * Unselect all items
	 *
	 * @param state
	 * @param payload
	 * @param {String} payload.name - The picker name
	 * @constructor
	 */
	UNSELECT_ITEM: function(state, payload) {
		_.validate(payload, 'FIREFLY.MUTATIONS.UNSELECT', {
			name: {type: String, required: true},
		});

		if (_.isString(payload.item)) {
			// assume the uuid of the item
			var item = _.find(state.pickers[payload.name].items, {id: payload.item});
			if (_.isUndefined(item)) throw 'Cannot find item with id "' + payload.item + '"';
			payload.item = item;
		}

		// Do not add again to the selected list if it is already in there.
		var index = _.findIndex(state.pickers[payload.name].selected, payload.item);
		if (index !== -1) {
			state.pickers[payload.name].selected.splice(index, 1)
		}
	},

	/**
	 * Unselect all items
	 *
	 * @param state
	 * @param payload
	 * @param {String} payload.name - The picker name
	 * @constructor
	 */
	UNSELECT: function(state, payload) {
		_.validate(payload, 'FIREFLY.MUTATIONS.UNSELECT', {
			name: {type: String, required: true},
		});

		state.pickers[payload.name].selected.splice(0, state.pickers[payload.name].selected.length);
	},

	/**
	 * Delete an item by id
	 *
	 * @param state
	 * @param payload
	 * @param {String} payload.name - The name of the picker
	 * @param {String} payload.id - The id of the item to delete
	 * @constructor
	 */
	DELETE_ITEM: function(state, payload) {
		_.validate(payload, {
			name: {type: String, required: true},
			id: {type: String, required: true}}, 'FIREFLY.MUTATIONS.DELETE_ITEM');

		let picker = state.pickers[payload.name];
		picker.selected.splice(_.findIndex(picker.selected, {id: payload.id}), 1);
		picker.items.splice(_.findIndex(picker.items, {id: payload.id}), 1);
	},

/*
 |------------------------------------------------------------------------------
 | MUTATIONS related to uploading files
 |------------------------------------------------------------------------------
 */

	/**
	 * When adding a file to the upload queue
	 *
	 * @param state
	 * @param {Object} payload
	 * @param {String} payload.file - The plupload file object
	 * @param {String} payload.name - The picker name
	 * @param {String} payload.path - The current path being browsed
	 * @constructor
	 */
	ADD_FILE_UPLOAD: function(state, payload) {
		_.validate(payload, {
			file: {type: Object, required: true},
			name: {type: String, required: true},
			path: {type: String, required: true}}, 'FIREFLY.MUTATIONS.ADD_FILE_UPLOAD');

		payload.file.path = payload.path;
		payload.file.mime_type = payload.file.type;
		var item = {
			id: payload.file.id,
			type: 'upload',
			file: _.pick(payload.file, ['id', 'name', 'mime_type']),
			path: payload.path,
			upload_percent: 0
		};
		state.pickers[payload.name].itemsUploading.push(item);
	},

	/**
	 * Promotes an upload file placeholder to a proper file
	 *
	 * @param state
	 * @param payload
	 * @param payload.file - The firefly media file object as returned by the server on successful upload
	 * @param payload.uploadId - The temporary upload id
	 * @param payload.name - The picker name
	 * @constructor
	 */
	FILE_UPLOADED: function(state, payload) {
		let items = state.pickers[payload.name].itemsUploading;
		let indexOfItem = _.findIndex(items, {id: payload.uploadId});
		payload.file.type = 'file';
		// remove from the uploading items
		state.pickers[payload.name].itemsUploading.splice(indexOfItem, 1);
		// add to the items array if appropriate
		// only add if the current path matches the file path
		if (payload.file.path === state.pickers[payload.name].path) {
			state.pickers[payload.name].items.push(payload.file);
		}
	},

	ADD_ITEM: function(state, payload) {
		state.pickers[payload.name].items.push(payload.item);
		state.pickers[payload.name].selected = [payload.item];
	},

	/**
	 * Show upload progress
	 *
	 * @param state
	 * @param payload
	 * @param {Object} payload.file - The file object
	 * @param payload.name - The picker name
	 * @constructor
	 */
	UPLOAD_FILE_PROGRESS: function(state, payload) {
		let items = state.pickers[payload.name].itemsUploading;
		let item = _.find(items, {id: payload.file.id});
		item.upload_percent = payload.file.percent;
	},

	/**
	 * Update the total progress
	 *
	 * @param state
	 * @param payload
	 * @param {Object} payload.total - The total object giving upload progress containing the following properties
	 *  - http://www.plupload.com/docs/v2/QueueProgress
	 * @param {number} payload.total.bytesPerSec - Bytes uploaded per second
	 * @param {number} payload.total.failed - The number of failed uploads
	 * @param {number} payload.total.loaded - Total bytes uploaded
	 * @param {number} payload.total.size - Total queue file size in bytes
	 * @param {number} payload.total.uploaded - The Number of files uploaded
	 * @param {number} payload.total.queued - Number of files yet to be uploaded
	 * @param {number} payload.total.percent - Total percent of the uploaded bytes.
	 * @constructor
	 */
	UPLOAD_TOTAL_PROGRESS: function(state, payload) {
		if (!_.has(state.pickers, [payload.name, 'uploadStats'])) return;
		state.pickers[payload.name].uploadStats = _.clone(payload.total);
	},


/*
|------------------------------------------------------------------------------
| MUTATIONS related to editing an items name
|------------------------------------------------------------------------------
*/

	/**
	 * Mark an item as being edited
	 *
	 * @param state
	 * @param payload
	 * @param {Object} payload.id - The id of the item being edited
	 * @constructor
	 */
	ITEM_NAME_EDIT_START: function(state, payload) {
		_.validate(payload, {
			id: {type: Object, required: true},
		}, 'FIREFLY.MUTATIONS.ITEM_NAME_EDIT_START');

		state.editingItemNameId = payload.id;
	},

	/**
	 * Mark globally that item editing has stopped
	 *
	 * @param state
	 * @param payload
	 * @constructor
	 */
	ITEM_NAME_EDIT_STOP: function(state, payload) {
		// don't save changes and revert
		state.editingItemNameId = false;
	},

	/**
	 * Edit the name of an item
	 *
	 * @param state
	 * @param payload
	 * @param {Object}  payload.item - the item object of which the name property is being edited
	 * @param {String}  payload.item.path - the string path of the item
	 * @param {String}  payload.item.type - the string "dir"|"file" marking the type of the item
	 * @param {String} [payload.item.file.name] - the property to be replaced by the new name if this item is of type 'file'
	 * @param {Object}  payload.name - the new name for the item
	 */
	ITEM_NAME_EDIT: function(state, payload) {
		_.validate(payload, {
			item: {type: Object, required: true},
		}, 'FIREFLY.MUTATIONS.ITEM_NAME_EDIT');

		if (payload.item.type === 'file') {
			payload.item.file.name = payload.name;
		} else {
			var bits = payload.item.path.split('/');
			bits.pop(); bits.push(payload.name);
			payload.item.path = bits.join('/');
		}
	},

	/**
	 * Mark an item as having items currently dragging over it
	 *
	 * @param state
	 * @param {Object} payload
	 * @param {String} payload.name - The name of the picker
	 * @param {String} payload.id the id of the item that has items being dragged over it
	 */
	DROP_OVER: function(state, payload) {
		_.validate(payload, {
			name: {type: String, required: true},
			id: {type: Object, required: true},
		}, 'FIREFLY.MUTATIONS.DROP_OVER');

		state.pickers[payload.name].dropOverItemId = payload.id;
	},

	/**
	 * Remove drop over information
	 *
	 * @param state
	 * @param {Object} payload
	 * @param {String} payload.name - The picker name
	 */
	DROP_OUT: function(state, payload) {
		_.validate(payload, {
			name: {type: String, required: true},
		}, 'FIREFLY.MUTATIONS.DROP_OUT');

		state.pickers[payload.name].dropOverItemId = false
	},

	/**
	 * Add file info to the state object - this forms a local file cache
	 *
	 * @param state
	 * @param payload
	 * @param payload.id - the uuid of the file
	 * @param payload.info - the info to store - as returned by firefly api file meta call
	 * @constructor
	 */
	FILE_INFO: function(state, payload) {
		// check mutation has correct payload
		_.validate(payload, {id: {type: String, required: true}, info: {type: Object, required: true}}, 'FIREFLY.MUTATIONS.FILE_INFO');
		Vue.set(state.files, payload.id, payload.info);
	},

};
