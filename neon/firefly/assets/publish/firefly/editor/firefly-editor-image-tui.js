/**
 * ------------------------
 * <firefly-editor-image>
 * ------------------------
 */
Vue.component('firefly-editor-image', {
	props: {

		/**
		 * The firefly media item object representing the image
		 */
		item: Object,

		/**
		 * Settings for the editor
		 */
		settings: {
			type: Object, default: function () {
				return {

					/**
					 * Whether to launch the editor with a crop selection already made
					 */
					cropAuto: false,

					/**
					 * The fixed crop width used to determine an aspect ratio
					 */
					cropWidth: 0,

					/**
					 * The fixed crop height used to determine an aspect ratio
					 */
					cropHeight: 0,

				}
			}
		},

		/**
		 * toggle the close button - this can be useful when other things are controlling
		 * whether the image editor is displaying or not.
		 */
		closeButton: { Type: Boolean, default: false }
	},
	data: function () {

		let editingImageId = this.item.id;
		let cropData = null;
		let canvasData = null;

		// a transform has been applied to this image so we want to load the origin *source* image
		// and apply the same transformations so they can be tweaked

		if (_.isDefined(this.item.meta) && this.item.meta !== null
			&& _.isDefined(this.item.meta.transform)
			&& _.isDefined(this.item.meta.transform.sourceImage)) {
			// get source image info
			editingImageId = this.item.meta.transform.sourceImage;
			cropData = this.item.meta.transform.crop;
			canvasData = this.item.meta.transform.canvas;
		}

		return {
			editingImageId: editingImageId,
			bounds: {
				width: this.settings.cropWidth,
				height: this.settings.cropHeight
			},
			dragMode: 'none',
			/**
			 * Initial data to pass into the cropper allowed values:
			 * x,y,height,width,scaleX,scaleY,zoom,rotate
			 */
			cropData: cropData,
			/**
			 * Initial canvas information to send to the cropper
			 *
			 */
			canvasData: canvasData,
			cropping: false,
			croppingAjax: false, // whether actively cropping to server - useful to show UI updates
			cropper: null,
			crop: {
				x: 0, y: 0, height: 0, width: 0, zoom: 1, rotate: 0, scaleX: 1, scaleY: 1
			}
		}
	},
	template: `
	<div class="fireflyEditor" style="position:absolute;top:0;bottom:0;left:0;right:0;">
		<div class="fireflyEditor_toolbar">
			<div class="btn-group">
			
			</div>
			<div class="btn-group" v-if="closeButton">
				<button class=" btn btn-default" @click.prevent="$emit('close')" title="Close (Esc)"><span class="fa fa-close"></span></button>
			</div>
		</div>
		<div ref="editorWindow" class="fireflyEditor_body" style="height:100%;">
			<div id="tui-image-editor" style="height: 800px">
				<canvas></canvas>
			</div>
		</div>
	</div>`,
	mounted: function () {
		FIREFLY.editor = this;

		const bounds = this.$refs.editorWindow.getBoundingClientRect()
		var imageEditor = new tui.ImageEditor('#tui-image-editor', {
			usageStatistics: false,
			includeUI: {
				menu: [
					"crop",
					"flip",
					"rotate",
					"draw",
					"shape",
					"icon",
					"text",
					"filter",
				],
				initMenu: 'crop',
				loadImage: {
					path: this.imageUrl, // Replace with your image path
					name: 'SampleImage'
				},
				theme: {
					"common.bisize.width": "0",
					"common.bisize.height": "0",
					// "common.backgroundColor": "#fff",
				  
					// header
					"header.backgroundImage": "none",
					"header.backgroundColor": "transparent",
					"header.border": "0px",
				  
					// load button
					// "loadButton.backgroundColor": "#fff",
					// "loadButton.border": "1px solid #ddd",
					// "loadButton.color": "#222",
					// "loadButton.fontFamily": "'Noto Sans', sans-serif",
					// "loadButton.fontSize": "12px",
				  
					// // download button
					// "downloadButton.backgroundColor": "#fdba3b",
					// "downloadButton.border": "1px solid #fdba3b",
					// "downloadButton.color": "#fff",
					// "downloadButton.fontFamily": "'Noto Sans', sans-serif",
					// "downloadButton.fontSize": "12px",
				
				},
				menuBarPosition: 'bottom'
			},
			cssMaxWidth: bounds.width,
			cssMaxHeight: bounds.height
		});

		imageEditor.on('loadImage', function() {
			imageEditor.setCropzoneRect(
				parseInt(300, 10) / parseInt(400, 10)
			);
		});

		window.onresize = function () {
			imageEditor.ui.resizeEditor();
		}

		window.imageEditor = imageEditor;

	},
	methods: {
		/**
		 * Initialise the cropper
		 */
		cropperStart: function () {
			var vm = this;
			var aspectRatio = NaN;

			if (vm.bounds.width != 0 && vm.bounds.height != 0) {
				aspectRatio = vm.bounds.width / vm.bounds.height;
			}

			var currentAspectRatio = this.$refs.image.width / this.$refs.image.height;

			// if the aspect ratio of the image is suitable then return the same item
			// thus skipping the edit process
			if (currentAspectRatio.toFixed(2) === aspectRatio.toFixed(2)) {
				vm.$emit('on-saved', this.item);
			}

			vm.cropper = new Cropper(this.$refs.image, {
				autoCropArea: 1,
				autoCrop: this.settings.cropAuto,
				background: false,
				zoomOnWheel: false,
				dragMode: vm.dragMode,
				viewMode: 0,
				// preview: '#preview-' + this.item.id, //- id of a preview element
				aspectRatio: aspectRatio,
				zoom: 1,
				//crop: function (e) {
				// vm.crop.current.x = e.detail.x; // vm.crop.current.y = e.detail.y;
				// vm.crop.current.width = e.detail.width; // vm.crop.current.height = e.detail.height;
				//},
				ready: () => {
					if (this.cropData) {
						// crappy conversions
						// make sure this is either stored correctly or something (its stored wrong in the db at the moment)
						this.cropData.x = parseInt(this.cropData.x);
						this.cropData.y = parseInt(this.cropData.y);
						this.cropData.height = parseInt(this.cropData.height);
						this.cropData.width = parseInt(this.cropData.width);
						this.cropData.rotate = parseInt(this.cropData.rotate);
						this.cropData.scaleX = parseInt(this.cropData.scaleX);
						this.cropData.scaleY = parseInt(this.cropData.scaleY);

						this.cropper.setData(this.cropData);
						//.setCanvasData(this.canvasData);
					}
					var canvas = this.cropper.getCanvasData();
					this.crop.zoom = (canvas.width / canvas.naturalWidth);
				},
			});
		},
		zoomReset: function () {
			this.crop.zoom = 1;
			this.cropper.zoomTo(this.crop.zoom);
		},
		zoomIn: function () {
			this.crop.zoom = this.crop.zoom + 0.1;
			this.cropper.zoomTo(this.crop.zoom);
		},
		zoomOut: function () {
			this.crop.zoom = this.crop.zoom - 0.1;
			this.cropper.zoomTo(this.crop.zoom);
		},
		rotateRight: function () {
			if (this.crop.rotate == 360)
				this.crop.rotate = 90;
			this.crop.rotate = this.crop.rotate + 90;
			this.cropper.rotateTo(this.crop.rotate);
		},
		rotateLeft: function () {
			if (this.crop.rotate == 0)
				this.crop.rotate = 360;
			this.crop.rotate = this.crop.rotate - 90;
			this.cropper.rotateTo(this.crop.rotate);
		},
		flipHorizontal: function () {
			this.crop.scaleX = -this.crop.scaleX;
			this.cropper.scaleX(this.crop.scaleX);
		},
		flipVertical: function () {
			this.crop.scaleY = -this.crop.scaleY;
			this.cropper.scaleY(this.crop.scaleY);
		},
		cropToolClick: function () {
			if (!this.cropping) {
				this.cropping = true;
				this.dragMode = 'crop';
				this.cropper.setDragMode(this.dragMode);
			} else {
				this.cropper.clear();
				this.cropping = false;
				this.dragMode = 'move';
				this.cropper.setDragMode(this.dragMode);
			}
		},
		moveToolClick: function () {
			if (this.dragMode == 'crop') {
				this.dragMode = 'move';
				this.cropper.setDragMode(this.dragMode);
			} else if (this.dragMode == 'move') {
				if (this.cropping) {
					this.dragMode = 'crop';
				} else {
					this.dragMode = 'none';
				}
				this.cropper.setDragMode(this.dragMode);
			} else if (this.dragMode == 'none') {
				this.dragMode = 'move';
				this.cropper.setDragMode(this.dragMode);
			}
		},
		save: function () {
			this.onCrop();
		},
		onCrop: function () {
			var transform = {
				sourceImage: this.editingImageId,
				crop: this.cropper.getData(true),
				canvas: this.cropper.getCanvasData(true)
			};
			var vm = this;
			var data = { transform: transform, path: this.item.path };
			this.croppingAjax = true;
			$.ajax(neon.base() + '/firefly/api/media/transform', {
				method: "POST",
				contentType: 'application/json',
				data: JSON.stringify(data),
				success: function (response) {
					vm.$emit('on-saved', response);
					vm.croppingAjax = false;
				},
				error: () => { vm.croppingAjax = false; console.log('Upload error'); },
				dataType: 'json'
			});
		},
	},
	computed: {
		imageUrl: function () {
			return neon.base() + '/firefly/file/img?id=' + this.editingImageId;
		},
		zoomPercent: function () {
			return Math.round(this.crop.zoom * 100);
		}
	}
});