/**
 * ------------------------
 * <firefly-media-browser>
 * ------------------------
 *
 */
Vue.component('firefly-media-browser-toolbar', {
	props: {
		/**
		 * The name of the media picker - all browsers store their state against their picker key
		 * state.pickers[name]
		 * neon.Store.state.Firefly.pickers[name]
		 * @type {String}
		 */
		name: {
			type: String,
			required:true
		}
	},
	template: `
		<div class="workbenchHeader">
			<button class="btn btn-default" @click.prevent="addFolder"><i class="fa fa-folder-o"></i> Add Folder</button>
			<firefly-media-upload-button class="mls" :name="name" :path="path">Upload...</firefly-media-upload-button>
			<button @click="uploadFromUrl" class="btn btn-default">Url</button>
			<button :disabled="noEditorExists" @click="edit" class="btn btn-default mls" ><i class="fa fa-pencil"></i> Edit...</button>
			<button :disabled="!selectedItems.length" @click="deleteSelected"  class="btn btn-danger mls"><i class="fa fa-trash-o"></i> Delete</button>
			<div :disabled="!selectedItems.length" @click="download()" class="btn btn-default mls"><i class="fa fa-cloud-download"></i> Download</div>

			<div class="mls">{{dropText}}</div>
			<div v-show="uploaderStatus" class="progress progressThin" style="width:100px"><div class="progress-bar" :style="{width: uploadPercent+'%'}"></div></div>
			<!-- uploading status -->
			<div v-show="uploaderStatus" class="mls">{{uploaderStatus}}</div>
<!--		<button class="mls btn" @click="unsplash">Unsplash</button>-->
			Deleted ({{deletedCount}})
		</div>
	`,
	methods: {
		addFolder: function() {
			this.$store.dispatch('Firefly/CREATE_DIRECTORY', { name: this.name }).then(dir => {
				this.scrollTo(dir.id);
			});
		},
		loadPath:  function(path) { this.$store.dispatch('Firefly/LOAD_ITEMS', { path: path, name: this.name }); },
		edit:      function() {
			FIREFLY.edit(this.selectedItem, {}, (editedItem) => {
				this.$store.dispatch('Firefly/LOAD_ITEMS', {name: this.name, path: editedItem.path});
				// select the new edited item
				this.$store.commit('Firefly/SELECT_ITEM', {name: this.name, item: editedItem});
			});
		},
		deleteSelected: function() { this.$store.dispatch('Firefly/DELETE_SELECTED', {name: this.name}); },
		download: function() {
			if (_.isDefined(this.selectedItem.id)) {
				window.location = neon.url('firefly/api/file/download?id=' + this.selectedItem.id);
			}
		},
		unsplash: function() {
			FIREFLY.unsplash();
		},
		uploadFromUrl:function() {
			var url = prompt("Please enter the url to the image", "Image Url");
			if (url) {
				$.post( neon.url('firefly/api/media/upload-from-url') , { url: url, path: this.path })
				.done(( data ) => {
					this.$store.commit('Firefly/ADD_ITEM', {item: data, name: this.name});
					// once the new item is added to the dom scoll to it
					this.scrollTo(data.id);
				});
			}
		},
		scrollTo: function(id){
			Vue.nextTick(function() {
				document.querySelector(`[data-id="${id}"]`).scrollIntoView({behavior:'smooth'})
			});
		}
	},
	computed: {
		deletedCount:   function() {
			return _.filter(this.items, item => {
				return (item.deleted == 1);
			}).length;
		},
		uploadPercent:  function() { return this.$store.getters['Firefly/getTotalUploadPercent'](this.name); },
		items:          function() { return this.$store.getters['Firefly/getItems'](this.name); },
		uploaderStatus: function() { return this.$store.getters['Firefly/getUploadingFilesStatus'](this.name); },
		dropText:       function() { return this.$store.getters['Firefly/getDropMoveText'](this.name); },
		path:           function() { return this.$store.getters['Firefly/getPath'](this.name); },
		pathBits:       function() { return this.$store.getters['Firefly/getPathBits'](this.name); },
		selectedItems:  function() { return this.$store.getters['Firefly/getSelectedItems'](this.name); },
		selectedItem:   function() { return this.$store.getters['Firefly/getSelectedItem'](this.name); },
		noEditorExists: function() { return !this.hasEditor; },
		hasEditor:      function() { return this.$store.getters['Firefly/getHasEditor'](this.selectedItem) }
	}
});

