<template>
	<div class="cobe" :style="'grid-template-columns: minmax(200px, min-content) auto '+inspectorWidth+'px'">

		<!-- title toolbar -->
		<div class="toolbar">
			<a class="btn btn-default" :href="'/cms/index/index'"><span class="fa fa-chevron-left">&nbsp;</span>Exit Edit Mode</a>
			<span class="workbenchHeader_title"> Cobe CMS: Select a page below to edit its content.</span>
		</div>

		<!-- sidebar navigator -->
		<div class="tools">
			<div>
				<ul class="tree" v-cloak>
					<input style="width:100%;border:0;padding-left:4px;" placeholder="Search" v-model="pageSearch" />
					<li v-for="page in pagesOrdered" @click="onPageSelected(page)" :key="page.id"   >
						<div class="niNode isPage" :class="{'isSelected': (onPage==page.nice_id)}">
							<span class="niNode_icon">
								<i v-if="page.collection==1" class="fa fa-folder"></i>
								<i v-if="page.collection==0" class="fa fa-file-o"></i>
							</span>
							<span class="niNode_label" >
							<span class="page_title">{{page.name || (page.title || 'Unamed')}}</span>
						</span>
						</div>
					</li>
				</ul>
			</div>
			<div>
				<ul class="tree isRoot" style="margin:0px;">
					<cmp-tree-node :cmp="rootComponent"></cmp-tree-node>
				</ul>
			</div>
		</div>
		<!-- stage -->
		<div class="stage" >
			<div class="resizeHandle" @mousedown="onResizeHandleMouseDownRight($event, 'inspectorWidth')"></div>
			<div class="stage_preview" id="stagePreview" >
				<stage-selector id="stage_overlay" ></stage-selector>
				<iframe v-if="onPage" @load="frameLoad()" ref="siteFrame" id="siteFrame" frameborder="0" :src="iframeSrc" :key="iframeSrc" ></iframe>
				<div v-else id="emptyFrame">
					<div v-if="pages.length!=0">Select a page from the left to begin editing!</div>
					<div v-else>Create a page</div>
				</div>
			</div>
		</div>

		<!-- sidebar inspector -->
		<div class="inspector">
			<PageSeo :page-uuid="page ? page.id : '' "></PageSeo>
			<!-- <InspectorPanel title="Page Data" :startOpen="false">
				<pre>{{page}}</pre>
			</InspectorPanel> -->
			<pre v-for="(component, uuid) in selectedComponents" :key="uuid">{{uuid}} {{component}} </pre>
			<textarea rows="10" @input="onUpdateComponentProps(uuid, $event)" style="width:100%" v-for="(component, uuid) in selectedComponents" :key="uuid+'textarea'">{{componentProps(component.cmp, component)}}</textarea>
			<br/>
			<!-- <button @click="onDelete">Delete</button> -->
		</div>
	</div>
</template>

<script>

import StageSelector from './StageSelector.vue';
import PageSeo from './inspector/PageSeo.vue';
import InspectorPanel from './inspector/InspectorPanel.vue';

// noinspection JSAnnotator
// const pages = <?= json_encode($pages) ?>;

const devices = {
	768: [
		'iPad Pro (7.9")',
		'iPad 2017/2018',
		'iPad Air/Air 2',
		'iPad 4th gen (and earlier)',
		'iPad mini (all models)',
		'Microsoft Surface',
	],
	800: [
		'Galaxy Note 10.1',
		'Galaxy Tab',
	],
	1024: [
		'iPad Pro (12.9")',
		'Galaxy Tab S3',
		'Surface Pro 3 (Portrait)',
	],
	1280: [
		'Retina MacBook (12")',
		'MacBook Pro (early 13" models)',
	]
}

// Sync the stores.
// Set up two way messaging between the iframe website in the stage and the parent windows store
// window.addEventListener("message", receiveMessage, false);
//
// function receiveMessage(event) {
// 	if (event.origin !== NEON_DATA.host.replace(NEON_DATA.base, ''))
// 		return;
// 	if (event.data.name === 'neonEditorState') {
// 		console.log('received' , event.data);
// 		neon.Store.commit('editor/syncState', _.cloneDeep(event.data.state));
// 	}
// }

// set up by the view file that uses this component

export default {
	name: "Cobe",
	store: neon.Store,
	components: {StageSelector, PageSeo, InspectorPanel},
	props: {
		startPage: { type: String, default: '' }, // uuid of the page to start on
		loadPages: { type: Object, default() { return {}; } }, // collection indexed by page uuid of page objects
	},
	data() {
		return {
			// page nice_id
			onPage: '',
			iframeSrc: '',
			previewBounds:{},
			inspectorWidth: 300,
			pageSearch: '',
		}
	},
	watch: {
		onPage: function(newVal, oldVal) {
			this.iframeSrc = this.pageUrl;
		},
	},
	computed: {
		rootComponent() {
			return this.$store.getters['editor/rootComponent'];
		},
		components() {
			return this.$store.getters['editor/components'];
		},
		selectedComponents() {
			var res = {};
			_.forEach(this.$store.getters['editor/getSelected'], uuid => {
				res[uuid] = this.$store.getters['editor/getComponent'](uuid);
			});
			return res;
		},
		pagesOrdered() {
			return _.sortBy(this.pages, 'url')
		},
		pages() {
			if (this.pageSearch) {
				let q = _.toLower(this.pageSearch);
				return _.filter(this.$store.getters['editor/pages'], (page) => {
					return (_.toLower(page.name).search(q) >= 0)
				})
			}
			return this.$store.getters['editor/pages'];
		},
		page() {
			return _.find(this.pages, { nice_id: this.onPage });
		},
		page_description: {
			set: function(val) {
				this.page.description = val;
			},
			get: function() {
				return this.page.description;
			},
		},
		page_title: {
			set: function(val) {
				this.page.title = val;
			},
			get: function() {
				return this.page.title;
			},
		},
		fields() {
			return [
				{class:'text', name:'nice_id', id:'nice_id', label:'ID', value: this.page.nice_id, readOnly:true},
				{class:'text', name:'id', id:'id', label:'UUID', value: this.page.id, readOnly:true},
				{class:'text', name:'title', id:'title', label:'Title',  value: this.page.title },
				{class:'textarea', name:'description', id:'description', label:'SEO Description',  value: this.page.title },
			];
		},
		pageUrl() {
			return neon.url(_.get(this.page, 'url', null));
		}
	},
	methods: {
		onPageSelected: function(page) {
			this.onPage = page.nice_id
		},
		onResizeHandleMouseDownRight: function(e, prop) {
			const vm = this;
			const mousemove = function(e) {
				if (e.clientX <= 100)
					return;
				if (e.clientX >= (window.innerWidth - 200))
					return;
				console.log(e);
				vm[prop] = window.innerWidth - e.clientX;
				if(e.stopPropagation) e.stopPropagation();
				if(e.preventDefault) e.preventDefault();
				e.cancelBubble=true;
				e.returnValue=false;
				return false;
			};
			const mouseup = function () {
				document.removeEventListener('mousemove', mousemove);
				document.removeEventListener('mouseup', this);
			};
			document.addEventListener('mousemove', mousemove);
			document.addEventListener('mouseup', mouseup);
		},
		onResizeHandleMouseDown: function(e, prop) {
			const vm = this;

			const mousemove = function(e) {
				if (e.clientX <= 100)
					return;
				if (e.clientX >= (window.innerWidth - 200))
					return;
				vm[prop] = e.clientX;
				if(e.stopPropagation) e.stopPropagation();
				if(e.preventDefault) e.preventDefault();
				e.cancelBubble=true;
				e.returnValue=false;
				return false;
			};
			const mouseup = function () {
				document.removeEventListener('mousemove', mousemove);
				document.removeEventListener('mouseup', this);
			};
			document.addEventListener('mousemove', mousemove);
			document.addEventListener('mouseup', mouseup);
		},
		onUpdateComponentProps: function(uuid, $event) {
			let props;
			try {
				props = JSON.parse($event.target.value);
			} catch (e) {
				return false;
			}
			this.$store.commit('editor/updateComponentProps', {uuid: uuid, props: props});
		},
		componentProps(cmp, component) {
			var c = _.cloneDeep(component);
			_.defaults(c.props, neon.phoebe.getCmpDefault(cmp));
			return c;
		},
		homePage() {
			return _.find(this.pages, {url:'/'});
		},
		frameLoad() {
			var frameUrl = this.$refs.siteFrame.contentWindow.location.pathname.replace(neon.base(), '');
			let page = _.find(this.pages, {url:frameUrl});
			if (page)
				this.onPage = page.nice_id;
			return true;
		},
	},
	created() {
		this.$store.commit('editor/setPages', { pages: this.loadPages });
	},
	mounted() {

		this.$store.subscribe((mutation, state) => {
			console.log(state.editor, mutation);
			var m = JSON.parse(JSON.stringify(mutation));
			this.$refs.siteFrame.contentWindow.postMessage({name:'neonEditorToFrame', state:state.editor, m}, '*');
		});

		this.onPage = this.startPage;
		// load the homepage if it exists and no id is defined
		if (this.onPage === '') {
			let homePage = this.homePage();
			if (homePage) {
				this.onPage = homePage.nice_id;
				this.iframeSrc = this.pageUrl;
			}
		}

		window.cobe = this;
		var vm = this;

		// apply a hover effect to elements in stage window:
		// currently under development
		// works well but requires scroll position to be handled

	}
};

</script>

<style scoped>
	.cobe {
		display: grid;
		grid-template-areas: 'header header header' 'left stage inspector';
		grid-template-columns: minmax(200px, min-content) auto 240px;
		grid-template-rows: min-content auto;
		height: calc(100vh -50px);
		--headerHeight: 50px;
		--bgPanelColor: #F4F6F9;
		position:absolute;
		top:50px;
		bottom:0;
		left:0;
		right:0;
	}
	.toolbar {grid-area: header; grid-row: 1; height: var(--headerHeight);border-bottom: 1px solid #A7A7A8;background-color: var(--bgPanelColor);border-top: 1px solid #fff;}
	.tools,.inspector { background-color:#E3E7ED; display:flex; flex-direction:column; overflow:auto; }
	.inspector {overflow:auto;}
	.inspector pre {font-size:10px; font-family:Monaco, monospace; border:none; background:none;}
	.stage {background-color: var(--bgPanelColor);padding:28px;position:relative;}
	.stage_preview {height:100%; box-shadow:0 2px 8px 0 rgba(0,0,0,0.27); background:white; overflow: visible; position:relative; margin:0 auto; }
	.stage_preview iframe { width:100%; height:100% }
	#stage_overlay {pointer-events: none;overflow: visible;}

	/** could be extracted into generic tree component: */
	.tree,.tree ul {margin:0;padding:0;list-style:none;position:relative;}
	.tree:before,.tree ul:before {content:"";display:block;width:0;position:absolute;top:0;bottom:0;left:0;}
	.tree li {margin:0;padding:0;line-height:2em;position:relative;font-weight: 500;}
	.tree li:before {content:"";display:block;width:10px;height:0;margin-top:-1px;position:absolute;top:1em;left:0;}
	.tree.isRoot > li:before {border:none;}

	li {text-align: left;}
	.tree .niNode {display:block; color:#333; white-space: nowrap; font-size:12px;line-height:27px;}
	.niNode_caret {margin-top:-2px;display: inline-block;width: 8px;height: 10px;vertical-align: middle;border-left: 8px solid #787979;border-top: 5px solid transparent;border-bottom: 5px solid transparent;margin-right:5px;}
	.niNode_icon {margin-top:-2px;display:inline-block;margin-right:5px;fill:#777; color:#777;}

	.niNode { padding:0 10px 0 10px; cursor:pointer}
	.niNode.isPage {font-weight:bold;}
	.niNode.isHover {outline:1px solid #007AFF !important}
	.niNode.isSelected {background-color: #007AFF;color: #fff;}
	.niNode.isLeaf .niNode_caret{visibility:hidden;}
	.niNode.isOpen .niNode_caret {transform:rotate(90deg);}
	.niNode.isSelected .niNode_caret {border-left-color:#fff;}
	.niNode.isSelected .niNode_icon {color:#fff;}
	.niNode_label {cursor:pointer;}
	/** ni:tree **/

	[v-cloak] {display: none;}

	#emptyFrame {padding:20px;}
	#responsiveHandle {position: absolute;right:-14px;top: 50%;width: 14px;height: 38px;margin-top: -19px;background: #808080;cursor: col-resize;pointer-events: all;border-top-right-radius: 3px;border-bottom-right-radius: 3px;}
	#responsiveHandle:after, #responsiveHandle:before {content: "";position: absolute;top: 8px;bottom: 8px;width: 1px;background: #a6a6a6;}
	#responsiveHandle:before {left:5px;}
	#responsiveHandle:after {left:8px;}

	#stage_overlay {position:absolute;top:0;bottom:0;right:0;left:0;overflow: visible;}

	.resizeHandle {top:0; bottom:0;right:0;position:absolute;width:4px; cursor:ew-resize;}
	.resizeHandle:hover {background:#1da7ee;}
</style>
