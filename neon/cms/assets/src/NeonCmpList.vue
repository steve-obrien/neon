<template>
	<div>
		<NeonCmp v-for="uuid in childComponents" :key="uuid" :render="uuid" v-if="exists(uuid)"  ></NeonCmp>
		<NeonAdder :uuid="render"></NeonAdder>
	</div>
</template>

<script>
	//import _ from 'lodash';
	import Vue from 'vue';
	import {GETTERS, MUTATIONS} from "./store";

	/**
	 * A NeonCmpList is a component that allows a list NeonCmp's inside
	 * This component enables nesting and creating component trees
	 */
	export default {
		name: "NeonCmpList",
		comments: true,
		inject: {'editor': { default: false }},
		props:{
			render: '',
		},
		data() {
			return {componentMenu: false}
		},
		// components
		computed: {
			isEditMode() {
				return this.$store.getters[GETTERS.isEditMode];
			},
			childComponents() {
				return this.$store.getters[GETTERS.getChildren](this.render);
			},
		},
		methods: {
			exists(uuid) {
				var exists = !!this.$store.getters[GETTERS.components][uuid];
				if (!exists) {
					// remove child
					this.$store.commit('editor/removeChild', {parent:this.render, child: uuid});
					return false;
				}
				return true;
			},
		}
	}
</script>

<style>
	.niEditor_btnAdd {min-width:0;width:inherit;height:inherit;min-height:0;padding:0;display:none;}
	.niEditor_btnAdd  {display:block}
</style>
