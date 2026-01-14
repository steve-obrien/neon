<template>
	<div>
		<NeonCmpList ref="html" :render="rootComponent"></NeonCmpList>
		<div class="niEditor_toolbar" v-if="editMode">Toolbar</div>
	</div>
</template>

<script>
	/**
	 * The neon editor component is responsible for rendering components on the website when in edit mode.
	 *
	 * Withing smarty:
	 * {editor}
	 * {/editor}
	 *
	 * The editor component is only used during edit mode - otherwise server rendered content is returned.
	 * Rendering the components dynamically allows us to control the user input and understand intentions.
	 * @todo rename to ContentEditor
	 */

	import Vue from 'vue';
	//import _ from 'lodash';
	import NeonCmpList from './NeonCmpList.vue';
	import NeonCmp from './NeonCmp.vue';
	import NeonCard from "./components/NeonCard";
	import NeonComeTalkToUs from "./components/NeonComeTalkToUs";
	import CmpTree from './CmpTree.vue';
	import CmpTreeNode from './CmpTreeNode.vue';
	import Store from './store.js';
	import PhoebePlugin from './PhoebePlugin';
	import {GETTERS, MUTATIONS} from "./store";

	Vue.component('NeonCmpList', NeonCmpList);
	Vue.component('NeonCmp', NeonCmp);
	Vue.component('NeonCard', NeonCard);
	Vue.component('NeonComeTalkToUs', NeonComeTalkToUs);
	Vue.component('CmpTree', CmpTree);
	Vue.component('CmpTreeNode', CmpTreeNode);

	PhoebePlugin.install(Vue);

	var depth = function(component) {
		let depth = 0, parent = component;
		while(parent !== undefined) { parent = parent.$parent; depth++; }
		return depth;
	}
	var indent = function(component) {
		return '  '.repeat(depth(component))
	}

	export default {
		name: 'NeonEditor',
		store: Store,
		provide: function() {
			return {editor: this};
		},
		props: {
			contentKey: {type: String},
			components: {type:Object}
		},
		created() {
			this.$store.subscribe((mutation, state) => {
				window.parent.postMessage({name:'neonEditorState', state:state.editor, mutation}, '*');
			});
			this.$store.commit(MUTATIONS.setComponents, this.components)
		},
		computed: {
			rootComponent() {
				return this.$store.getters[GETTERS.rootComponent];
			},
			editMode: {
				get() {
					return this.$store.getters[GETTERS.isEditMode];
				},
				set(value) {
					this.$store.commit(MUTATIONS.setEditMode, value);
				}
			},
		},
		methods: {
			getCmpObject(cmpName) {
				if (Vue.component(cmpName))
					return Vue.component(cmpName).options;
				// eslint-disable-next-line no-console
				console.error(cmpName + ' is not a global registered vue component');
				return {};
			},
			deleteSelected() {
				let uuids = this.$store.getters[GETTERS.getSelected];
				this.$store.commit(MUTATIONS.deleteComponents, {uuids});
			},
			getCmpDefaults(cmpName) {
				return neon.phoebe.getCmpDefault(cmpName);
			},
			html() {
				this.editMode = false;
				this.$store.state.selected = {};
				this.$store.state.hovering = '';
				return new Promise((resolve, reject) => {
					this.$nextTick(() => {
						var html = this.$el.innerHTML;
						html = html.replace(/<!---->/g, '');
						resolve(html);
						this.editMode = true;
					})
				})
			},
			save: async function () {
				// save to the db
				var html = await this.html();
				var props = {
					components: this.$store.getters[GETTERS.components],
					contentKey: this.contentKey
				};
				var editorProps = JSON.stringify(props);
				var content = '<!-- ni:editor ' + editorProps + ' -->\n' + html + '\n<!-- /ni:editor -->' ;
				var data = {
					content: content, //JSON.stringify({html: this.html(), components: {}}),
					key: this.contentKey,
					pageId: null
				};
				$.ajax({
					method: "POST",
					url: neon.url('/cms/data/update-content-widget'),
					data: data,
					success: function (response) {
						alert('saved');
					}
				});
			}
		},
		mounted() {
			window.editor=this;
			// create toolbar
			const toolbarContainer = document.createElement('div')
			document.body.appendChild(toolbarContainer)
			new Vue({
				parent: neon.app,
				render: h => h('neon-editor-toolbar')
			}).$mount(toolbarContainer)

			if (window.neon && window.neon.editor === undefined) {
				window.neon.editor = this;
			}

			// only create one for debug
			if (window.niEditor === undefined)
				window.niEditor = this;
		}
	}

	Vue.component('neon-editor-toolbar', {
		inject: {'editor': { default: false }},
		template: `
			<div v-if="isEditMode" style="position:fixed;bottom:0;left:0;right:0;display:flex;background-color:#000;z-index:999999999999999;">
				<button @click="save" class="btn btn-primary btn-sm">Save</button>
				<button @click="deleteSelected" class="btn btn-danger btn-sm">Delete</button>
				<button @click="editPage">Edit</button>
				<button @click="exitEditMode">Exit</button>
				<div class="btn-group mr-2 btn-group-sm" role="group" aria-label="Undo group">
					<button class="btn btn-secondary btn-sm" :disabled="!canUndo" @click="undo">undo {{history.length - 1}}</button>
					<button class="btn btn-secondary btn-sm"  :disabled="!canRedo" @click="redo">redo {{(history.length - currentIndex) - 1}}</button>
				</div>
				<div class="badge">Components: {{componentCount}}</div>
			</div>
		`,
		created() {
			let firstState = _.cloneDeep(this.$store.state);
			this.addState(firstState);
			this.$store.subscribe((mutation, state) => {
				if (this.ignoreMutations.indexOf(mutation.type) === -1) {
					this.addState(_.cloneDeep(state));
				}
			});
		},
		mounted() {
			window.toolbar = this;
		},
		data() {
			return {
				history: [],
				// the number of history states to remember
				historyStates: 1000,
				currentIndex: -1,
				ignoreMutations: ['editor/hover']
			}
		},
		computed:{
			componentCount() {
				return Object.keys(this.$store.state.editor.components).length;
			},
			isEditMode() {
				return this.$store.getters[GETTERS.isEditMode];
			},
			canUndo() {
				return this.history[this.currentIndex - 1] !== undefined;
			},
			canRedo() {
				return this.history[this.currentIndex + 1] !== undefined;
			},
		},
		methods: {
			addState(state) {
				// may be we have to remove redo steps
				if (this.currentIndex + 1 < this.history.length) {
					this.history.splice(this.currentIndex + 1);
				}
				this.history.push(state);
				this.currentIndex++;
				if (this.history.length === this.historyStates) {
					this.history.splice(0,1);
				}
			},
			undo() {
				if (!this.canUndo) return;
				const prevState = this.history[this.currentIndex - 1];
				// take a copy of the history state
				// because it would be changed during store mutations
				// what would corrupt the undo-redo-history
				// (same on redo)
				this.$store.replaceState(_.cloneDeep(prevState));
				this.currentIndex--;
			},
			redo() {
				if (!this.canRedo) return;
				const nextState = this.history[this.currentIndex + 1];
				this.$store.replaceState(_.cloneDeep(nextState));
				this.currentIndex++;
			},
			editPage() {
				location.href = neon.url('cms/editor/index', {"id":'styleguide'});
			},
			save() {
				window.niEditor.save();
			},
			deleteSelected() {
				window.niEditor.deleteSelected();
			},
			postMessage() {
				// origin
				//window.parent.postMessage('Hey there',  '*');
			},
			exitEditMode() {
				$.post(neon.url('cms/editor/exit-edit-mode')).done(function() {
					location.href = location.href.replace('edit=1', '');
				})
			}
		}
	});

	// only listen to parent messages if we have a parent
	window.addEventListener("message", receiveParentMessage, false);
	function receiveParentMessage(event) {
		if (event.data.name === 'neonEditorToFrame') {
			if (event.data.mutation.type !== 'editor/syncState') {
				neon.Store.commit(event.data.mutation.type, event.data.mutation.payload);
			}
		}
	}


	Vue.component('NeonThing', {
		name: 'NeonThing',
		comments: true,
		props: {
			content: {type: String, default:'HERE IS A THING'}
		},
		template: `<div>
			{{content}}
		</div>`,
		methods: {
			save() {
				return `${indent(this)}<!-- cmp:NeonThing -->\n${indent(this)}<div>${this.content}</div>\n${indent(this)}<!-- /cmp:NeonThing -->`
			}
		}
	});

	Vue.component('NeonColumns', {
		inject: {'editor': { default: false }},
		comments: true,
		props: {
			uuid: {type: String},
			columns: {type: Number, default:2},
			children: {type: Array, default: function() {return [];}}
		},
		template: `<NeonCmpList class="row" :style="editor.editMode?'padding:16px;':''" :render="uuid"></NeonCmpList>`,
		methods: {
			save() {
				var children = this.$children.map(c => c.save()).join("\n");
				return `${indent(this)}<!-- cmp:NeonColumns -->\n${indent(this)}<div class="row" style="padding:10px;">\n${children}\n${indent(this)}</div>\n${indent(this)}<!-- /cmp:NeonColumns -->`;
			}
		}
	});

	Vue.component('NeonColumn', {
		inject: {'editor': { default: false }},
		comments: true,
		props: {
			me: {},
			columns: {type: Number, default:2},
			children: {type: Array, default: function() {return [];}}
		},
		template: `
			<NeonCmpList :render="uuid" class="col"></NeonCmpList>
		`,
		methods: {
			save() {
				var children = this.$children.map(c => c.save()).join("\n");
				return `${indent(this)}<!-- cmp:NeonColumn -->\n${indent(this)}<div class="col" style="padding:10px;">\n${children}\n${indent(this)}</div>\n${indent(this)}<!-- /cmp:NeonColumn -->`;
			}
		}
	})

	Vue.component('NeonGrid', {
		inject: {'editor': { default: false }},
		props: {
			me: {},
			columns: {type: Number, default:2},
			children:[]
		},
		template: `<NeonCmpList class="row" style="border:1px solid black;padding:10px;" :render="uuid"></NeonCmpList>`,
		methods: {
			save() {
				return indent(this) + this._name + ' provide save function'
			}
		}
	});

	Vue.component('NeonHeader', {
		props: {
			content: {type: String, default:'Enter Heading text'}
		},
		template: `<h1>{{content}}</h1>`,
	})

	Vue.component('NeonParagraph', {
		props: {
			text: {type: String, default:'Enter paragraph text'}
		},
		template: `<p>{{text}}</p>`,
	})

	Vue.component('NeonRoot', {
		props: {
			children: {type: Array, default:function(){return [];}}
		},
		template: `<NeonCmpList :children="children" ></NeonCmpList>`,
		methods: {
			save(){
				var children = this.$children.map(child => child.save()).join('\n');
				return `${indent(this)}<NeonRoot>\n${children}\n${indent(this)}</NeonRoot>`;
			}
		}
	})

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  Theme specific and need to be loaded:
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	Vue.component('NeonComeTalkToUsAgain', {
		template:`<NeonServerSideRender tag="section_come_talk_to_us"></NeonServerSideRender>`
	})


	Vue.component('cta', {
		template:`<NeonServerSideRender tag="cta" :params="$props"></NeonServerSideRender>`
	})

	Vue.component('in_depth', {
		template:`<NeonServerSideRender tag="in_depth" :params="$props"></NeonServerSideRender>`
	})

	Vue.component('hero_banner', {
		props: {
			title: {type:String, default: ''},
			subTitle: {type:String, default: ''},
		},
		template:`<div><NeonServerSideRender tag="hero_banner" :params="$props"></NeonServerSideRender></div>`
	})

	Vue.component('subscribe_form', {
		props: {},
		template:`<div><NeonServerSideRender tag="subscribe_form" :params="$props"></NeonServerSideRender></div>`
	})

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	Vue.component('NeonCmsCollection', {
		props: {
			info: {type:String, default: ''     },
			cols: {type:Number, default:3},
			// store override properties
			map: {type: Object, default: function() { return {} }},
			rows: {type: Array, default: function() { return [
				{title: 'my title'},
				{title: 'other'},
				{title: 'other'},
			]}}
		},
		template:`<div class="row">
 {{info}} <br>
 map:{{map}}
			<div v-for="row in rows" :class="column">
				{{row}}
				<NeonCmp v-for="uuid in childComponents" :key="uuid" :map-props="getMapProps(uuid, row)" :render="uuid" ></NeonCmp>
			</div>
<!--		<NeonCmp v-for="uuid in childComponents" :key="uuid" :render="uuid" ></NeonCmp>-->
			<NeonAdder :uuid="uuid"></NeonAdder>
		</div>`,
		methods: {
			getMapProps(uuid, row) {
				var mapProps = {};
				console.log(this.map, uuid, this.map[uuid], 'get map');
				var map = this.map[uuid];
				// map looks like:
				// [propName]: dataName
				// { "propKey": "dataKey" }
				// { "somethihng.title": "author.name" }
				// would create {something: {title: "value of row['author']['name']"}}
				console.log(uuid, 'UUID Map')
				console.log(map, 'MAP')
				if (map) {
					_.forEach(map, (rowKey, propKey) => {
						console.log('GET _.get(row, rowKey)', _.get(row, rowKey));
						_.set(mapProps, propKey, _.get(row, rowKey));
					});
				}
				console.log('MAP PROPS', mapProps);
				return mapProps;
			}
		},
		computed: {
			column() {
				return 'col-'+ (12/this.cols);
			},
			childComponents() {
				// get the child component uuids - children of cms collection are repeated
				let components =  this.$store.getters[GETTERS.getChildren](this.uuid);
				// loops through component and replace properties with maps.
				_.forEach(components, uuid => {
					if (this.map[uuid]) {

					}
				});
				console.log(components, 'components');


				return components;
			}
		}
	})

	Vue.component('NeonHeroText', {
		props: {
			title: {type:String, default: 'Enter a title'},
			bgTitle: {type:String, default: 'BG Title'},
			subtitle: {type:String, default: 'Enter a sub title if required'},
			body: {type:String, default: "A summary of the main building blocks of the newicon site"}
		},
		template: `<div><NeonServerSideRender tag="hero_text" :params="$props"></NeonServerSideRender></div>`
	});

	Vue.component('NeonServerSideRender', {
		props: {
			tag: '',
			params: {}
		},
		watch: {
			params: {
				// Will fire as soon as the component is created
				immediate: true,
				deep: true,
				handler() {
					this.update();
				}
			}
		},
		data() {
			return {template: 'Loading...'}
		},
		template:`<div v-html="template"></div>`,
		methods: {
			update() {
				var vm = this;
				$.ajax({
					method: "POST",
					url: neon.url('/cms/data/server-render'),
					data: {tag: this.tag, params: this.params},
					success: function (response) {
						vm.template = response;
					}
				});
			}
		}
	})

</script>

<style>
	[disabled] {opacity:0.5}
	.row {display: -ms-flexbox;display: flex;-ms-flex-wrap: wrap;flex-wrap: wrap;margin-right: 16px;margin-left: 16px;}
	.col {-ms-flex-preferred-size: 0;flex-basis: 0;-ms-flex-positive: 1;flex-grow: 1;max-width: 100%;padding-right: 16px;padding-left: 16px;}
	.niEditor_toolbar {position:absolute;}
</style>
