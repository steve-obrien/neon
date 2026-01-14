FIREFLY.GETTERS = {

	/**
	 * Get the path the user is currently viewing e.g /team/profile
	 *
	 * @param state
	 * @param getters
	 * @return String
	 */
	getPath: (state, getters) => (picker) => {
		return state.pickers[picker].path;
	},

	/**
	 * Get a unique directory name for the current directory (current directory defined by the path)
	 *
	 * @param state
	 * @param getters
	 */
	getUniqueDirName: (state, getters) => (picker) => {
		var untitledFolder = 'untitled folder';
		var name = untitledFolder
		var i = 1;
		var path = getters.getPath(picker);
		while (_.find(getters.getItems(picker), {path: getters.sanitizePath(path + '/' + name)})) {
			name = untitledFolder + ' ' + i;
			i ++;
		}
		return getters.sanitizePath(path + '/' + name);
	},

	/**
	 * Sanitize a path (remove double //)
	 *
	 * @param state
	 * @param getters
	 */
	sanitizePath: (state, getters) => (path) => {
		return path.replace('//', '/')
	},

	/**
	 * Gets a folder name from it's path
	 *
	 * @param state
	 * @param getters
	 */
	getNameFromPath:  (state, getters) => (path) => {
		if (path === '/') {
			return 'Media';
		}
		return path.split('/').pop();
	},

	/**
	 * Compile breadcrumb objects from the current path
	 *
	 * @param state
	 * @param getters
	 * @return {{name:String, path: String}[]} - An array of path bit objects containing name, and path property
	 */
	getPathBits: (state, getters) => (picker) => {
		var parts = [];
		var here = getters.getPath(picker).split('/');
		for( var i = 0; i < here.length; i++ ) {
			var part = here[i];
			var link = here.slice( 0, i + 1 ).join('/');
			if (link !== '') parts.push({"name": part, "path": link});
		}
		return parts;
	},

	/**
	 * Get parent path
	 * @param state
	 * @param getters
	 */
	getParent: (state, getters) => (picker) => {
		var bits = getters.getPathBits(picker);
		return bits[bits.length-2];
	},

	/**
	 * Get the currently selected firefly file/directory item
	 *
	 * @param state
	 * @param getters
	 * @return Object
	 */
	getSelectedItem: (state, getters) => (picker) => {
		// selected item can be the last item in the list of selected.
		// Therefore the last item to be selected is the singular selectedItem (if more than one)
		var selected = state.pickers[picker].selected;
		return selected[selected.length-1];
		// return state.pickers[picker].selectedItem;
	},

	/**
	 * Whether there is currently a selected item defined for this picker
	 *
	 * @return boolean
	 */
	getHasSelectedItem: (state, getters) => (picker) => {
		return _.isDefined(state.pickers[picker].selectedItem);
	},

	/**
	 * Get all selected items
	 *
	 * @param state
	 * @param getters
	 * @return Object
	 */
	getSelectedItems: (state, getters) => (picker) => {
		return state.pickers[picker].selected;
	},

	/**
	 * Get the items currently loaded into the view (under the current path)
	 *
	 * @param state
	 * @param getters
	 * @return Function(pciker) Array of firefly media item Objects
	 */
	getItems: (state, getters) => (picker) => {
		var uploadingItems = getters.getItemsUploadingInPath(picker);
		var addUploadItems = [];
		_.each(uploadingItems, function(item) {
			if (_.find(state.pickers[picker].items, item) == null) {
				addUploadItems.push(item);
			}
		});
		// only add if they don't already exist
		var items = state.pickers[picker].items.concat(addUploadItems);
		// add sorting rules here
		return _.chain(items).sortBy('path').sortBy('file.path.name').sortBy('type').value()
	},

	getItemsDeleted: (state, getters) => (picker) => {
		return _.filter(getters.getItems(picker), {deleted:1})
	},

	/**
	 * Get items ordered by name
	 * This will order all folders by name first and position them to the tp of the list.
	 * The it filters all files
	 *
	 * @param state
	 * @param getters
	 * @return Array of firefly media item Objects
	 */
	getItemsOrderedByName: (state, getters) => (picker) => {
		var items = getters.getItems(picker);
		var folders = _.sortBy(_.filter(items, {type: 'dir'}), function(item) {
			return getters.getItemName(item);
		});
		var files = _.sortBy(_.filter(items, {type: 'file'}), function(item) {
			return getters.getItemName(item);
		});
		return folders.concat(files);
	},

	/**
	 * Get the start path of the picker specified by name
	 *
	 * @param state
	 * @param getters
	 * @return String - the start path
	 */
	getStartPath: (state, getters) => (picker) => {
		return state.pickers[picker].startPath;
	},

	/**
	 * Determine whether an item with the specified id exists in the picker items collection specified by name
	 *
	 * @param state
	 * @param getters
	 * @return Boolean
	 */
	getItemExists: (state, getters) => (picker, id) => {
		return _.find(state.pickers[picker].items, {id: id});
	},

	/**
	 * Whether a picker state exists for the given picker name
	 *
	 * @param state
	 * @param getters
	 * @return {Boolean}
	 */
	getPickerExists: (state, getters) => (picker) => {
		return _.isDefined(state.pickers[picker]);
	},

	/**
	 * Whether the item (supplied item.id) is currenctly selected
	 *
	 * @param {string} picker - the picker name
	 * @param {string} itemId - the id of the item to check
	 * @return Boolean - whether the item is selected
	 */
	getIsSelected: (state, getters) => (picker, itemId) => {
		var selectedItem = getters.getSelectedItem(picker);
		if (_.isUndefined(selectedItem))
			return false;
		return selectedItem.id == itemId ||
		// this is a bit narly
		_.isDefined(_.find(state.pickers[picker].selected, {id:itemId}))
	},

	/**
	 * Get whether the item has a registered editor component
	 *
	 * @param state
	 * @param getters
	 * @return Boolean
	 */
	getHasEditor: (state, getters) => (item) => {
		return (getters.getEditorForItem(item) !== 'firefly-editor-null');
	},

	/**
	 * Get the editor component for the specified item
	 *
	 * @param state
	 * @param getters
	 * @return {String} - component name
	 */
	getEditorForItem: (state, getters) => (item) => {
		if (getters.getIsItemImage(item))
			return 'firefly-editor-image';
		return 'firefly-editor-null';
	},

	/**
	 * Whether the passed in item is an image file
	 *
	 * @param state
	 * @param getters
	 * @return {Boolean}
	 */
	getIsItemImage: (state, getters) => (item) => {
		if (_.isUndefined(item)) return false;
		if (!item['file']) return false;
		if (!item['file']['mime_type']) return false;
		if (item.file.mime_type === 'image/svg') return false;
		return (item.file.mime_type.substr(0,5) == 'image');
	},

	/**
	 * Get the name of an item - a directory or a file
	 *
	 * @param state
	 * @param getters
	 * @return {String}
	 */
	getItemName: (state, getters) => (item) => {
		return FIREFLY.getItemName(item);
	},

	/**
	 * Whether this item's name is being edited.
	 *
	 * @param state
	 * @param getters
	 */
	getIsEditingItemName: (state, getters) => (item) => {
		return state.editingItemNameId === item.id;
	},

	/**
	 * Whether we are currently loading items
	 *
	 * @param state
	 * @param getters
	 */
	getItemsLoading: (state, getters) => (picker) => {
		return state.pickers[picker].items_loading;
	},

	/**
	 * Get an array of items currently being uploaded
	 *
	 * @param state
	 * @param getters
	 * @return Array
	 */
	getItemsUploading: (state, getters) => (picker) => {
		return state.pickers[picker].itemsUploading;
	},

	/**
	 * Get an array of items being uploaded in the current selected path
	 *
	 * @param state
	 * @param getters
	 * @return Array
	 */
	getItemsUploadingInPath: (state, getters) => (picker) => {
		return _.filter(getters.getItemsUploading(picker), {path: getters.getPath(picker)});
	},

	/**
	 * Get text showing information about a drop action
	 *
	 * @param state
	 * @param getters
	 * @return Object
	 */
	getDropMoveText: (state, getters) => (picker) => {

		var dropId = state.pickers[picker].dropOverItemId;
		if (!dropId)
			return '';
		var item = _.find(state.pickers[picker].items, {id: dropId});
		var dirName = _.isUndefined(item) ? getters.getNameFromPath(dropId) : getters.getItemName(item);
		return 'Move to "' + dirName + '"';
	},

	/**
	 * Whether the specified item id is currently being dropped over
	 * *Note* the itemId could be the uuid or the full path string
	 *
	 * @param state
	 * @param getters
	 */
	getIsItemBeingDroppedOver: (state, getters) => (picker, itemId) => {
		return state.pickers[picker].dropOverItemId == itemId;
	},

	/**
	 * Get upload stats
	 *
	 * @param state
	 * @param getters
	 * @return {Object} Object similar to pluploadQueue object http://www.plupload.com/docs/v2/QueueProgress
	 * {
	 *     size: 0, // Total queue file size.
	 *     loaded: 0, // Total bytes uploaded.
	 *     uploaded: 0, // Number of files uploaded.
	 *     failed: 0, // Number of files failed to upload.
	 *     queued: 0, // Number of files yet to be uploaded.
	 *     percent: 0, // Total percent of the uploaded bytes.
	 *     bytesPerSec: 0 // Bytes uploaded per second.
	 * }
	 */
	getUploadingInformation: (state, getters) => (picker) => {
		return state.pickers[picker].uploadStats;
	},

	getUploadingFilesStatus: (state, getters) => (picker) => {
		var data = getters.getUploadingInformation(picker);
		var total = data.uploaded + data.queued + data.failed;
		return (data.queued > 0)
			? 'Uploaded ' + data.uploaded + ' of ' + total + '. ' + _.formatBytes(data.loaded) + ' of ' + _.formatBytes(data.size)
			: false;
	},

	getTotalUploadPercent: (state, getters) => (picker) => {
		var data = getters.getUploadingInformation(picker);
		return data.percent;
	},

	/**
	 * Get the browserHistory boolean property - whether the current picker supports url browser history
	 * @param state
	 * @param getters
	 * @returns {function(*): boolean|{default: boolean, type: Boolean | BooleanConstructor}|firefly-media-browser.computed.browserHistory}
	 */
	getBrowserHistory: (state, getters) => (picker) => {
		return state.pickers[picker].browserHistory;
	}

};
