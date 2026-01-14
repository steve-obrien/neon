// The image upload component could end up being quite complex.
// With a file browser and media manager.
// A cropper and image manipulator...
// So it makes sense we deffer to javascript for the heavy lifting
/**
 *
 * @events:
 * **on-error** event to listen to specific plupload errors:
 * @example:
 * `<firefly-form-image @on-error="onError($event)"></firefly-form-image>`
 * The event object will have {name: name, error: err, uploader: up} where:
 * - {String} name: Name of the picker with the plupload that fired the event
 * - {Object} error: An object containing keys:
 *     - {Number} code: The plupload error code
 *     - {String} message: Description of the error
 * - {Object} uploader: plupload.Uploader object
 *
 * **click** - fired when the picker modal is launched
 */
Vue.component('firefly-form-image', {
	props: {
		name: { type: String, required: true },
		value: String, // the uuid of the file
		crop: false,
		cropWidth: '',
		cropHeight: '',
		startPath: { type: String, default: '/' },
		readOnly: { type: Boolean, default: false }
	},
	data: function() {
		return {
			editing: false,
			selectedId: '',
			editItem: null,
		}
	},
	template: `
		<div>
			<input @input="$emit('input', $event.target.value)" type="hidden" :name="name" :value="selectedId" :readonly="readOnly">
			<span v-if="crop" class="hint-block hint-bottom">The image aspect ratio: {{cropWidth}} x {{cropHeight}}</span>
			<div v-if="!readOnly">
				<a class="btn btn-default" @click.prevent="launchPicker()"><i class="fa fa-image"></i>
					<span v-if="!selectedId">Pick Image...</span>
					<span v-if="selectedId">Change Image...</i></span>
				</a>
				<button title="Remove" v-if="selectedId" class="btn btn-default" @click.prevent="removeImage"><i class="fa fa-times"></i></button>
			</div>
			<div class="mtm" :id="previewId" v-if="selectedId" >
				<img :src="url" :style="imagePreviewStyle" />
			</div>
		</div>
	`,
	methods: {
		launchPicker: function() {
			this.$emit('click');
			this.fileInfo().then(response => {
				var options = {};
				if (_.has(response.data, 'path')) {
					options.startPath = response.data.path;
					options.select = response.data.id;
				}
				FIREFLY.picker(this.name, this.onSelected, options);
				this.$store.commit('Firefly/SELECT_ITEM', {name: this.name, item:response.data});
			});
			FIREFLY.events.$on('uploadError', this.onError)
			this.$once('hook:beforeDestroy', function() {
				FIREFLY.events.$off('uploadError', this.onError);
			});
		},
		onError: function(err) {
			// only bubble events related to this picker
			if (err.name === this.name) {
				this.$emit('on-error', err);
			}
		},
		/**
		 * Gets media item by id
		 * @returns {Promise}
		 */
		fileInfo() {
			return this.$store.dispatch('Firefly/FILE_INFO', {
				id: this.selectedId,
				url: '/firefly/api/media/meta-by-id'
			});
		},
		onSelected: function(item) {
			var vm = this;
			if (item.file.mime_type.substr(0,5) !== 'image') {
				alert('You must select an image');
				return;
			}
			
			vm.selectedId = item.id;
			vm.$emit('input', vm.selectedId);
			
			// must also be a image!
			if (vm.crop) {
				// you can't crop svgs - so return
				if (item.file.mime_type === 'image/svg') 
					return;
				var settings = {cropAuto: true, cropWidth: vm.cropWidth, cropHeight: vm.cropHeight};
				FIREFLY.edit(item, settings, function(newItem) {
					vm.selectedId = newItem.id;
					// make compatible with v-model
					vm.$emit('input', vm.selectedId);
				});
			}
		},
		removeImage: function (e) {
			this.selectedId = '';
		},
	},
	beforeMount: function() {
		this.selectedId = this.value;
	},
	computed: {
		previewId () { return this.name + '_imagePreview';},
		url () { return FIREFLY.getImageUrl(this.selectedId) },
		imagePreviewStyle: function() {
			return {
				maxWidth:'250px',
				maxHeight:'250px'
			};
		}
	}
});
