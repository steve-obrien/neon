<template>
	<component
		:is="componentIs"
		v-bind="component.props"
		:uuid="render"
		@click.exact.native="select($event)"
		@mouseover.native="onMouseOver($event)"
		:data-cmp="component.cmp"
		:data-uuid="render"
		:class="{ 'niEditor_selected': selected, 'niEditor_hovering': hovering }"
	></component>
</template>

<script>
	import {GETTERS} from "./store";
	import Vue from "vue";

	export default {
		name: "NeonCmp",
		comments: true,
		inject: ['editor'],
		props: {
			// the uuid of the component to render
			render: {type: String},
			// attemp to create a mapping for props to a data source for example
			mapProps: {type:Object, default: function() {return {};}},
		},
		computed: {
			componentIs: function() {
				console.log(this.component);
				var cmp = this.component;
				// const unknown = {
				// 	data: function() { return {cmp: cmp} },
				// 	template: `<div>Unknown component - {{cmp.cmp}} props: <pre>{{cmp.props}}</pre></div>`
				// };
				const unknown = {
					data: function() { return {cmp: cmp} },
					template: `<NeonServerSideRender :tag="cmp.cmp"></NeonServerSideRender>`
				};

				// normal operation
				if (Vue.options.components[this.component.cmp]) {
					return this.component.cmp;
				}
				return unknown;
			},
			selected: function() {
				return this.$store.getters[GETTERS.isSelected](this.render);
			},
			hovering: function() {
				return this.$store.getters[GETTERS.isHovering](this.render);
			},
			component() {
				var componentProps = this.$store.getters[GETTERS.getComponent](this.render);
				_.forEach(this.mapProps, (value, key) => {
					componentProps[key] = value;
				});
				return componentProps;
			},
		},
		methods: {
			select: function($event) {
				$event.stopPropagation();
				this.$store.commit('editor/select', {uuid:this.render})
			},
			onMouseOver: function($event) {
				$event.stopPropagation();
				if (this.editor.dragging) return;
				this.$store.commit('editor/hover', {uuid:this.render})
			}
		}
	}
</script>

<style>
	.niEditor_selected, .niEditor_hovering {
		outline: 1px solid #007AFF !important;
	}
</style>
