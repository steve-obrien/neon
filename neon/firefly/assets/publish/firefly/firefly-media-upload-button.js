/**
 * ------------------------
 * <firefly-media-browser>
 * ------------------------
 *
 */
Vue.component('firefly-media-upload-button', {
	props: {
		name: {type: String, required:true},
		path: {type: String, default: '/'}
	},
	data: function() {
		var id = 'uploader_' + _.uniqueId();
		return {
			container: id + '_container',
			drop: id + '_drop',
		}
	},
	template: `
		<div :id="drop" class="btn btn-default">
			<div>
				<i class="fa fa-cloud-upload" aria-hidden="true"></i>
				<slot></slot>
			</div>
		</div>
	`,
	mounted() {
		FIREFLY.createPlupload(this.name, {
			drop_element: this.drop,
			browse_button: this.drop,
		});
	}
});

