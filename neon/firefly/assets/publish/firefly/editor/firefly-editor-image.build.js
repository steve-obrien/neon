"use strict";

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
      type: Object,
      default: function _default() {
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
          cropHeight: 0
        };
      }
    },

    /**
     * toggle the close button - this can be useful when other things are controlling
     * whether the image editor is displaying or not.
     */
    closeButton: {
      Type: Boolean,
      default: false
    }
  },
  data: function data() {
    var editingImageId = this.item.id;
    var cropData = null;
    var canvasData = null; // a transform has been applied to this image so we want to load the origin *source* image
    // and apply the same transformations so they can be tweaked

    if (_.isDefined(this.item.meta) && this.item.meta !== null && _.isDefined(this.item.meta.transform) && _.isDefined(this.item.meta.transform.sourceImage)) {
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
      croppingAjax: false,
      // whether actively cropping to server - useful to show UI updates
      cropper: null,
      crop: {
        x: 0,
        y: 0,
        height: 0,
        width: 0,
        zoom: 1,
        rotate: 0,
        scaleX: 1,
        scaleY: 1
      }
    };
  },
  template: "\n\t<div class=\"fireflyEditor\" style=\"position:absolute;top:0;bottom:0;left:0;right:0;\">\n\t\t<div class=\"fireflyEditor_toolbar\">\n\t\t\t<div class=\"btn-group\">\n\t\t\t\t<button class=\" btn btn-default\" @click.prevent=\"zoomOut\" title=\"Zoom Out (-)\"><span class=\"fa fa-minus\"></span></button> \n\t\t\t\t<button class=\" btn btn-default\" @click.prevent=\"zoomReset\" title=\"Zoom Out (-)\"><span class=\"fa fa-search\"></span> {{zoomPercent}}%</button>\n\t\t\t\t<button class=\" btn btn-default\" @click.prevent=\"zoomIn\" title=\"Zoom In (+)\"><span class=\"fa fa-plus\"></span></button>\n\t\t\t</div>\n\t\t\t<div class=\"btn-group\">\n\t\t\t\t<button class=\" btn btn-default\" @click.prevent=\"rotateLeft\" title=\"Rotate Left (L)\"><span class=\"fa fa-rotate-left\"></span></button>\n\t\t\t\t<button class=\" btn btn-default\" @click.prevent=\"rotateRight\" title=\"Rotate Right (R)\"><span class=\"fa fa-rotate-right\"></span></button>\n\t\t\t</div>\n\t\t\t<div class=\"btn-group\">\n\t\t\t\t<button class=\" btn btn-default\" @click.prevent=\"flipHorizontal\" title=\"Flip Horizontal (H)\"><span class=\"fa fa-arrows-h\"></span></button>\n\t\t\t\t<button class=\" btn btn-default\" @click.prevent=\"flipVertical\" title=\"Flip Vertical (V)\"><span class=\"fa fa-arrows-v\"></span></button>\n\t\t\t</div>\n\t\t\t<div class=\"btn-group\">\n\t\t\t\t<button class=\" btn btn-default\" @click.prevent=\"cropToolClick\" :class=\"{ 'active': cropping }\" title=\"Crop Tool\">\n\t\t\t\t\t<span class=\"fa fa-crop\"></span>\n\t\t\t\t</button>\n\t\t\t\t<button class=\" btn btn-default\" @click.prevent=\"moveToolClick\" :class=\"{ 'active': dragMode=='move' }\" title=\"Move tool\">\n\t\t\t\t\t<span ><span class=\"fa fa-arrows\"></span></span>\n\t\t\t\t</button>\n\t\t\t</div>\n\t\t\t<div class=\"btn-group\" v-if=\"closeButton\">\n\t\t\t\t<button class=\" btn btn-default\" @click.prevent=\"$emit('close')\" title=\"Close (Esc)\"><span class=\"fa fa-close\"></span></button>\n\t\t\t</div>\n\t\t\t{{editingImageId}}\n\t\t</div>\n\t\t<div class=\"fireflyEditor_body\">\n\t\t\t<img ref=\"image\" @load=\"cropperStart\" :src=\"imageUrl\" style=\"max-width: 100%;\" />\n\t\t</div>\n\t</div>",
  mounted: function mounted() {
    FIREFLY.editor = this;
  },
  methods: {
    /**
     * Initialise the cropper
     */
    cropperStart: function cropperStart() {
      var _this = this;

      var vm = this;
      var aspectRatio = NaN;

      if (vm.bounds.width != 0 && vm.bounds.height != 0) {
        aspectRatio = vm.bounds.width / vm.bounds.height;
      }

      var currentAspectRatio = this.$refs.image.width / this.$refs.image.height; // if the aspect ratio of the image is suitable then return the same item
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
        ready: function ready() {
          if (_this.cropData) {
            // crappy conversions
            // make sure this is either stored correctly or something (its stored wrong in the db at the moment)
            _this.cropData.x = parseInt(_this.cropData.x);
            _this.cropData.y = parseInt(_this.cropData.y);
            _this.cropData.height = parseInt(_this.cropData.height);
            _this.cropData.width = parseInt(_this.cropData.width);
            _this.cropData.rotate = parseInt(_this.cropData.rotate);
            _this.cropData.scaleX = parseInt(_this.cropData.scaleX);
            _this.cropData.scaleY = parseInt(_this.cropData.scaleY);

            _this.cropper.setData(_this.cropData); //.setCanvasData(this.canvasData);

          }

          var canvas = _this.cropper.getCanvasData();

          _this.crop.zoom = canvas.width / canvas.naturalWidth;
        }
      });
    },
    zoomReset: function zoomReset() {
      this.crop.zoom = 1;
      this.cropper.zoomTo(this.crop.zoom);
    },
    zoomIn: function zoomIn() {
      this.crop.zoom = this.crop.zoom + 0.1;
      this.cropper.zoomTo(this.crop.zoom);
    },
    zoomOut: function zoomOut() {
      this.crop.zoom = this.crop.zoom - 0.1;
      this.cropper.zoomTo(this.crop.zoom);
    },
    rotateRight: function rotateRight() {
      if (this.crop.rotate == 360) this.crop.rotate = 90;
      this.crop.rotate = this.crop.rotate + 90;
      this.cropper.rotateTo(this.crop.rotate);
    },
    rotateLeft: function rotateLeft() {
      if (this.crop.rotate == 0) this.crop.rotate = 360;
      this.crop.rotate = this.crop.rotate - 90;
      this.cropper.rotateTo(this.crop.rotate);
    },
    flipHorizontal: function flipHorizontal() {
      this.crop.scaleX = -this.crop.scaleX;
      this.cropper.scaleX(this.crop.scaleX);
    },
    flipVertical: function flipVertical() {
      this.crop.scaleY = -this.crop.scaleY;
      this.cropper.scaleY(this.crop.scaleY);
    },
    cropToolClick: function cropToolClick() {
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
    moveToolClick: function moveToolClick() {
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
    save: function save() {
      this.onCrop();
    },
    onCrop: function onCrop() {
      var transform = {
        sourceImage: this.editingImageId,
        crop: this.cropper.getData(true),
        canvas: this.cropper.getCanvasData(true)
      };
      var vm = this;
      var data = {
        transform: transform,
        path: this.item.path
      };
      this.croppingAjax = true;
      $.ajax(neon.base() + '/firefly/api/media/transform', {
        method: "POST",
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function success(response) {
          vm.$emit('on-saved', response);
          vm.croppingAjax = false;
        },
        error: function error() {
          vm.croppingAjax = false;
          console.log('Upload error');
        },
        dataType: 'json'
      });
    }
  },
  computed: {
    imageUrl: function imageUrl() {
      return neon.base() + '/firefly/file/img?id=' + this.editingImageId;
    },
    zoomPercent: function zoomPercent() {
      return Math.round(this.crop.zoom * 100);
    }
  }
});