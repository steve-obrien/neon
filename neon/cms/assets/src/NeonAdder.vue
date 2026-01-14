<template>
	<div :style="style">
		<a class="btn niEditor_btnAdd" v-if="isEditMode" ref="cmpInsertButton" @click="showComponentMenuToggle"><svg aria-hidden="true" role="img" focusable="false" class="dashicon dashicons-insert" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path d="M10 1c-5 0-9 4-9 9s4 9 9 9 9-4 9-9-4-9-9-9zm0 16c-3.9 0-7-3.1-7-7s3.1-7 7-7 7 3.1 7 7-3.1 7-7 7zm1-11H9v3H6v2h3v3h2v-3h3V9h-3V6z"></path></svg></a>
		<div v-if="isEditMode" ref="cmpMenuPop" v-show="componentMenu">
			<a class="btn btn-default" v-for="(cmpConfig, cmpName) in componentConfig" :key="cmpName" @click="addComponent(cmpName)">
				{{cmpName}} <span v-html="cmpConfig.icon"></span>
			</a>
		</div>
	</div>
</template>

<script>
	import {GETTERS, MUTATIONS} from "./store";

	export default {
		name: "NeonAdder",
		data() {
			return {
				componentMenu: false,
				style: {
					top:'-10px', left:'-10px'
				}
			}
		},
		mounted() {
			// var bounds = this.$parent.$el.getBoundingClientRect();
			// var left = bounds.width / 2 + bounds.left;
			// var top = bounds.top;
			// this.style.position = 'absolute';
			// this.style.top = top+'px';
			// this.style.left = left+'px';
			// console.log(this.$parent.$el.getBoundingClientRect());
		},
		props:{
			/**
			 * The component uuid that this adder will add components to
			 */
			uuid: {type:String, required:true},
		},
		computed: {
			isEditMode() {
				return this.$store.getters[GETTERS.isEditMode];
			},
			componentConfig() {
				return this.$store.getters[GETTERS.getAvailableComponentsFor](this.uuid);
			},
		},
		methods: {
			showComponentMenuToggle() {
				this.componentMenu = !this.componentMenu;
			},
			addComponent(componentName, props={}, children=[]) {
				console.log(MUTATIONS.addComponent, {componentName:componentName, props:props, children:children, parent:this.uuid})
				this.$store.commit(MUTATIONS.addComponent, {componentName, props, children, parent:this.uuid});
				this.componentMenu = false;
			},
		}
	}
</script>

<style>
	.niEditor_btnAdd {min-width:0;width:inherit;height:inherit;min-height:0;padding:0;display:none;}
	.niEditor_btnAdd  {display:block}
</style>