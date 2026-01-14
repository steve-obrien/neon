import Vue from 'vue';

export const GETTERS = {
	isSelected: 'editor/isSelected',
	isHovering: 'editor/isHovering',
	getSelected: 'editor/getSelected',
	components: 'editor/components',
	getComponent: 'editor/getComponent',
	isEditMode: 'editor/isEditMode',
	getChildren: 'editor/getChildren',
	getAvailableComponents: 'editor/getAvailableComponents',
	getAvailableComponentsFor: 'editor/getAvailableComponentsFor',
	getComponentConfig: 'editor/getComponentConfig',
	rootComponent: 'editor/rootComponent',
};

export const MUTATIONS = {
	deleteComponents: 'editor/deleteComponents',
	setComponents: 'editor/setComponents',
	addComponent: 'editor/addComponent',
	setEditMode: 'editor/setEditMode',
	removeChild: 'editor/removeChild',
	updateComponentProps: 'editor/updateComponentProps',
};

const initialState = function() {
	return {

		pages: {},

		root: 'root',
		/**
		 * Indexed list of components
		 * indexed by components uuid
		 */
		components: {},

		componentTree: {},
		/**
		 * indexed list of selected components:
		 * ```js
		 * { "cDoeMj5S12Aprbcu45fDsw": true }
		 * ```
		 * {Object}
		 */
		selected: {},
		/**
		 * indexed list of hover components
		 * { "cDoeMj5S12Aprbcu45fDsw": true }
		 */
		hover: {},

		/**
		 * Whether we are in edit mode
		 * @type Boolean
		 */
		editMode: true,

		/**
		 * List of component configurations
		 * available for use with the editor
		 */
		availableComponents:  {
			'Root': {
				icon: '<svg width="24" height="24" data-icon="sitemap" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M128 352H32c-17.67 0-32 14.33-32 32v96c0 17.67 14.33 32 32 32h96c17.67 0 32-14.33 32-32v-96c0-17.67-14.33-32-32-32zm-24-80h192v48h48v-48h192v48h48v-57.59c0-21.17-17.23-38.41-38.41-38.41H344v-64h40c17.67 0 32-14.33 32-32V32c0-17.67-14.33-32-32-32H256c-17.67 0-32 14.33-32 32v96c0 17.67 14.33 32 32 32h40v64H94.41C73.23 224 56 241.23 56 262.41V320h48v-48zm264 80h-96c-17.67 0-32 14.33-32 32v96c0 17.67 14.33 32 32 32h96c17.67 0 32-14.33 32-32v-96c0-17.67-14.33-32-32-32zm240 0h-96c-17.67 0-32 14.33-32 32v96c0 17.67 14.33 32 32 32h96c17.67 0 32-14.33 32-32v-96c0-17.67-14.33-32-32-32z"></path></svg>'
			},
			// Layout
			"NeonSection": {
				category: 'layout'
			},
			'NeonContainer': {
				category: 'layout'
			},
			'NeonColumns': {
				title: 'Columns',
				icon: '<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true" focusable="false"><path fill="none" d="M0 0h24v24H0V0z"></path><g><path d="M21 4H3L2 5v14l1 1h18l1-1V5l-1-1zM8 18H4V6h4v12zm6 0h-4V6h4v12zm6 0h-4V6h4v12z"></path></g></svg>',
				category: 'layout',
				only:['NeonColumn']
			},
			'NeonColumn': {
				title: 'Column',
				icon: '<svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" role="img" aria-hidden="true" focusable="false"><path fill="none" d="M0 0h24v24H0V0z"></path><path d="M11.99 18.54l-7.37-5.73L3 14.07l9 7 9-7-1.63-1.27zM12 16l7.36-5.73L21 9l-9-7-9 7 1.63 1.27L12 16zm0-11.47L17.74 9 12 13.47 6.26 9 12 4.53z"></path></svg>',
				category: 'layout',
				parent: 'NeonColumns'
			},
			'NeonGrid': {
				category: 'layout',
			},
			// Basic
			'NeonCard': {
				category: 'basic',
			},
			'NeonComeTalkToUs': {
				category: 'basic',
			},
			'NeonHeroText': {
				category: 'basic'
			},
			'NeonComeTalkToUsAgain': {
				category: 'basic',
			},
			'NeonThing': {
				category: 'basic',
			},
			'NeonParagraph': {
				icon: '',
				category: 'basic',
			},
			'cta': {category: 'basic'},
			'hero_banner': {category: 'basic'},
			'in_depth': {category: 'basic'},
			'subscribe_form': {category: 'basic'},
			'NeonHeader': {
				icon: '<svg data-icon="index" aria-hidden="true" focusable="false" width="60" height="32" viewBox="0 0 60 32" class="bem-Svg" style="display: block; transform: translate(0px, 0px);"><path opacity=".4" d="M6.9 5.5H4V1.2H.4V13H4V9h2.9v4h3.7V1.2H6.9v4.3zm26.6-1.8c-.4-.2-.9-.2-1.4-.2-1.3 0-2.4.4-3.3 1.3-.5.5-.9 1.1-1.1 1.8-.1-1-.5-1.7-1.2-2.2-.7-.6-1.7-.9-2.8-.9-1.4 0-2.6.4-3.7 1.3l-.7.6.6.8.7 1-.2.1c-.2.1-.3.2-.4.3-.1-1.2-.5-2.2-1.4-3-.9-.8-2-1.2-3.2-1.2-1.3 0-2.4.4-3.3 1.3-1 .9-1.4 2.1-1.4 3.5 0 1.5.5 2.6 1.4 3.5.9.9 2 1.3 3.4 1.3 1.6 0 2.9-.5 3.9-1.6l.3-.3V11c.2.4.4.8.8 1 .7.6 1.6.9 2.6.9.5 0 1-.1 1.5-.2v.3H28v-2.9c-.1-.2-.1-.4-.1-.5.2.9.6 1.6 1.2 2.2.9.9 2 1.4 3.2 1.4.6 0 1.1-.1 1.5-.3v.1H37V.6h-3.6v3.1zm-14.4 6.4L19 10h.1v-.3c0 .2 0 .4.1.6 0 0-.1-.1-.1-.2zm4.2-.3c-.3 0-.5-.1-.6-.1.1 0 .3-.1.7-.1h.6c-.3.1-.5.2-.7.2zm9.8-.4c-.2.3-.5.4-.8.4-.4 0-.6-.1-.9-.4-.3-.3-.4-.6-.4-1 0-.5.1-.8.4-1.1.3-.3.5-.4.9-.4.3 0 .6.1.8.4.3.3.4.6.4 1.1 0 .4-.1.7-.4 1zM40.5 1c-.4-.4-.9-.6-1.4-.6-.5 0-1 .2-1.4.6-.4.4-.6.9-.6 1.4 0 .4.1.8.4 1.2h-.2V13h3.6V3.6h-.2c.3-.3.4-.7.4-1.2 0-.6-.2-1.1-.6-1.4zm15 2.6v.1c-.4-.2-.9-.2-1.4-.2-1.3 0-2.3.5-3.2 1.4-.5.5-.8 1.1-1 1.8-.1-.8-.5-1.5-1-2.1-.7-.7-1.6-1.1-2.7-1.1-.5 0-1 .1-1.5.3v-.2h-3.6V13h3.6V8c0-.6.2-.8.3-.8.2-.2.5-.3.7-.3.4 0 .7 0 .7 1.1v5H50V9.8c-.1-.2-.1-.3-.1-.5.2.8.5 1.5 1.1 2.1.1.2.3.3.5.4l-.3.5-.7 1.2-.5.8.7.6c1.1.8 2.3 1.3 3.7 1.3 1.4 0 2.5-.4 3.4-1.3.9-.9 1.4-2.1 1.4-3.7V3.6h-3.7zm-.3 5.3c-.2.2-.5.3-.8.3-.4 0-.6-.2-.8-.3-.2-.3-.3-.6-.3-.9 0-.4.1-.7.3-1 .2-.2.4-.3.7-.3.4 0 .6.1.8.3.2.3.3.6.3 1 .1.4 0 .7-.2.9z"></path><g fill="currentColor"><path d="M1.4 12V2.2H3v4.2h4.9V2.2h1.7V12H7.9V8H3v4H1.4z"></path><path d="M1.4 12V2.2H3v4.2h4.9V2.2h1.7V12H7.9V8H3v4H1.4zm17.5-3h-5.8c0 .5.3 1 .7 1.3.5.3 1 .5 1.6.5.9 0 1.6-.3 2.1-.9l.9 1c-.8.8-1.8 1.2-3.1 1.2-1 0-1.9-.3-2.7-1s-1.1-1.6-1.1-2.8c0-1.2.4-2.1 1.1-2.8.7-.7 1.6-1 2.6-1s1.9.3 2.6.9 1.1 1.5 1.1 2.5V9zm-5.8-1.3h4.3c0-.6-.2-1.1-.6-1.4-.4-.3-.9-.5-1.4-.5s-1.2.2-1.6.5c-.5.4-.7.8-.7 1.4z"></path><path d="M18.9 9h-5.8c0 .5.3 1 .7 1.3.5.3 1 .5 1.6.5.9 0 1.6-.3 2.1-.9l.9 1c-.8.8-1.8 1.2-3.1 1.2-1 0-1.9-.3-2.7-1s-1.1-1.6-1.1-2.8c0-1.2.4-2.1 1.1-2.8.7-.7 1.6-1 2.6-1s1.9.3 2.6.9 1.1 1.5 1.1 2.5V9zm-5.8-1.3h4.3c0-.6-.2-1.1-.6-1.4-.4-.3-.9-.5-1.4-.5s-1.2.2-1.6.5c-.5.4-.7.8-.7 1.4zM26.7 12h-1.4v-1c-.6.7-1.4 1.1-2.5 1.1-.8 0-1.4-.2-1.9-.7s-.8-1-.8-1.8.3-1.3.8-1.6c.5-.4 1.3-.5 2.2-.5h2v-.3c0-1-.6-1.5-1.7-1.5-.7 0-1.4.3-2.2.8l-.7-1c.9-.7 1.9-1.1 3.1-1.1.9 0 1.6.2 2.1.7s.8 1.1.8 2.1V12zm-1.6-2.8v-.6h-1.8c-1.1 0-1.7.4-1.7 1.1 0 .4.1.6.4.8.3.2.7.3 1.2.3s.9-.1 1.3-.4c.4-.3.6-.7.6-1.2z"></path><path d="M26.7 12h-1.4v-1c-.6.7-1.4 1.1-2.5 1.1-.8 0-1.4-.2-1.9-.7s-.8-1-.8-1.8.3-1.3.8-1.6c.5-.4 1.3-.5 2.2-.5h2v-.3c0-1-.6-1.5-1.7-1.5-.7 0-1.4.3-2.2.8l-.7-1c.9-.7 1.9-1.1 3.1-1.1.9 0 1.6.2 2.1.7s.8 1.1.8 2.1V12zm-1.6-2.8v-.6h-1.8c-1.1 0-1.7.4-1.7 1.1 0 .4.1.6.4.8.3.2.7.3 1.2.3s.9-.1 1.3-.4c.4-.3.6-.7.6-1.2zm4.4 1.8c-.7-.7-1-1.6-1-2.8s.4-2.1 1.1-2.8c.7-.7 1.6-1 2.6-1s1.8.4 2.4 1.3V1.6H36V12h-1.6v-1.1c-.6.8-1.4 1.2-2.5 1.2-.9 0-1.7-.3-2.4-1.1zm.5-2.7c0 .7.2 1.3.7 1.7.5.4 1 .7 1.6.7.6 0 1.1-.2 1.6-.7.4-.5.6-1 .6-1.7s-.2-1.3-.6-1.8c-.4-.5-1-.7-1.6-.7s-1.2.2-1.7.7c-.4.5-.6 1.1-.6 1.8z"></path><path d="M29.5 11c-.7-.7-1-1.6-1-2.8s.4-2.1 1.1-2.8c.7-.7 1.6-1 2.6-1s1.8.4 2.4 1.3V1.6H36V12h-1.6v-1.1c-.6.8-1.4 1.2-2.5 1.2-.9 0-1.7-.3-2.4-1.1zm.5-2.7c0 .7.2 1.3.7 1.7.5.4 1 .7 1.6.7.6 0 1.1-.2 1.6-.7.4-.5.6-1 .6-1.7s-.2-1.3-.6-1.8c-.4-.5-1-.7-1.6-.7s-1.2.2-1.7.7c-.4.5-.6 1.1-.6 1.8zm8.4-5.2c-.2-.2-.3-.4-.3-.7s.1-.5.3-.7c.2-.2.4-.3.7-.3s.5.1.7.3.3.4.3.7-.1.5-.3.7c-.2.2-.4.3-.7.3s-.5-.1-.7-.3zm1.5 8.9h-1.6V4.6h1.6V12z"></path><path d="M38.4 3.1c-.2-.2-.3-.4-.3-.7s.1-.5.3-.7c.2-.2.4-.3.7-.3s.5.1.7.3.3.4.3.7-.1.5-.3.7c-.2.2-.4.3-.7.3s-.5-.1-.7-.3zm1.5 8.9h-1.6V4.6h1.6V12zm3.8-4v4h-1.6V4.6h1.6V6c.3-.5.6-.8 1-1.1.4-.3.9-.4 1.4-.4.8 0 1.5.3 2 .8.6.4.9 1.2.9 2.1V12h-1.6V7.9c0-1.4-.6-2.1-1.7-2.1-.5 0-1 .2-1.4.5-.4.5-.6 1-.6 1.7z"></path><path d="M43.7 8v4h-1.6V4.6h1.6V6c.3-.5.6-.8 1-1.1.4-.3.9-.4 1.4-.4.8 0 1.5.3 2 .8.6.4.9 1.2.9 2.1V12h-1.6V7.9c0-1.4-.6-2.1-1.7-2.1-.5 0-1 .2-1.4.5-.4.5-.6 1-.6 1.7zm14.4-3.4V11c0 1.3-.4 2.3-1.1 3s-1.6 1-2.8 1-2.1-.3-3-1l.7-1.2c.7.6 1.5.8 2.2.8.7 0 1.3-.2 1.8-.6s.7-1 .7-1.8v-1c-.2.4-.6.8-1 1.1-.4.3-.9.4-1.5.4-1 0-1.8-.3-2.4-1-.6-.7-1-1.5-1-2.6 0-1 .3-1.9 1-2.6.6-.7 1.4-1 2.4-1s1.8.4 2.4 1.2V4.6h1.6zM52.2 8c0 .6.2 1.1.6 1.6s.9.7 1.5.7 1.2-.2 1.6-.6c.4-.4.6-1 .6-1.6 0-.6-.2-1.2-.6-1.6s-.9-.8-1.6-.8-1.1.2-1.5.7c-.4.4-.6 1-.6 1.6z"></path><path d="M58.1 4.6V11c0 1.3-.4 2.3-1.1 3s-1.6 1-2.8 1-2.1-.3-3-1l.7-1.2c.7.6 1.5.8 2.2.8.7 0 1.3-.2 1.8-.6s.7-1 .7-1.8v-1c-.2.4-.6.8-1 1.1-.4.3-.9.4-1.5.4-1 0-1.8-.3-2.4-1-.6-.7-1-1.5-1-2.6 0-1 .3-1.9 1-2.6.6-.7 1.4-1 2.4-1s1.8.4 2.4 1.2V4.6h1.6zM52.2 8c0 .6.2 1.1.6 1.6s.9.7 1.5.7 1.2-.2 1.6-.6c.4-.4.6-1 .6-1.6 0-.6-.2-1.2-.6-1.6s-.9-.8-1.6-.8-1.1.2-1.5.7c-.4.4-.6 1-.6 1.6z"></path></g><path fill="currentColor" d="M1 17v2h57v-2H1zm55 4H1v2h55v-2zM1 27h56v-2H1v2zm0 4h27v-2H1v2z" opacity=".25"></path></svg>',
			},
			'NeonCmsCollection': {
				icon: '<svg data-icon="index" aria-hidden="true" focusable="false" width="45" height="37" viewBox="0 0 45 37" class="bem-Svg" style="display: block; transform: translate(0px, 0px);"><path d="M42 1a2 2 0 0 1 1.12.34 2 2 0 0 1 .73.88A2 2 0 0 1 44 3v31a2 2 0 0 1-2 2H3a2 2 0 0 1-1.12-.34 2 2 0 0 1-.73-.88A2 2 0 0 1 1 34V3a2 2 0 0 1 2-2h39m0-1H3a3 3 0 0 0-3 3v31a3 3 0 0 0 .24 1.17 3 3 0 0 0 1.09 1.32A3 3 0 0 0 3 37h39a3 3 0 0 0 3-3V3a3 3 0 0 0-.24-1.17A3 3 0 0 0 43.68.51 3 3 0 0 0 42 0z" opacity=".4"></path><path d="M43.84 2.22a2 2 0 0 0-.73-.88A2 2 0 0 0 42 1H3a2 2 0 0 0-2 2v31a2 2 0 0 0 .16.78 2 2 0 0 0 .73.88A2 2 0 0 0 3 36h39a2 2 0 0 0 2-2V3a2 2 0 0 0-.16-.78zM3 3h39v9H3zm39 31H3v-9h39zm0-11H3v-9h39z" fill="currentColor"></path><path fill="currentColor" opacity=".2" d="M3 3h39v31H3z"></path></svg>',
			}
			// 'theme/button': {
			// 	loadFromServer: true,
			// 	category: 'basic',
			// },
		}

	}
};


const Store =  {
	namespaced: true,
	state: initialState(),
	mutations: {

		setPages: function(state, { pages }) {
			state.pages = pages;
		},

		setPage: function(state, {page}) {
			state.pages[page.id] = page;
		},

		updatePageAttr: function(state, { uuid, name, value }) {
			if (state.pages[uuid])
				Vue.set(state.pages[uuid], name, value);
		},

		updatePageTitle: function(state, { uuid, title }) {
			if (state.pages[uuid])
				state.pages[uuid].title = title;
		},

		updatePageDescription: function(state, { uuid, description }) {
			if (state.pages[uuid])
				state.pages[uuid].description = description;
		},

		deleteAll: function(state) {
			var uuids = Object.keys(state.components);
			uuids.forEach(function(uuid) {
				// we may just want to delete the child instead of the root definition
				// delete references
				// let children = state.components[state.components[uuid]._parent].children;
				// children.splice(children.indexOf(uuid), 1);
				Vue.delete(state.components, uuid);
			});
		},

		syncState: function(state, payload) {
			state.components = payload.components;
			state.selected = payload.selected;
			state.hover = payload.hover;
			state.editMode = payload.editMode;
		},

		setComponents: function(state, payload) {
			state.components = payload || {
				'root': {cmp: 'Root', children:[], props: {}},
			};
		},

		/**
		 *
		 * @param state
		 * @param componentName
		 * @param props
		 * @param children
		 * @param to
		 */
		addComponent: function(state, {componentName, props, children, parent}) {
			console.log(componentName, props, children, parent);
			var cmp = {}; //_.defaults(props, neon.editor.getCmpDefaults(componentName));
			cmp.cmp = componentName;
			cmp.uuid = neon.uuid64();
			cmp.children = children;
			cmp.props = _.cloneDeep(_.defaults(props, neon.editor.getCmpDefaults(componentName)));
			Vue.set(state.components, cmp.uuid, cmp);
			state.components[parent].children.push(cmp.uuid);
		},
		select: function(state, {uuid}) {
			// reset selected object to a single item
			state.selected = {};
			if (uuid)
				Vue.set(state.selected, uuid, true);
		},
		hover: function(state, {uuid}) {
			// reset selected object to a single item
			state.hover = uuid;
		},
		deleteComponents: function(state, {uuids}) {
			uuids = _.isString(uuids) ? [uuids]: uuids;
			uuids.forEach(function(uuid) {
				// we may just want to delete the child instead of the root definition
				// delete references
				// let children = state.components[state.components[uuid]._parent].children;
				// children.splice(children.indexOf(uuid), 1);
				Vue.delete(state.components, uuid);
			});
		},
		setEditMode: function(state, mode) {
			state.editMode = mode;
		},
		/**
		 * Remove a child from a parent
		 */
		removeChild: function(state, {parent, child}) {
			let children = state.components[parent].children;
			let indexOfChild = children.indexOf(child);
			if (indexOfChild !== -1)
				children.splice(indexOfChild)
		},

		updateComponentProps: function(state, {uuid, props}) {
			// ignore uuid fields
			delete props.uuid;
			Object.keys(props).forEach(function(key) {
				Vue.set(state.components[uuid], key, props[key]);
			});
		}
	},
	getters: {
		pages: state => { return state.pages; },
		components: state => { return state.components; },
		rootComponent: state => { return state.root; },
		getComponent: state => uuid => { return state.components[uuid]; },
		isEditMode: state => { return state.editMode; },
		getSelected: state => { return Object.keys(state.selected); },
		isSelected: state => uuid => { return state.selected[uuid] === true; },
		isHovering: state => uuid => { return state.hover === uuid; },
		getChildren: state => uuid => {
			// for Neill:
			// return state.componentsTree[uuid].children
			return state.components[uuid].children;
		},
		getAvailableComponents: state => {return state.availableComponents;},
		/**
		 * Get available components that can be added to the component specified by the uuid
		 * @param state
		 */
		getAvailableComponentsFor: (state, getters) => uuid => {
			const cmpConfigList = getters.getAvailableComponents;
			const components = getters.components;
			// get parent component config
			const parent = components[uuid] || {};
			const parentConfig = cmpConfigList[parent.cmp] || {};
			// if the parent component defines an only - then we should only return the specified components allowed
			if (!_.isUndefined(parentConfig.only)) {
				return _.pick(cmpConfigList, parentConfig.only);
			}
			// return all components except those that do not match an explicitly stated parent
			return _.pickBy(cmpConfigList, c => {
				// return all components with any parent allowed
				if (_.isUndefined(c.parent) || _.isEmpty(c.parent) || c.parent === '*')
					return true;
				return c.parent === parentConfig.cmp;
			});
		},

		/**
		 * @typedef ComponentConfig
		 * @property {String} name - Unique component name
		 * @property {String} title - Display title for this component
		 * @property {String} description - A component description - this will be shown in the component inspector sidebar
		 * @property {String} icon - This can be a string class name or an svg element string
		 * @property {String} category - The component category
		 * @property {Array} keywords - Comma separated list of keywords relevant to the component
		 * @property {Array} parent - Array of parent components, when specified this component will only be available
		 *                            as a direct child
		 */

		/**
		 * Get the component configuration information - this is NOT the component props.
		 * the config includes the props plus information about the component such as
		 *
		 * @param state
		 * @returns {function(type): ComponentConfig}
		 */
		getComponentConfig: state => type => {
			return state.availableComponents[type]
		}
	},
	actions: {
		updatePage: function(context, {page, redirect}) {
			return new Promise((resolve, reject) => {
				let url = neon.url('/cms/editor/page-update');
				$.ajax({
					type: "POST",
					url: url,
					dataType: 'json',
					data: {"CmsPage": page, redirect: redirect}
				}).done(function (result) {
					context.commit('setPage', {page: result});
					resolve(result)
				}).fail(function (response) {
					console.error('error', response);
					reject(response);
				});
			})
		},
	}
};

neon.Store.registerModule('editor', Store);

export default neon.Store
