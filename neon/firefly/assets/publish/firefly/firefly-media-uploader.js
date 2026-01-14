Vue.component('firefly-media-uploader', {
	props: {
		name: {type: String, required: true},
		path: {type: String, default: '/'},
		hide: false
	},
	data() {
		var id = _.uniqueId('uploader_');
		return {
			container: id+'_container',
			drop: id+'_drop',
			browse: id+'_browse',
			dragOver:false
		}
	},
	template: `
		<div class="fireflyMediaUploder">
			<div :id="container">
				<div :id="drop" v-show="hide">
					<div class="text-center" ><br/>
						<h3>Drop files anywhere to upload</h3>
						<p>or</p>
						<a :id="browse" style="font-size:20px;" href="#" class="btn btn-lg btn-primary native-file-picker">
							<i class="fa fa-upload"></i>
							Select files...
						</a>
					</div>
				</div>
			</div>

 			<transition name="fade">
				<div id="bigDropUploader" class="bigDropUploader" v-show="dragOver" style="opacticy:0.5;background:rgba(0,0,0,0.5);top:0;bottom:0;left:0;right:0;position:fixed;z-index:1000">
					<div class="bigDropUploader_content" style="">	
						<h1>Drop files here to upload</h1>
					</div>
				</div>
			</transition>

		</div>
	`,
	methods: {
		attachDragInOutEvents: function () {
			let vm = this;
			this.timer = {};
			//do not care much for this code...
			window.addEventListener("dragenter", this.dragenter, false);
			window.addEventListener('dragover', this.dragover, true);
			window.addEventListener("dragleave", this.dragleave, false);
			this.$once('hook:beforeDestroy', function () {
				window.removeEventListener("dragenter", this.dragenter, false);
				window.removeEventListener('dragover', this.dragover, true);
				window.removeEventListener("dragleave", this.dragleave, false);
			});
		},

		dragenter: function(e) {
			if (this.timer) clearTimeout(this.timer);
			this.dragOver = true;
		},

		dragover: function(e) {
			if (this.timer) clearTimeout(this.timer);
			var vm = this;
			this.timer = setTimeout(function () { vm.dragOver = false; }, 200);
			this.dragOver = true;
		},

		dragleave: function(e) {
			if (this.timer) clearTimeout(this.timer);
			var vm = this;
			this.timer = setTimeout(function () { vm.dragOver = false; }, 200);
		}
	},
	mounted() {
		this.uploader = FIREFLY.createPlupload(this.name, {
			container: this.container,
			drop_element: this.drop,
			browse_button: this.browse,
		});
		this.$once('hook:beforeDestroy', function () {
			this.uploader.destroy();
		});
		// Now that we've initialized the pluploader
		// we can wire up the larger full screen drop zone

		// -- Configure DROPZONE. -- //
		// Wiki: https://github.com/moxiecode/moxie/wiki/FileDrop
		var dropzone = new moxie.file.FileDrop({
			drop_zone: $('#bigDropUploader')[0]
		});
		// When the event is fired, the context (ie, this)
		// is the actual dropzone. As such, you can access
		// the files using any of the following:
		// --
		// * this.files
		// * dropzone.files
		// * event.target.files
		dropzone.ondrop = (event) => {
			// The addFile() method can accept an individual
			// file or an array of file sources.
			this.dragOver = false;
			this.uploader.addFile( dropzone.files );
		};
		dropzone.init();

		this.attachDragInOutEvents();
	},
});
