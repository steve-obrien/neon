// The image upload component could end up being quite complex.
// With a file browser and media manager.
// A cropper and image manipulator...
// So it makes sense we deffer to javascript for the heavy lifting
Vue.component('firefly-form-file-browser', {
	props: {
		name: {type: String, required: true},
		value: String, // the uuid of the file
		startPath: { type: String, default: '/' },
		readOnly: { type: Boolean, default: false}
	},
	data: function() {
		return {
			selectedId: '',
		}
	},
	template: `
		<div>
			<input @input="$emit('input', $event.target.value)" type="hidden" :name="name" :value="selectedId" :readonly="readOnly">
			<div v-if="!readOnly">
				<a class="btn btn-default" @click.prevent="launchPicker"><i class="fa fa-file-o"></i>
					<span v-if="!selectedId">Pick File...</span>
					<span v-if="selectedId">Change File...</i></span>
				</a>
			</div>
			<firefly-form-file-list style="margin: 0;padding: 0;list-style: none; margin-bottom:5px;"
				:files="files"
				@onClearClick="onClear">
			</firefly-form-file-list>
		</div>
	`,
	mounted: function() {
		this.selectedId = this.value;
		if (this.selectedId) this.$store.dispatch('Firefly/FILE_INFO', {id: this.selectedId});
	},

	methods: {
		launchPicker: function() {
			console.log(this.fileInfo);
			FIREFLY.picker(this.name, (item) => {
				this.selectedId = item.id;
				this.$store.commit('Firefly/FILE_INFO', {id: this.selectedId, info: item.file});
				this.$emit('input', this.selectedId);
			}, {startPath:this.startPath})
		},

		onClear: function(){
			this.selectedId = '';
		}
	},

	computed: {
		files() {
			if (this.selectedId === '' || _.isEmpty(this.fileInfo)){
				return [];
			}
			return [this.fileInfo]
		},
		fileInfo() {
			if (this.$store.state.Firefly.files[this.selectedId])
				return this.$store.state.Firefly.files[this.selectedId];
			return {};
		}
	}
});
