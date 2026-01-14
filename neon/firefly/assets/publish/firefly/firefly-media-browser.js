// const listeners = [];
//
// export const push = route => {
// 	const previousRoute = window.location.pathname;
// 	window.history.pushState(null, null, route);
// 	listeners.forEach(listener => listener(route, previousRoute));
// };
//
// export const listen = fn => {
// 	listeners.push(fn);
// };

/**
 * ------------------------
 * <firefly-media-browser>
 * ------------------------
 * @param {String} name
 */
Vue.component('firefly-media-browser', {
	props: {
		/**
		 * The name of the media picker - all browsers store their state against their picker key
		 * state.pickers[name]
		 * neon.Store.state.Firefly.pickers[name]
		 * @type {String}
		 */
		name: { type: String, required: true },
	},
	data: function () {
		return {
			// 'default' | 'lassoing'
			status: 'default',
			showDeleted: false,
			listeners: [],
			hash: window.location.hash,
			search: '',
			searchData: []
		}
	},
	template: `
		<div class="fireflyMediaContainer" style="display:flex">
			<!-- File List -->
			<div class="ffFilesList" ref="stage" style="flex:1">
				<!--<firefly-item-parent :name="name" @click.stop="selectItem(item, $event)" @dblclick="parentDoubleClick(item)"></firefly-item-parent>-->
				<div >
					<input type="text" v-model="search" @keyup="handleSearch" placeholder="Search" />
					<button v-if="search" @click="search = ''">X</button>
				</div>
				<div v-if="search==''">
					<firefly-item
						v-for="(item, key) in items"
						v-if="item.deleted != 1"
						:name="name"
						:key="item.id"
						:item="item"
						:selected="isSelected(item.id)"
						@click.stop="selectItem(item, $event)"
						@dblclick="doubleClick(item)"
						@dragStart="dragStart(item)"
						:data-id="item.id"
						class="ffItem"
						>
					</firefly-item>
				</div>
				<div v-else>
					<firefly-item
						v-for="(item, key) in searchData"
						v-if="item.deleted != 1"
						:name="name"
						:key="item.id"
						:item="item"
						:selected="isSelected(item.id)"
						@click.stop="selectItem(item, $event)"
						@dblclick="doubleClick(item)"
						@dragStart="dragStart(item)"
						:data-id="item.id"
						class="ffItem"
						>
					</firefly-item>
				</div>
			</div>
			<!-- drop upload -->
			<firefly-media-uploader :path="path" :name="name" :hide="!itemsLoading && items.length == 0"></firefly-media-uploader>
			<div class="fireflySidebar">
				<firefly-item-sidebar v-if="selectedItem" :item="selectedItem" :name="name"></firefly-item-sidebar>
			</div>
		</div>
	`,
	computed: {
		selectedItem() { return this.$store.getters['Firefly/getSelectedItem'](this.name); },
		path() { return this.$store.getters['Firefly/getPath'](this.name); },
		startPath() { return this.$store.getters['Firefly/getStartPath'](this.name); },
		items() { return this.$store.getters['Firefly/getItems'](this.name); },
		browserItems() { return this.$store.getters['Firefly/getItemsOrderedByName'](this.name); },
		hasEditor() { return this.$store.getters['Firefly/getItemHasEditor'](this.selectedItem); },
		itemsLoading() { return this.$store.getters['Firefly/getItemsLoading'](this.name); },
		browserHistory() { return this.$store.getters['Firefly/getBrowserHistory'](this.name); },
		isEditing() { return this.$store.state.Firefly.editingItemNameId },
	},
	methods: {
		handleSearch() {
			// Perform AJAX request when user types in the input
			// You can use Axios, Fetch API, or any other library for AJAX
			// For simplicity, let's assume we're using Fetch API
			fetch(neon.url('/firefly/api/media/search', {q: this.search.toLowerCase()}))
				.then(response => {
					if (response.ok) {
						return response.json();
					}
					throw new Error('Network response was not ok.');
				})
				.then(data => {
					// Handle the response data
					console.log(data);
					this.searchData = data;
				})
				.catch(error => {
					// Handle errors
					console.error('There was a problem with your fetch operation:', error);
				});
		},
		isEditingItemName(item) { return this.$store.getters['Firefly/getIsEditingItemName'](item); },
		isSelected(id) { return this.$store.getters['Firefly/getIsSelected'](this.name, id); },
		selectPrevious(shift) { this.$store.commit('Firefly/SELECT_PREVIOUS_ITEM', { name: this.name, shift: shift }); },
		selectNext(shift) { this.$store.commit('Firefly/SELECT_NEXT_ITEM', { name: this.name, shift: shift }); },
		selectItem(item, event) {
			if (event.shiftKey) {
				this.$store.commit('Firefly/SELECT_ALL_BETWEEN', { item: item, name: this.name });
			} else {
				// Sometimes event.metaKey will return false when the CTRL key is used
				let isMetaKey = event.metaKey;
				if (!isMetaKey && event.ctrlKey) { // CTRL key event
					isMetaKey = true;
				}
				this.$store.commit('Firefly/SELECT_ITEM', { item: item, name: this.name, multiple: isMetaKey });
			}
			this.$emit('onSelect', item);
		},
		unSelect() {
			this.$store.commit('Firefly/UNSELECT', { name: this.name })
		},
		stageClicked() {
			this.unSelect();
		},
		loadPath(path, pushState) {
			this.$store.dispatch('Firefly/LOAD_ITEMS', { path: path, name: this.name, pushState: pushState });
		},
		deleteSelected: function () { this.$store.dispatch('Firefly/DELETE_SELECTED', { name: this.name }); },
		doubleClick: function (item) {
			if (item.type === 'dir') {
				// Don't redirect on double click if we are editing the items name
				if (!this.isEditingItemName(item)) {
					this.loadPath(item.path);
					this.search = ''
				}
			} else {
				this.$emit('onDoubleClickItem', item);
			}
		},
		dragStart: function (item) {
			// if item is not selected then select it
			if (!this.isSelected(item.id)) {
				this.$store.commit('Firefly/SELECT_ITEM', { item: item, name: this.name })
			}
		},
		selectAll: function () {
			this.$store.commit('Firefly/SELECT_ALL', { path: this.path, name: this.name });
		},
		onKeyup: function (event) {
			if (event.which === 37 && !this.isEditing) // left key
				this.selectPrevious(event.shiftKey);
			if (event.which === 39 && !this.isEditing) // right key
				this.selectNext(event.shiftKey);
			if (event.metaKey && event.which === 8 && !this.isEditing) // back space key
				this.deleteSelected();
			if (event.which === 27) // escape key
				this.unSelect();
		}
	},
	mounted: function () {
		if (window.location.hash !== '') {
			this.loadPath(decodeURIComponent(window.location.hash), false);
		} else {
			this.loadPath(this.startPath, false);
		}
		window.addEventListener('keyup', this.onKeyup);
		this.$once('hook:beforeDestroy', function () {
			window.removeEventListener('keyup', this.onKeyup);
		});
		// only do this if browser history is enabled
		if (this.browserHistory) {
			window.addEventListener('popstate', event => {
				this.loadPath(window.location.hash, false)
			})
		}

		// attach stageClick event:
		this.$refs.stage.addEventListener('click', this.stageClicked);
		this.$once('hook:beforeDestroy', () => {
			this.$refs.stage.removeEventListener('click', this.stageClicked);
		});

		$(".ffFilesList").selectable({
			filter: "[data-id]",
			distance: 30,
			start: (event) => {
				this.status = 'lassoing';
				// detach the unselect event
				// if you finished dragging the lasso over the stage - this will also trigger the stageClicked event
				// default action of stageClicked is to unselect selected items.
				// therefore we want to remove this event listener until lassoing has finished
				this.$refs.stage.removeEventListener('click', this.stageClicked);
				if (!event.shiftKey)
					this.unSelect();
			},
			selected: function (event, ui) {
				this.$store.commit('Firefly/SELECT_ITEM', { item: ui.selecting.getAttribute('data-id'), name: this.name, multiple: true, });
			},
			selecting: (event, ui) => {
				this.$store.commit('Firefly/SELECT_ITEM', { item: ui.selecting.getAttribute('data-id'), name: this.name, multiple: true, });
			},
			unselecting: (event, ui) => {
				this.$store.commit('Firefly/UNSELECT_ITEM', { item: ui.unselecting.getAttribute('data-id'), name: this.name, multiple: true });
			},
			stop: (event) => {
				this.status = 'default';
				event.preventDefault();
				// reattach the stage click event
				// place in a timeout so the release of the mouse triggering this action does not imidiately
				// invoke the click once reattached
				setTimeout(() => {
					this.$refs.stage.addEventListener('click', this.stageClicked);
				}, 0);
			}
		});
	},
});

