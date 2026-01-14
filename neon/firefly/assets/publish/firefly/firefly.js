const FIREFLY = {
	MUTATIONS: {},
	ACTIONS: {},
	STATE: {},
	GETTERS: {},

	editor: {},

	// firefly specific global component event buss.
	events: new Vue(),

	/**
	 * Launch a new firefly picker
	 *
	 * @param {String} name - The name of the picker
	 * @param {Function} selected - A callback function to call when an image is selected
	 * @param {Object} pickerOptions - An object of firefly picker options
	 * @param {String} pickerOptions.startPath - The start directory to open
	 * @param {String} pickerOptions.selected - The id of the item to select - does nothing if it does not exist in the start folder
	 * @param {String} modalOptions.events - The id of the item to select - does nothing if it does not exist in the start folder
	 */
	picker: function (name, selected, pickerOptions, modalOptions={}, modalEvents={}) {
		pickerOptions = pickerOptions || {};
		if (_.isUndefined(name))
			throw Error('A name must be specified for the picker');
		neon.Store.dispatch('Firefly/PICKER', _.extend({name: name}, pickerOptions));
		neon.modal.show(
			// component to embed
			Vue.component('firefly-picker-modal'),
			// firefly-picker-modal component options
			 _.extend({name: name}, pickerOptions), 
			 // modal options
			_.extend({
				name: name, width:'90%', height: '90%', draggable:true, resizable:true,
				buttons: [{title: 'Close'}, {
					title: 'Select',
					disabled: () => { 
						return !neon.Store.getters['Firefly/getSelectedItem'](name);
					},
					handler: () => {
						FIREFLY.events.$emit('selected', neon.Store.getters['Firefly/getSelectedItem'](name));
					}
				}],
			}, modalOptions),
			// modal events
			modalEvents
		);
		this.events.$once('selected', (item) => {
			// close the modal
			neon.modal.hide(name);
			// call the user defined callback - deliver the selected item
			selected(item);
		});

	},

	pickerMultiple: function (name, selected, pickerOptions) {
		pickerOptions = pickerOptions || {};
		if (_.isUndefined(name))
			throw Error('A name must be specified for the picker');
		neon.Store.dispatch('Firefly/PICKER', _.extend({name: name}, pickerOptions));
		neon.modal.show(Vue.component('firefly-picker-modal'), _.extend({name: name}, pickerOptions), {
			name: name, width:'90%', height: '90%',
			buttons: [
				{title: 'Close'},
				{
					title: 'Select',
					disabled: () => { return !neon.Store.getters['Firefly/getSelectedItem'](name); },
					handler: () => { FIREFLY.events.$emit('selected', neon.Store.getters['Firefly/getSelectedItems'](name)); }
				},
			]
		});
		this.events.$once('selected', (items) => {
			// close the modal
			neon.modal.hide(name);

			// make it consistently return an array - firefly returns an object for individual items and an array
			// for multiple selections
			if (!_.isArray(items)) {
				selected([items]);
				return;
			}
			// call the user defined callback - deliver the selected item
			selected(items);
		});
	},

	/**
	 * Launch an editor for an item
	 *
	 * Example usage:
	 *
	 * ```js
	 * FIREFLY.edit(item, {cropAuto:false, cropWidth:100, cropHeight:100}, function(newItem){
	 *   // newItem object or modified item
	 * })
	 * ```
	 *
	 * @param {Object} item - The firefly media item object to launch an editor for
	 * @param {Object} settings - The settings to pass to the editor
	 * @param {Function} savedCallback - A callback function called when an editor saves a file
	 * the modified file item object will be returned as the first agrument.
	 * Note that this may be a new item with a new id entirely as some times
	 * new versions are created rather than modifying the original, if this is the case then
	 * the original information will typically be stored in the item objects meta information.
	 */
	edit: function (item, settings, savedCallback) {
		neon.modal.show(
			Vue.component('firefly-editor-modal'),
			{item: item, settings: settings},
			{
				name: 'fireflyEditor',
				width:'99%',
				height: '99%',
				buttons: [
					{title: 'Close'},
					{
						title: 'Save',
						handler: function() { FIREFLY.editor.save(); },
						disabled: function() { return FIREFLY.editor.croppingAjax }
					}
				]
			},
		);
		this.events.$once('edited', (item) => {
			// close the modal
			neon.modal.hide('fireflyEditor');
			// call the user defined callback - deliver the selected item
			savedCallback(item);
		});
	},

	unsplash: function (importCallback) {
		neon.modal.show(
			Vue.component('firefly-unsplash-modal'),
			{ importCallback: importCallback },
			{
				name: 'fireflyUnsplash',
				width:'90%',
				height: '90%',
				buttons: [
					{title: 'Close'},
					{
						title: 'Save',
						handler: function() {  },
					}
				]
			},
		);
	},

	preview: function (item) {
		neon.Store.commit('SHOW_MODAL', {
			component: 'firefly-preview',
			props: {item: item}
		});
	},

	/**
	 * Return a firefly image url for the specified id
	 *
	 * @return {String} url of the image
	 */
	getImageUrl: function(id) {
		return neon.base() + '/firefly/file/get?id=' + id
	},

	getImageSrcset: function(id) {
		var base = this.getImageUrl(id);
		var sizes = [200,400,640,768,800,1024,1280,1536,1700,2000,2400,2592];
		var srcset = '';
		_.each(sizes, size => {srcset += base+'&w='+size+'&q=90 '+size+'w, ';});
		return srcset;
	},

	/**
	 * Get the name of an item
	 *
	 * @param {Object} item - A firefly media item object
	 * @returns {String} - The items name
	 */
	getItemName: function(item) {
		if (!item || !item.path)
			return 'unknown';
		if (item.type === 'file' || item.type === 'upload')
			return item.file.name;
		return item.path.split('/').pop();
	}

};

/**
 * Create a new plupload instance
 * This is currently confused with firefly picker - the plupload should carve out its own position in the global state
 * each plupload instance can be indexed by a passed in id.
 *
 * Example usage inside a component where the component "knows" the element id's:
 *
 * ```js
 * var uploader = FIREFLY.createPlupload(this.name, this.path, {
 *     container: this.container,
 *     drop_element: this.drop,
 *     browse_button: this.browse,
 *     // ... Other plupload options ... //
 *     // can pass function in to listen to the following events:
 *     onFileUploaded: function (up, file, result){},
 *     onFilesAdded: function (up, files) {},
 *     onBeforeUpload: function(up, file) {},
 *     onUploadProgress: function (up, file) {},
 *     onUploadComplete: function (up, files) {},
 *     onFilesRemoved: function(up, files) {},
 *     onStateChanged: function(up) {},
 *     onError: function (up, err) {},
 * });
 * ```
 */
FIREFLY.createPlupload = function(name, settings) {
	// create unique state
	// neon.Store.state.
	// Defense against monkey brains
	if (_.isUndefined(settings.browse_button))
		throw 'Missing required property "browse_button". Plupload requires the "browse_button" options set';
	var dontLeaveMe =  function() {return 'Are you sure you want to leave? Current uploads will be lost';};

	var options = _.defaults(settings, {
		// Try to load the HTML5 engine and then, if that's
		// not supported, the Flash fallback engine.
		runtimes: "html5,html4",
		url: neon.url('/firefly/api/media/upload'),
		// The maximum chunk of a file that will be uploaded at a time
		chunk_size: '500kb',

		// validation filters
		// Plupload can resize images on the client side
		// it will resize an image up to a maximum resolution of 6500 x 6500 this is an assumed safe client limit.
		// If an image is above this then plupload will not attempt to resize the image and will upload it.

		// https://www.plupload.com/docs/v2/Image-Resizing-on-Client-Side
		// plupload will resize maximum 6500x6500 - this is a reasonably safe intern
		// We assume it to be 6500 by 6500 pixels (about 42MP).
		// I say assume, because there's no way to figure out the actual top line or detect when browser (or its add-on) runs out of memory - if this happens it simply hangs (the best case).
		// So we came up with the safe margin, where browser can still reliably operate (read - breathe) and if either side of the image is larger than that margin, Plupload will simply bail out and upload the image as is, without any tampering.
		// however anything close to 6000x6000 risks breaking the server process if memory limit is close to 128Meg (minus 10 meg for process)
		resize: {
			width: 4000,
			height: 4000
		},
		filters: {
			// as plupload will not auto resize images larger than 6500x6500 we need to throw an error
			// when uploading images larger than 6500x6500 resolution
			max_img_resolution: 6500*6500 // maxResolution(megaBytesAvailableForImage),
		 	// mime_types: [{title : "Image files", extensions : "jpg,jpeg,gif,png,svg"}]
		},
		// add X-CSRF-TOKEN in headers to pass CSRF validation
		headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},

		onFileUploaded: function (up, file, result) {
			var response = JSON.parse(result.response);
			neon.Store.dispatch('Firefly/FILE_UPLOADED', {
				file: response.result,
				uploadId: file.id,
				name: name
			});
		},
		onFilesAdded: function (up, files) {
			_.each(files, function(file) {
				var path = neon.Store.getters['Firefly/getPath'](name);
				neon.Store.commit('Firefly/ADD_FILE_UPLOAD',  {file: file, name: name, path: path});
			});
			up.start();
		},
		onBeforeUpload: function(up, file) {
			if (_.isUndefined(file.path))
				file.path = '/';
			up.settings.multipart_params = {path: file.path};
			return true;
		},
		onUploadProgress: function (up, file) {
			neon.Store.commit('Firefly/UPLOAD_TOTAL_PROGRESS', { total: up.total, name: name });
			neon.Store.commit('Firefly/UPLOAD_FILE_PROGRESS', { file: file, name: name });
		},
		onUploadComplete: function (up, files) {
			neon.Store.commit('Firefly/UPLOAD_TOTAL_PROGRESS', { total: up.total, name: name });
		},
		onFilesRemoved: function(up, files) {},
		onStateChanged: function(up) {},
		onError: function (up, err) {
			if (err.code === plupload.IMAGE_DIMENSIONS_ERROR) {
				neon.modal.show({props:{error:''}, template:`<div style="padding:20px;" v-html="error"></div>`}, {error:err.message});
			}
			FIREFLY.events.$emit('uploadError', {name: name, error: err, uploader: up});
			_.error("\nError #" + err.code + ": " + err.message);
		}
	});
	var uploader = new plupload.Uploader(options);
	uploader.init();
	// let the store know we have added a file to be uploaded.
	uploader.bind('FilesAdded', options.onFilesAdded);
	// Called while file is being uploaded
	uploader.bind('UploadProgress', options.onUploadProgress);
	// When the file has been successfully uploaded
	uploader.bind('FileUploaded', options.onFileUploaded);
	// Before upload starts - can return false to stop upload
	uploader.bind('BeforeUpload', function(up, files) {
		// add navigation warning during upload
		// this will prevent the page redirecting
		window.addEventListener('beforeunload', dontLeaveMe);
		var ret = options.onBeforeUpload(up, files);
		// if we don't explicitly return false from onBeforeUpload handler assume we want to continue execution
		return (ret === false) ? false : true;
	});
	uploader.bind('UploadComplete', function(up, files) {
		options.onUploadComplete(up, files);
		// Called when all files are either uploaded or failed
		// remove navigation warning
		window.removeEventListener('beforeunload', dontLeaveMe);
	});
	uploader.bind('FilesRemoved', options.onFilesRemoved);
	uploader.bind('StateChanged', options.onStateChanged);
	uploader.bind('Error', options.onError);

	return uploader;
};

plupload.addFileFilter('max_img_resolution', function(maxRes, file, cb) {
	if (['jpg', 'jpeg', 'png'].indexOf(file.name.split('.').pop()) === -1) {
		cb(true);
		return;
	}
	var self = this, img = new moxie.image.Image();
	function finalize(result) {

			// cleanup
		img.destroy();
		img = null;

		// if rule has been violated in one way or another, trigger an error
		if (!result) {
			self.trigger('Error', {
				code : plupload.IMAGE_DIMENSIONS_ERROR,
				message : "<h2>Resolution exceeds the allowed limit of " + maxRes  + " pixels.</h2> <p>Try resizing the image and uploading again.</p>",
				file : file
			});
		}
		// the image will be resized if bigger than 5000x5000
		cb(result);
	}

	img.onload = function() {
		// check if resolution cap is not exceeded
		finalize(img.width * img.height < maxRes);
	};

	img.onerror = function() {
		finalize(false);
	};

	img.load(file.getSource());
});
