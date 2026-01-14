/**
 * Created by newicon on 09/05/2017.
 */
/**
 * ------------------------
 * <firefly-item>
 * ------------------------
 *
 * Firefly item represents an individual item stored - this could be either a directory or a file
 */
Vue.component('firefly-item', {
	props: {
		name: {type: String}, // this picker name
		item: {type: Object},
		selected: {type: Boolean}
	},
	template: `
		<div class="fireflyItem" :class="{'is-selected':selected, dragOver:dragOver}" @click="$emit('click', $event)" @dblclick="$emit('dblclick')">
			<div :class="itemClassName">
	
				<firefly-preview :item="item" style="height:150px; display: flex; align-items: center;justify-content: center;"></firefly-preview>

				<firefly-item-name :can-edit="item.type != 'upload'" :item="item" :selected="selected"></firefly-item-name>
			</div>
		</div>
	`,
	computed: {
		dragOver: function() { return this.$store.getters['Firefly/getIsItemBeingDroppedOver'](this.name, this.item.id); },
		selectedItems: function() { return this.$store.getters['Firefly/getSelectedItems'](this.name); },
		urlBase: function() { return neon.base(); },
		itemClassName: function () {
			return 'fireflyThumb ' + 'fireflyThumb' + _.capitalize(this.item.type);
		},
	},
	mounted: function() {
		var vm = this;
		if (this.item.type === 'dir') {
			$(vm.$el).droppable({
				accept: ".fireflyItem",
				over: function(event, ui) { vm.$store.commit('Firefly/DROP_OVER', {id: vm.item.id, name: vm.name}); },
				out: function(event, ui) { vm.$store.commit('Firefly/DROP_OUT', {name: vm.name}); },
				drop: function(event, ui) {
					vm.$store.dispatch('Firefly/ITEMS_DROPPED', {into: vm.item.id, items: vm.selectedItems, name: vm.name});
					vm.$store.commit('Firefly/DROP_OUT', {name: vm.name});
				}
			});
			$(vm.$el).draggable({
				helper: function() {
					console.log(vm.selectedItems);
					return $("<div><span class=\"numberDragging label label-danger\"></span></div>")
						.append(
							//$(draggableSelector).clone().removeClass('is-selected')
							$(vm.$el).clone().removeClass('is-selected')
						)
				},
				opacity:0.7,
				revert: false,
				start: function(event, ui) {
					// register this item as being dragged
					vm.$emit('dragStart');
					var numberDragging = vm.selectedItems.length;
					ui.helper.find('.numberDragging').text((numberDragging > 1) ? numberDragging : '');
				}
			})
		}
		if (this.item.type === 'file') {
			$(vm.$el).draggable({
				helper: function() {
					var draggableSelector = [];
					_.each(vm.selectedItems, (item) => {
						if (item.id===vm.item.id) return;
						draggableSelector.push('[data-id="'+item.id+'"]');
					});
					console.log(draggableSelector);
					var clones = $(draggableSelector.join(',')).clone();
					_.each(clones, (clone, i) => { $(clone).removeClass('is-selected').css({position:'absolute', transform:'rotate('+i*5+'deg)'}); })
					// $(vm.$el).draggable( "option", "cursorAt", { left: 100 } );
					return $("<div style=''><span class=\"numberDragging label label-danger\"></span></div>")
						.append([clones, $(vm.$el).clone().removeClass('is-selected')]
							//$(vm.$el).clone().removeClass('is-selected')
						).css({top:'-100px;',left:'-200px'});
				},
				opacity:0.7,
				revert: false,
				start: function(event, ui) {
					// register this item as being dragged
					vm.$emit('dragStart');
					var numberDragging = vm.selectedItems.length;
					ui.helper.find('.numberDragging').text((numberDragging > 1) ? numberDragging : '');
				}
			})
		}
	}
});


Vue.component('firefly-preview', {
	props: {
		item: {type:Object}
	},
	template: `
		<div style="position:relative;">
			<!-- Directory -->
			<div v-if="item.type == 'dir'" style="position:absolute; inset:0;">
				<div class="fireflyThumb_folder fireflyThumb_preview"></div>
			</div>
			
			<!-- File -->
			<div v-if="item.type == 'file'" style="">
				<div v-if="isImage" class="fireflyThumb_preview fireflyThumb_image" >
					<firefly-img ondragstart="return false;" :item="item" ></firefly-img>
				</div>
				<div v-else-if="isVideo" class="fireflyThumb_preview fireflyThumb_image" >
					<video controls style="width:100%; max-height:150px;" ondragstart="return false;" :src="urlBase + '/firefly/file/get?id=' + item.id + '&w=250&h=250'" ></video>
				</div>
				<div v-else-if="fileType === 'pdf'" class="fireflyThumb_preview fireflyThumb_file">
					<object :data="urlBase + '/firefly/file/get?id=' + item.id" type="application/pdf" width="100%">
						<p>Unable to display PDF file.</p>
					</object>
				</div>
				<div v-else class="fireflyThumb_preview fireflyThumb_file" style="inset: 0;position: absolute;">
					<div class="fireflyThumb_previewText">{{fileType}}</div>
				</div>
			</div>

			<!-- Uploading file -->
			<div v-if="item.type == 'upload'" :style="uploadStyles">
				<div class="fireflyThumb_preview fireflyThumb_progress">
					<div class="fireflyThumb_previewText">{{item.upload_percent}}%</div>
					<div class="progress progressThin"><div class="progress-bar" :style="{width: item.upload_percent+'%'}"></div></div>
				</div>
			</div>
		</div>
	`,
	computed: {
		urlBase: function() { return neon.base(); },
		uploadStyles: function() {
			var opacity = this.item.upload_percent / 100;
			if (opacity < 0.4) { opacity = 0.4; }
			return {opacity : opacity}
		},
		isVideo: function() {
			if (this.item.file) {
				var bits = this.item.file.mime_type.split('/');
				return bits[0] === 'video'
			}
			return false;
		},
		fileType: function() {
			if (this.item.file) {
				var bits = this.item.file.mime_type.split('/');
				return bits[1];
			}
			return '';
		},
		isImage: function() {
			if (!this.item.file) return false;
			var mime = this.item.file.mime_type || '';
			return mime.substring(0, 5) === 'image'
			return (mime === 'image/png' || mime === 'image/jpeg' || mime === 'image/gif' || mime === 'image/svg+xml' || mime === 'image/svg');
		},
	}

})