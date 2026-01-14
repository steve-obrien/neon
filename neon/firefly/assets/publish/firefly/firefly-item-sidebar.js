/**
 * Created by newicon on 23/05/2017.
 */
Vue.component('firefly-item-sidebar', {
	props: {
		item: [Object, Array],
		name: {type: String, required: true}
	},
	data: function() {
		return {
			references: {},
			altFecthing: false
		}
	},
	watch: {
		item: function() {
			this.findReferences();
		},
		'item.file.alt': function (val, oldVal) {
			this.adjustHeight();
		},
		'item.file.caption': function (val, oldVal) {
			this.adjustHeight();
		}
	},
	template: `
		<div v-if="item">
		
			<div v-for="upload in itemsUploading">
				<pre>{{upload}}</pre>
			</div>
		
			<div v-if="item.file" class="max-w-4xl mx-auto">
				<div class="bg-white shadow overflow-hidden ">
					<div class="px-4 py-5 border-b border-gray-200 ">
						<h3 class="text-lg leading-6 font-medium text-gray-900">{{itemName}}</h3>
					</div>
				</div>
				<div class="px-4 py-5 ">
					<dl>
						 <div class="mt-8 form-group ">
							<dt class="text-sm leading-5 font-medium text-gray-500">Caption</dt>
							<dd class="mt-1 text-sm leading-5 text-gray-900 ">
								<textarea ref="textareaCaption" @blur="updateCaption" class="form-control w-full py-2 text-sm border-b" v-model="item.file.caption" placeholder=""></textarea>
							</dd>
						</div>
						<div class="mt-8 ">
							<dt class="text-sm leading-5 font-medium text-gray-500">Alt</dt>
							<dd class="mt-1 text-sm leading-5 text-gray-900 ">
								<textarea ref="textareaAlt" @blur="updateAlt" class="form-control w-full py-2 text-sm border-b" v-model="item.file.alt"></textarea>
								<button :disabled="altFecthing" class="btn" @click="generateAlt">{{ altFecthing ? 'thinking...' : 'generate' }}</button>
								<p class="text-xs text-gray-400"><a href="https://www.w3.org/WAI/tutorials/images/decision-tree" target="_blank" rel="noopener noreferrer">Describe the purpose of the image</a>.</p>
							</dd>
						</div>
						<div class="mt-8 ">
							<dt class="text-sm leading-5 font-medium text-gray-500">Selected</dt>
							<dd class="mt-1 text-sm leading-5 text-gray-900 "  style="display:flex; flex-wrap:wrap;">
								<div v-for="item in selectedItems" >
									<firefly-preview :item="item" style="width:40px; height:40px; overflow:hidden; max-width:40px;max-height:40px;"></firefly-preview>
									<!--<img v-if="item.type!='dir'" :src="urlPreview(item)" style="max-width:40px;max-height:40px;">-->
								</div>
							</dd>
						</div>
						<div class="">
							<dt class="text-sm leading-5 font-medium text-gray-500">Size</dt>
							<dd class="mt-1 text-sm leading-5 text-gray-900 ">{{size}}</dd>
						</div>
						<div class="mt-8 ">
							<dt class="text-sm leading-5 font-medium text-gray-500">Type</dt>
							<dd class="mt-1 text-sm leading-5 text-gray-900 ">{{item.file.mime_type}}</dd>
						</div>
						<div class="mt-8 ">
							<dt class="text-sm leading-5 font-medium text-gray-500">Uuid</dt>
							<dd class="mt-1 text-sm leading-5 text-gray-900 "><code>{{item.file.uuid}}</code></dd>
						</div>
						<div class="mt-8 ">
							<dt class="text-sm leading-5 font-medium text-gray-500">Drive</dt>
							<dd class="mt-1 text-sm leading-5 text-gray-900 "><code>{{item.file.drive}}</code></dd>
						</div>
						<div class="mt-8 ">
							<dt @click="copyLink" class="text-sm leading-5 font-medium text-gray-500">Public Link</dt>
							<dd class="mt-1 leading-5 text-gray-900 ">
								<input style="width:100%" ref="link" readonly :value="link"/>
							</dd>
						</div>
						<div class="mt-8 ">
							<dt class="text-sm leading-5 font-medium text-gray-500">Path</dt>
							<dd class="mt-1  leading-5 text-gray-900 ">
								<input style="width:100%" ref="media" readonly :value="item.file.drive + '/' + item.file.path"/>
							</dd>
						</div>
						<div class="mt-8 ">
							<dt class="text-sm leading-5 font-medium text-gray-500">Uploaded</dt>
							<dd class="mt-1 text-sm leading-5 text-gray-900 "><code>{{item.file.created_at}}</code></dd>
						</div>
					</dl> 
					<div class="mt-8 ">
						<dt class="text-sm leading-5 font-medium text-gray-500">References</dt>
						<dd class="mt-1 text-sm leading-5 text-gray-900 ">
							<div v-for="ref in references">
								<a :href="refUrl(ref.class, ref._uuid)">{{ref.class}} - {{ref._uuid}}</a>
							</div>
						</dd>
					</div>
					<button class="btn btn-default" @click="replace">Replace</button>
				</div>
			</div>
				
		</div>
	`,
	mounted() {
		this.findReferences();
	},
	methods: {
		adjustHeight() {
			[this.$refs.textareaAlt, this.$refs.textareaCaption].map(textarea => {
				textarea.style.height = 'auto';
				textarea.style.height = textarea.scrollHeight + 'px';
			});
		},
		generateAlt () {
			this.altFecthing = true
			const url = neon.url('/firefly/api/file/generate-alt',{uuid: this.item.file.uuid});
			const eventSource = new EventSource(url,  { withCredentials: true });
			this.item.file.alt = '';
			// Event handler for the 'message' event
			eventSource.addEventListener('message', (event) => {
				console.log('New message received:', event.data);
				// You can add custom logic here to handle the received data
				const data = JSON.parse(event.data);
				this.item.file.alt = (this.item.file.alt || '') + (data.delta.content || '')
			});

			eventSource.addEventListener('stop', (event) => {
				this.altFecthing = false
				// If we don't close the event - the browser will continue to request it, 
				// and we will be looping forever.
				eventSource.close();
				this.updateAlt()
			});
		
			// Event handler for the 'error' event
			eventSource.onerror = function(event) {
				this.altFecthing = false
				eventSource.close();
				console.error('Error occurred:', event);
			};
		},
		replace() {
			FIREFLY.picker('replace', (file) => {
				// id of the file currently selected item.file.uuid
				// file.id is the id of the file to replace the item with.
				// original = item.file.uuid
				// replaceWith = file.id
				// fire an ajax request
				const replaceUrl = neon.url('/firefly/api/file/replace');
				$.ajax({ type: "POST", url: replaceUrl, dataType: 'json', 
					data: { original: this.item.file.uuid, replace: file.id } 
				})
					.done((item) => { console.log(item) })
					.fail((error) => { console.error(error) });
			});
		},
		refUrl(className, id) {
			return neon.url('/daedalus/index/view-object', { "type": className, "id": id})
		},
		updateCaption: function() {
			var url = neon.url('/firefly/api/file/update', { id: this.item.file.uuid });
			$.ajax({type: "POST", url: url, dataType: 'json', data: this.item.file })
				.done((item) => { console.log(item) })
				.fail((error) => { console.error(error) });
		},
		updateAlt: function() {
			var url = neon.url('/firefly/api/file/update', { id: this.item.file.uuid });
			$.ajax({type: "POST", url: url, dataType: 'json', data: this.item.file })
				.done((item) => { console.log(item) })
				.fail((error) => { console.error(error) });
		},
		referenceUrl: function(classRef, uuid) {
			return neon.url('/daedalus/index/edit-object',{type: classRef, id: uuid});
		},
		findReferences: function() {
			if (!_.has(this.item, 'file.uuid')) return;
			this.$store.dispatch('Firefly/FIND_REFERENCES', {uuid: this.item.file.uuid, name: this.name}).then(data => {
				this.references = data;
			})
		},
		urlPreview: function(item) { return neon.base() + '/firefly/file/get?id=' + item.id; },
		deleteItem: function() { this.$store.dispatch('Firefly/DELETE_ITEM', {id: this.item.id, name: this.name}); },
		copyLink() {
			var el = this.$refs.link;
			/* Select the text field */
			el.select();
			el.setSelectionRange(0, 99999); /*For mobile devices*/
			/* Copy the text inside the text field */
			// document.execCommand("copy");
			/* Alert the copied text */
		},
	},
	computed: {
		alt: {
			get() {
				return this.item.file.meta.alt
			},
			set(value) {
				if (this.item.file.meta)
				Vue.set(this.item.file.meta, 'alt', value);
			}
		},
		link() { return neon.url('/firefly/file/get', {id:this.item.id}, true); },
		size: function() { return neon.formatBytes(this.item.file.size); },
		itemsUploading: function () { return this.$store.getters['Firefly/getItemsUploading'](this.name); },
		selectedItems: function() { return this.$store.getters['Firefly/getSelectedItems'](this.name); },
		itemName:   function() { return this.$store.getters['Firefly/getItemName'](this.item); },
		itemExists: function() { return this.$store.getters['Firefly/getItemExists'](this.name, this.item.id); }
	}
});
