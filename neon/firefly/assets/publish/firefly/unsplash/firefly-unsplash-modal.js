/**
 * ------------------------
 * <firefly-editor-modal>
 * ------------------------
 * This component houses a firefly file editor in a modal
 * Depends on neon modal component
 */
Vue.component('firefly-unsplash-modal', {
	props: {
		importCallback: {type: Function, required:false},
	},
	data() {
		return {
			query: '',
			orientation: 'landscape', // portrait | squarish
			page: 1,
			found: {
				total: 0,
				total_pages: 0,
				per_page:30,
				results: [],
				col1: [],
				col2: [],
				col3: []
			}
		}
	},
	template: `
			<div style="position:absolute;top:0;bottom:0;right:0;left:0;">
				<div style="height:40px;display:flex">
					<div>
						<form @submit.prevent="handleSearch()">
							Unsplash search <input v-model="query" type="text" /> <button class="btn">Go</button>
						</form>
					</div>
					<div>
						Viewing {{found.per_page * page}} of {{found.total}}, page {{page}} or {{found.total_pages}}
					</div>
				</div>
				
				<div @scroll.passive="handleScroll" ref="preview" style="padding:8px;display:grid;grid-template-columns:repeat(3,minmax(0,1fr)); grid-gap:8px; position:absolute;top:40px;bottom:0;left:0;right:0; overflow:auto;">
					<div>
						<img :src="img.urls.small" v-for="img in found.col1" :key="img.id" style="margin-bottom:8px;" />
					</div>
					<div>
						<img :src="img.urls.small" v-for="img in found.col2" :key="img.id" style="margin-bottom:8px;" />
					</div>
					<div>
						<img :src="img.urls.small" v-for="img in found.col3" :key="img.id" style="margin-bottom:8px;" />
					</div>
				</div>
			</div>
	`,
	computed: {
	},
	mounted() {
		window.unsplash = this;
		// this.attachScroll();
	},
	methods: {
		chunkArray: function(array, size) {
			let result = []
			for (let i = 0; i < array.length; i += size) {
				let chunk = array.slice(i, i + size)
				result.push(chunk)
			}
			return result
		},



		loadImages: function(append=false) {
			let baseUrl = 'https://api.unsplash.com/search/photos';
			let clientId = '?client_id=KlZW_SWLmMvP1NkzOrA7qkR7NkaEkna4KrSFZ9SHr74';
			let query = '&query='+this.query;
			let perPage = '&per_page=30';
			let orientation = '&orientation=landscape';
			let page = '&page='+this.page;

			if (append == false) {
				this.page = 1;
			}

			$.getJSON(baseUrl+clientId+query+orientation+page+perPage).done(results => {
				console.log(results);
				this.found.total = results.total;
				this.found.total_pages = results.total_pages;
				this.found.results = results.results;
				var bits = this.chunkArray(this.found.results, 10);
				if (append) {
					_.each(bits[0], item => {this.found.col1.push(item)});
					_.each(bits[1], item => {this.found.col2.push(item)});
					_.each(bits[2], item => {this.found.col3.push(item)});
				} else {
					this.found.col1 = bits[0];
					this.found.col2 = bits[1];
					this.found.col3 = bits[2];
				}
			})
			.fail(error => {console.error(error)})
		},
		/**
		 * Handle search submit
		 * @param $event
		 */
		handleSearch: function($event) {
			this.loadImages(false);
		},
		handleScroll() {
			let scrollEl = this.$refs.preview;
			let bottom = scrollEl.scrollHeight - scrollEl.scrollTop == scrollEl.offsetHeight;

			console.log(scrollEl.scrollHeight, scrollEl.scrollTop, scrollEl.offsetHeight, bottom);

			if (bottom) {
				// increase the page number and load in
				this.page = this.page + 1;
				this.loadImages(true);
			}
		}
	}
});
