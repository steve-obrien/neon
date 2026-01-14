

Vue.component('neon-cms-form-fields-imagecompare', {
	extends: Vue.component('neon-core-form-fields-base'),
	props: {
		value: { type: [String, Array, Object] },
	},
	template: /*html*/
		`
		<neon-core-form-field v-bind="fieldProps" class="neonFieldJson"> 
			<input style="display:none;" type="text" :name="inputName" :value="modelValue" /> 
			<div @click="addImage(0)" class="btn btn-default" style="margin-bottom:8px;"><i class="fa fa-plus"></i> Image #1</div> 
			<div @click="addImage(1)" class="btn btn-default" style="margin-bottom:8px;"><i class="fa fa-plus"></i> Image #2</div> 
			<div ref="sortable" style="display:flex;"> 
				<div :id="image" v-for="(image, key) in images()" style="width:100px; height:100px; border: 1px solid #ccc; position:relative;border-radius:4px;margin-right:8px;"> 
					<img :src="url(image)" style="max-width:100px;max-height:100px;margin:auto;height:auto;position:absolute;top:0;left:0;right:0;bottom:0;padding:4px;"/> 
					<div @click="deleteImage(key)" class="btn btn-xs btn-danger" style="position:absolute;top:-5px;right:-5px;border-radius:20px;"><i class="fa fa-times"></i></div> 
				</div> 
			</div> 

			<div ref="preview" style="position:relative">
				<div style="z-index:1;position:absolute;left:0;top:0;bottom:0;right:50%;overflow:hidden">
					<div style="z-index:10;cursor:col-resize;top:0;bottom:0;position:absolute;right:0;padding:0 0;">
						<div style="width:5px;background-color:#aaa;height:100%;"></div>
					</div>
					<img draggable="false" style="width:200%; pointer-events:none;" :src="url(images()[0])" />
				</div>
				<img draggable="false" style="width:100%;z-index:0;pointer-events:none;" :src="url(images()[1])" />
			</div>

		</neon-core-form-field> 
	`,
	created: function () {
		// to cope with json and php mapping - unfortunately serialising from a php array of:
		// ['one', 'two'] actually generates an object in javascript like {'0': 'one', '2': 'two'}
		if (_.isObject(this.value))
			this.modelValue = JSON.stringify(_.values(this.value));
		// we will assume this is valid json that needs parsing
		if (_.isArray(this.value))
			this.modelValue = JSON.stringify(this.value);
	},
	mounted() {
		this.updateSlider();
	},
	methods: {
		updateSlider() {
			this.$nextTick(() => {
				function imageCompare (element) {
					var leftWindow = element.children[0]
					var handle = leftWindow.children[0]
					var img = leftWindow.children[1]
					if (!img || !handle || !leftWindow) console.warning('unable to find child elements');
					var handleRect = handle.getBoundingClientRect();
					const resize = (clientX, e) => {
						var elementBounds = element.getBoundingClientRect();
						var relativeOffsetLeft = (clientX - elementBounds.left + (handleRect.width/2))
						var leftPercent = Math.max(0.5, Math.min(100, relativeOffsetLeft / elementBounds.width * 100))
						leftWindow.style.right = (100-leftPercent)+'%'
						img.style.width = ((100/leftPercent)*100) + '%'
						e.stopPropagation(); e.preventDefault();
					}
					element.addEventListener("touchmove", e => { resize(e.touches[0].clientX, e) });
					element.addEventListener("touchstart", e => { resize(e.touches[0].clientX, e) })
					handle.addEventListener('mousedown', () => {
						const mousemove = e => { resize(e.clientX, e) };
						const mouseup = () => {
							document.removeEventListener('mousemove', mousemove);
							document.removeEventListener('mouseup', mouseup);
						};
						document.addEventListener('mousemove', mousemove);
						document.addEventListener('mouseup', mouseup);
					});
				};
				imageCompare(this.$refs.preview);
			})
		},
		images: function () {
			if (_.isEmpty(this.modelValue)) return [];
			return JSON.parse(this.modelValue);
		},
		addImage: function (position) {
			var vm = this;
			FIREFLY.pickerMultiple(this.path, function (items) { vm.addItem(position, items[0]) })
		},
		addItem: function (position, item) {
			var images = this.images();
			if (!_.isArray(images)) { images = []; }
			images[parseInt(position)] = item.id;
			this.modelValue = JSON.stringify(images);
			this.updateSlider();
		},
		deleteImage: function (key) {
			var images = this.images();
			images.splice(key, 1)
			this.modelValue = JSON.stringify(images);
		},
		url(image) { return FIREFLY.getImageUrl(image) },
	}

});