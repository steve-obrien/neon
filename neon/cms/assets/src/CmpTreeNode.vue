<template>
	<li>
		<div :style="{paddingLeft: padding+'px'}" class="niNode" @mouseover="onMouseOver" @click="toggle"
		   :class="{isBranch: hasChildren, isLeaf: (!hasChildren), isSelected:isSelected, isHover: isHover, isOpen: isOpen, 'boxTitle': isPage, isPage: isPage}">
			<span class="niNode_caret"></span>
			<span class="niNode_icon" v-html="icon"></span>
			<span class="niNode_label">{{label}}</span>
		</div>
		<ul v-show="isOpen" v-if="hasChildren" class="tree">
			<CmpTreeNode
				v-for="cmp in children"
				:key="cmp"
				:cmp="cmp">
			</CmpTreeNode>
		</ul>
	</li>
</template>

<script>
import {GETTERS} from "./store";

export default {
	name: 'CmpTreeNode',
	props: {
		cmp: String
	},
	data: function () {
		return {
			depth: (this.$parent.depth || 0) + 1,
			isOpen: false,
			showProps: false
		}
	},
	methods: {
		toggle: function () {
			this.$store.commit('editor/select', {uuid: this.cmp});
			if (this.hasChildren) {
				this.isOpen = !this.isOpen
			}
		},
		onMouseOver: function() {
			this.$store.commit('editor/hover', {uuid: this.cmp})
		},
		expandParents: function() {
			if (this.$parent.$options.name === this.$options.name) {
				this.$parent.expandParents();
			}
			this.isOpen = true;
		}
	},
	created: function() {
		if (this.isSelected)
			this.expandParents();
	},
	watch: {
		isSelected: function (val) {
			if (val === true) {
				this.expandParents();
			}
		}
	},
	computed: {
		isPage: function() {
			return this.type === 'NeonPage';
		},
		padding: function() {
			return this.depth * 10;
		},
		isHover: function() {
			return this.$store.getters[GETTERS.isHovering](this.cmp)
		},
		isSelected: function() {
			return this.$store.getters[GETTERS.isSelected](this.cmp)
		},
		component: function() {
			return this.$store.getters[GETTERS.components][this.cmp];
		},
		componentConfig: function() {
			return this.$store.getters[GETTERS.getComponentConfig](this.type)
		},
		props: function() {
			return this.component;
		},
		children: function() {
			if (this.component) {
				return this.component.children;
			}
			return [];
		},
		type: function() {
			if (this.component) {
				return this.component.cmp;
			}
			return 'unknown';
		},
		hasChildren: function() {
			if (this.children === undefined)
				return false;
			return this.children.length;
		},
		label() {
			return _.get(this.componentConfig, 'title', this.type);
		},
		icon() {
			return _.get(this.componentConfig, 'icon', 'icon');
		}
	},
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style>
	li {text-align: left;}
	.tree .niNode {display:block; color:#333; white-space: nowrap; font-size:12px;line-height:27px;}
	.niNode_caret {margin-top:-2px;display: inline-block;width: 8px;height: 10px;vertical-align: middle;border-left: 8px solid #787979;border-top: 5px solid transparent;border-bottom: 5px solid transparent;margin-right:5px;}
	.niNode_icon {margin-top:-2px;display:inline-block;margin-right:5px;fill:#777; color:#777;}

	.niNode.isPage {margin-bottom:5px;height:30px;font-weight:bold;}
	.niNode.isHover {outline:1px solid #007AFF !important}
	.niNode.isSelected {background-color: #007AFF;color: #fff;}
	.niNode.isLeaf .niNode_caret{visibility:hidden;}
	.niNode.isOpen .niNode_caret {transform:rotate(90deg);}
	.niNode.isSelected .niNode_caret {border-left-color:#fff;}
	.niNode.isSelected .niNode_icon {color:#fff;}

</style>
