// The image upload component could end up being quite complex.
// With a file browser and media manager.
// A cropper and image manipulator...
// So it makes sense we deffer to javascript for the heavy lifting
Vue.component('firefly-form-input-file-upload', {
	props: {
		multiple: {type: Boolean, default:false},
		label: String,
		hint: String,
		name: {type: String, required: true},
		value: {type: [Array, String, Object], default: ''}, // the uuid of the file
		readOnly: { type: Boolean, default: false},
		id: { type: String, required: true},
		printOnly: { type: Boolean, default: false},
		urlUpload: { type: String, default: '/firefly/api/file/upload'},
		urlDownload: {type: String, default: '/firefly/api/file/download'},
		urlMeta: {type: String, default: '/firefly/api/file/meta'}
	},
	data() {
		return {
			// plupload settings
			container: this.id+'_container',
			drop: this.id+'_drop',
			browse: this.id+'_browse',
			// store uploaded file info
			files: [],
			buttonUploadMessage: (this.multiple ? 'Upload Files ...' : 'Upload File ...'),
			buttonChangeMessage: (this.multiple ? 'Add Files ...' : 'Change File ...'),
			// plupload file state constant
			UPLOADING: 2,
			dragOver: false,
		}
	},
	template: `
		<div>
			<input v-if="multiple" v-for="file in value" :value="file" type="hidden" :name="name+'[]'" :id="id" :readonly="readOnly">
			<input v-if="!multiple" :value="value" type="hidden" :name="name" :id="id" :readonly="readOnly">
			<div :id="container">
				<div :id="drop" :class="{'fireflyDragOver':dragOver}" @dragover.prevent="onDragOver" @dragleave.prevent="dragOver = false">
					<a v-if='!(printOnly || readOnly)' :id="browse" href="#" class="btn btn-default">
						<i class="fa fa-upload"></i> {{files.length ? buttonChangeMessage : buttonUploadMessage }}
					</a>
					<!-- upload list component -->
					<firefly-form-file-list style="margin: 0;padding: 0;list-style: none; margin-bottom:5px;"
						:url-download="urlDownload"
						:files="files"
						:print-only="printOnly"
						@onClearClick="onClearClick">
					</firefly-form-file-list>
				</div>
			</div>
		</div>
	`,

	mounted: function() {
		var vm = this;
		this.uploader = FIREFLY.createPlupload(this.name, {
			url: neon.url(this.urlUpload),
			container: this.container,
			drop_element: this.drop,
			browse_button: this.browse,
			multi_selection: this.multiple,
			onFilesAdded: function (uploader, files) {
				vm.dragOver = false;
				if (vm.multiple)
					vm.files = vm.files.concat(files);
				else
					vm.files = files;
				uploader.start();
			},
			onFileUploaded: function (uploader, file, result) {
				var response = JSON.parse(result.response);
				file.uuid = response.result.uuid;
				vm.emitFilesChanged();
			},
			onUploadProgress: function(up, file) {},
			onStateChanged: function(uploader) {
				vm.$emit('uploading', (uploader.state === vm.UPLOADING));
			},
		});

		// load the file if a file uuid has been set
		if (this.value && !_.isEmpty(this.value)) this.loadFile(this.value);
	},
	methods: {
		emitFilesChanged: function() {
			// when files have changed reconfigure the files list
			var val = null;
			if (this.multiple) {
				val = [];
				_.each(this.files, function(file) {
					val.push(file.uuid);
				});
			} else {
				val = this.files[0].uuid;
			}
			console.log(val, 'val')
			this.$emit('input', val);
		},
		onClearClick: function(id) {
			this.uploader.removeFile(id);
			index = _.findIndex(this.files, function(vmFile) {return vmFile.id == id;});
			if (index !== -1)
				this.files.splice(index, 1);
			this.emitFilesChanged();
		},
		fileUrl: function(file) {
			if (file.uuid)
				return neon.url(this.urlDownload, {id: file.uuid});
			return false;
		},
		loadFile(fileUuid) {
			var vm = this;
			this.$store.dispatch('Firefly/FILE_INFO', {id: fileUuid, url: this.urlMeta}).then(function(response) {
				vm.files = [];
				if (Array.isArray(response)) {
					vm.files = _.clone(response);
				} else {
					vm.files.push(response);
				}
			});
		},
		onDragOver() {
			if (!this.disabled) {
				this.dragOver = true;
			}
		},
	}
});