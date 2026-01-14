<template>
	<div>
		<div class="stickyPanel"><button class="btn btn-primary btn-sm" @click="save">Save</button></div>
		<InspectorPanel title="General">
			<div class="form-group">
				<label class="control-label" style="display:block">Page Name <code>{{nice_id}}</code></label>
				<input class="form-control" name="page[name]" v-model="name" />
			</div>
			<div class="form-group">
				<label class="control-label" style="display:block">Status</label>
				<select class="form-control" name="page[status]" v-model="status">
					<option value="PUBLISHED">Published</option>
					<option value="DRAFT">Draft</option>
				</select>
			</div>
			<div class="form-group" v-if="url !== '/' ">
				<label class="control-label" style="display:block">Slug</label>
				<Slug :disabled="pageIsCollection" class="form-control" v-model="url" name="url" ></Slug>
				<div>
					<svg style="width:16px;height:16px;vertical-align: middle;display:inline-block;" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
					<span style="color:#666">{{base}}</span>
					<strong>{{url}}</strong>
				</div>
				<div v-if="slugChanged">
					<label style="display:flex">
						<input type="checkbox" v-model="redirect">
						<span style="margin-left:4px;">Permanently redirect the old url to this new one</span>
					</label>
					<div v-if="! redirect" class="alert alert-warning">
						<svg style="width:16px;height:16px;vertical-align: middle;display:inline-block;color:#d27400;" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
						Changing the slug without a redirect will break any external links to this page.
						If you don't yet have any existing external websites linking to this page using the url <strong>{{base + original[pageUuid].url}}</strong> then it's probably safe to change
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label" style="display:block">Page Template</label>
				<input class="form-control" name="page[template]" v-model="template" />
			</div>
			<div v-if="pageIsCollection">
				<div v-if="pageIsCollection" class="alert alert-info">
					This page is a collection template and is responsible for all items of {{page().collection_class}}.  Changes here will affect all pages that use this template.
				</div>
			</div>

		</InspectorPanel>
		<InspectorPanel title="SEO Settings">
			<div class="form-group">
				<label class="control-label" :disabled="og_title_use_seo"  style="display:block">Exclude this page from search engines</label>
				<input class="form-control" :disabled="og_title_use_seo"  name="page[og_title]" v-model="og_title" />
				<label><input type="checkbox" v-model="og_title_use_seo" :true-value="true" :false-value="false"> Same as SEO Title</label>
			</div>
			<p>Specify the page's title and description.</p>
			<div class="form-group">
				<label class="control-label" style="display:block">Title Tag</label>
				<small>Title the page - the optimal title length is approximately 55 characters</small>
				<a v-if="pageIsCollection" @click.prevent="" href>+ Field</a>
				<select v-if="pageIsCollection" class="form-control" style="font-size:10px;" @change="addFieldToSeoTitle($event)">
					<option value="">+ Field</option>
					<option v-for="member in members" :value="member.member_ref">{{member.member_ref}} - {{member.label}}</option>
				</select>
				<input class="form-control" name="page[title]" v-model="title" />
			</div>
			<div class="form-group">
				<label class="control-label" style="display:block">Meta Description</label>
				<small>Describe the page - the optimal description length is 155 to 300 characters</small>
				<a v-if="pageIsCollection" @click.prevent="" href>+ Field</a>
				<textarea class="form-control" name="page[description]" v-model="description"></textarea>
			</div>
			<div class="form-group">
				<label class="control-label" style="display:block" >Preview: See how this will typically look in search engines below.</label>
				<div v-if="pageIsCollection" class="dispay:inline-flex"><a href @click.prevent="prev"> << </a> <a href @click.prevent="next"> >> </a></div>
				<div class="seoPreview">
					<div class="seoPreview_title">{{ replaceFields(title) | truncate(55, '...') }}</div>
					<div class="seoPreview_url">{{ fullUrl }}</div>
					<div class="seoPreview_desc">{{ replaceFields(description) | truncate(160, '...') }}</div>
				</div>
				<div>This uses the typical character limits for Google search result pages on desktop. Search engines do experiment with their character limits, and may decide to show different content.</div>
			</div>
		</InspectorPanel>
		<InspectorPanel title="Open Graph Settings">
			<p>The info that shows up when sharing content on Facebook, Twitter, LinkedIn, Pinterest, and Google+.</p>
			<div class="form-group">
				<label class="control-label" :disabled="og_title_use_seo"  style="display:block">Open Graph Title</label>
				<input class="form-control" :disabled="og_title_use_seo"  name="page[og_title]" v-model="og_title" />
				<label><input type="checkbox" v-model="og_title_use_seo" :true-value="true" :false-value="false"> Same as SEO Title</label>
			</div>
			<div class="form-group">
				<label class="control-label" :disabled="og_description_use_seo" style="display:block">Open Graph Description</label>
				<textarea class="form-control" :disabled="og_description_use_seo" name="page[og_description]" v-model="og_description"></textarea>
				<label><input type="checkbox" v-model="og_description_use_seo"> Same as SEO Description</label>
			</div>
			<div class="form-group">
				<label class="control-label" style="display:block">Open Graph Image URL</label>
				<div v-if="! pageIsCollection">
					<small style="display:block;">Make sure your images are at least 1200px by 630px and have a 1.91:1 aspect ratio.</small>
					<button  class="btn btn-default" @click="setOgImage">Select Image</button>
				</div>
				<div v-else>
					Select an image field from {{classData.definition.label}} table
					<select class="form-control" v-model="og_image">
						<option disabled value="">Select an image field</option>
						<option v-for="imageField in classImageFields" :value="'{{'+imageField.member_ref+'}}'" >{{imageField.label}}</option>
					</select>
				</div>
			</div>
			<div v-if="pageIsCollection" class="dispay:inline-flex"><a href @click.prevent="prev"> << </a> <a href @click.prevent="next"> >> </a></div>
			<div class="seoPreview">
				<img :src="ogImageUrl" style="width:100%"/>
				<div class="ogPreview_title" style="margin-top:5px;line-height:18px;overflow-wrap: break-word;">{{ replaceFields(og_title) | truncate(90, '...')}}</div>
				<div class="ogPreview_desc" style="margin-top:4px;font-size:12px;line-height:14px;overflow-wrap: break-word;">{{ replaceFields(og_description) | truncate(300, '...') }}</div>
				<div class="ogPreview_url" style="margin-top:4px;color:#8A8A8A;font-size:12px;line-height:12px;overflow-wrap: break-word;">{{ og_url }}</div>
			</div>
			<div>Open graph preview - note that if no image is defined then one will most likely be taken from the content on the page</div>
		</InspectorPanel>
		<InspectorPanel title="Custom Code">
			<p>The code included here will only apply to this page, and will be appear after any site-wide custom code defined in settings.</p>
			<div class="form-group">
				<label class="control-label">Inside the &lt;head&gt; tag</label>
				<neon-input-ide v-model="code_head" lang="html"></neon-input-ide>
				<div class="alert alert-warning" style="padding:4px;margin:4px 0 4px 0;line-height:normal;">
					<small>Custom code is not validated. Incorrect code could break the website</small><br>
					<small>Make sure the code is not a security risk - code here could expose sensitive information</small>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label">Before the &lt;/body&gt; tag</label>
				<neon-input-ide v-model="code_body" lang="html"></neon-input-ide>
				<div class="alert alert-warning" style="padding:4px;margin:4px 0 4px 0;line-height:normal;">
					<small>Custom code is not validated. Incorrect code could break the website</small><br>
					<small>Make sure the code is not a security risk - code here could expose sensitive information</small>
				</div>
			</div>
		</InspectorPanel>

	</div>
</template>

<script>
import InspectorPanel from './InspectorPanel.vue';
import Slug from './Slug.vue';
import Vue from "vue";
export default {
	name: 'PageSeo',
	components: { InspectorPanel, Slug },
	props: {
		pageUuid: { type: String, default: '' },
	},
	data() {
		return {
			// store original page objected indexed by uuid
			original: {},
			redirect: true,

			// the index in data we are using for the preview
			previewIndex: 0,

			classData: {
				// list of all rows
				data: [],
				// the class definition with members
				definition: {
					members: {}
				}
			},
		}
	},
	watch: {
		pageUuid: function() {
			this.getClassFields();
			if (! this.original[this.pageUuid]) {
				Vue.set(this.original, this.pageUuid, Object.assign({}, this.page()));
			}
		},
		previewIndex: function() {
			window.cobe.iframeSrc = this.demoObject.slug;
		}
	},
	mounted() {
		window.pageseo = this;
	},
	methods: {
		addFieldToSeoTitle(event) {
			this.title = this.title + ' ' + '{{' +  event.target.value + '}}';
		},
		prev() {
			if (this.previewIndex === 0)
				this.previewIndex = this.classData.data.length-2;
			else
				this.previewIndex = this.previewIndex - 1;
		},
		next() {
			if (this.previewIndex === (this.classData.data.length-2))
				this.previewIndex = 0;
			else
				this.previewIndex = this.previewIndex + 1;
		},
		setOgImage: function() {
			FIREFLY.picker('setOgImage', file => {
				var settings = {cropAuto: true, cropWidth: 1.91, cropHeight: 1};
				FIREFLY.edit(file, settings, (newFile) => {
					this.og_image = newFile.id;
					// make compatible with v-model
				});
				this.og_image = file.id
			});
		},
		save() {
			//save the page data
			this.$store.dispatch('editor/updatePage', {page: this.page(), redirect: this.redirect}).then(result=>{
				this.original[result.id] = Object.assign({}, result);
			})
		},
		page() {
			if (!this.$store.state.editor.pages[this.pageUuid]) {
				return { title: '', description: '' };
			}
			return this.$store.state.editor.pages[this.pageUuid];
		},
		getClassFields() {
			if (! this.page().collection) return [];
			if (! _.has(this.page(), 'collection_class')) return [];
			this.loadClass().then(data => { this.classData = data; })
			// load class definition
		},
		loadClass() {
			return new Promise((resolve, reject) => {
				let url = neon.url('/daedalus/api/class/list', {class: this.page().collection_class});
				$.ajax({type: "POST", url: url, dataType: 'json',}).done(resolve).fail(reject);
			})
		},
		replaceFields: function (str) {
			if (! this.page().collection) return str;
			if (_.isEmpty(str)) return str;
			var demoObject = this.demoObject;
			// replace tags in string
			// this needs to be the post
			_.each(demoObject,(value, key) => {
				if (_.isString(value)) {
					str = str.replace(new RegExp('{{' + key + '}}', 'g'), value);
				}
			});
			return str;
		}
	},
	computed: {
		members() { return this.classData.definition.members },
		classImageFields() {
			return _.filter(this.classData.definition.members, function(member) {
				return member.data_type_ref === 'image_ref';
			});
		},
		pageIsCollection() { return this.page().collection == 1; },
		demoObject() {return this.classData.data[this.previewIndex];},
		slugChanged() {
			if (_.isUndefined(this.original[this.pageUuid])) return false;
			return this.original[this.pageUuid].url !== this.url;
		},
		nice_id: {
			set: function(val) {this.$store.commit('editor/updatePageAttr', { uuid: this.pageUuid, name: 'nice_id', value:val });},
			get: function() {return this.page().nice_id;},
		},
		status: {
			set: function(val) {this.$store.commit('editor/updatePageAttr', { uuid: this.pageUuid, name: 'status', value:val });},
			get: function() {return this.page().status;},
		},
		name: {
			set: function(val) {this.$store.commit('editor/updatePageAttr', { uuid: this.pageUuid, name: 'name', value:val });},
			get: function() {return this.page().name;},
		},
		template: {
			set: function(val) {this.$store.commit('editor/updatePageAttr', { uuid: this.pageUuid, name: 'template', value:val });},
			get: function() {return this.page().template;},
		},
		url: {
			set: function(val) {this.$store.commit('editor/updatePageAttr', { uuid: this.pageUuid, name: 'url', value:val });},
			get: function() {return this.page().url;},
		},
		title: {
			set: function(val) {this.$store.commit('editor/updatePageTitle', { uuid: this.pageUuid, title: val });},
			get: function() {return this.page().title;},
		},
		description: {
			set: function(val) {this.$store.commit('editor/updatePageDescription', { uuid: this.pageUuid, description: val });},
			get: function() {return this.page().description;},
		},
		og_title: {
			set: function(val) {this.$store.commit('editor/updatePageAttr', { uuid: this.pageUuid, name: 'og_title', value:val });},
			get: function() {return this.og_title_use_seo ? this.page().title : this.page().og_title ;},
		},
		og_description: {
			set: function(val) {this.$store.commit('editor/updatePageAttr', { uuid: this.pageUuid, name: 'og_description', value:val });},
			get: function() {return this.og_description_use_seo ? this.page().description :  this.page().og_description} ,
		},
		og_image: {
			set: function(val) {this.$store.commit('editor/updatePageAttr', { uuid: this.pageUuid, name: 'og_image', value:val });},
			get: function() {return this.page().og_image;},
		},
		og_description_use_seo: {
			set: function(val) {this.$store.commit('editor/updatePageAttr', { uuid: this.pageUuid, name: 'og_description_use_seo', value:val });},
			get: function() {return this.page().og_description_use_seo;},
		},
		og_title_use_seo: {
			set: function(val) { this.$store.commit('editor/updatePageAttr', { uuid: this.pageUuid, name: 'og_title_use_seo', value:val });},
			get: function() {return this.page().og_title_use_seo;},
		},
		code_head: {
			set: function(val) { this.$store.commit('editor/updatePageAttr', { uuid: this.pageUuid, name: 'code_head', value:val });},
			get: function() {return this.page().code_head;},
		},
		code_body: {
			set: function(val) { this.$store.commit('editor/updatePageAttr', { uuid: this.pageUuid, name: 'code_body', value:val });},
			get: function() {return this.page().code_body;},
		},
		og_url() {
			return this.fullUrl;
		},
		ogImageUrl() {
			return FIREFLY.getImageUrl(this.replaceFields(this.page().og_image));
		},
		fullUrl() {
			let url = '';
			if (this.pageIsCollection) {
				if (_.isUndefined(this.demoObject)) return '';
				url = this.demoObject.slug;
			} else if (this.url) {
				url = this.url;
			}
			return neon.url(url, false, true).replace(/\/$/, "");
		},
		base() {
			return neon.url('', undefined, true).replace(/\/$/, "");
		},
	},
	filters: {
		truncate: function(text, length, suffix) {
			if (typeof text === 'string') {
				if (text.length <= length) return text;
				return text.substring(0, length) + suffix;
			}
			return '';
		},
	}
};
</script>

<style scoped>
	.control-label { font-weight: bold; }
	.stickyPanel {position:sticky;top:0; background: #e3e7ec80;border-bottom: 1px solid #ccc;padding: 5px;backdrop-filter: blur(5px); }
	.seoPreview { padding: 15px; border:1px solid #ccc; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", sans-serif; background-color:white; overflow-wrap: break-word;}
	.ogPreview_title { font-size:18px; color: #212121; }
	.seoPreview_title { font-size: 18px; line-height: 22px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #1a0dab; overflow-wrap: break-word;}
	.seoPreview_url { color: #006621; font-size: 12px; line-height: 14px; margin-top: 4px; }
	.seoPreview_desc { margin-top: 4px; color: #3C4043; font-size: 12px; line-height: 14px; margin-top: 4px; }
</style>
