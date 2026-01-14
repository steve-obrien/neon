/**
 * This component displays a list of files - their upload progress and actions to remove from the list
 */
Vue.component('firefly-form-file-list', {
	props: {
		files: {type: Array, default: function() { return []; }},
		urlDownload: {type: String, default: '/firefly/api/file/download'},
		printOnly: { type: Boolean, default: false},
	},
	data() {
		return {
			UPLOADING: 2,
			timeout: {}
		}
	},
	template: `
		<transition tag="ul" name="fade">
			<ul v-if="files.length" style="margin: 0;padding: 0;list-style: none; margin-bottom: 5px;">
				<li v-for="file in files" :key="file.uuid" tabindex="0" class="el-upload-list__item is-success">
					<div v-if='printOnly'>
						<a class="el-upload-list__item-name" :href="fileUrl(file)" :target="file.name">
							<i class="el-icon-document"></i> {{file.name}}
						</a>
					</div>
					<div v-else>    
						<a class="el-upload-list__item-name" :href="fileUrl(file)" :target="file.name">
							<i class="el-icon-document"></i>{{file.name}}
						</a>
						<i class="el-icon-close" style="width:20px;" @click="$emit('onClearClick', file.id)"></i>
						<el-progress v-if="file.status == UPLOADING" type="line" :stroke-width="2" :percentage="file.percent"></el-progress>
					</div>
				</li>
			</ul>
		</transition>
	`,
	methods: {
		afterUploadSuccessEnter: function(fileId) {
			setTimeout(() => {Vue.set(this.timeout, fileId, true); } , 500);
		},
		fileUrl: function(file){
			if (file.uuid)
				return neon.url(this.urlDownload, {id: file.uuid});
			return false;
		}
	}
});