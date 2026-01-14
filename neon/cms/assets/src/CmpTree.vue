<template>
	<ul class="tree isRoot" style="margin:0px;">
		<CmpTreeNode :cmp="cmp" :children="children"></CmpTreeNode>
	</ul>
</template>

<script>

import CmpTreeNode from './CmpTreeNode';

export default {
	name: 'CmpTree',
	components: {CmpTreeNode},
	watch: {

	},
	props: {
		cmp: String
	},
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style>
	.tree,
	.tree ul {margin:0;padding:0;list-style:none;position:relative;}

	.tree:before,
	.tree ul:before {content:"";display:block;width:0;position:absolute;top:0;bottom:0;left:0;
		/*border-left:1px solid #ccc;*/
	}

	.tree li {margin:0;padding:0;line-height:2em;position:relative;font-weight: 500;}

	.tree li:before {
		content:"";
		display:block;
		width:10px; /* same with indentation */
		height:0;
		/*border-top:1px solid #ccc;*/
		margin-top:-1px; /* border top width */
		position:absolute;
		top:1em; /* (line-height/2) */
		left:0;
	}

	/*.tree li:last-child:before {*/
		 /*!*background:var(--colorTreeBg); !* same with body background *!*!*/
		 /*!*height:auto;*!*/
		 /*!*top:1em; !* (line-height/2) *!*!*/
		 /*!*bottom:0;*!*/
	 /*}*/
	.tree.isRoot > li:before {
		border:none;
	}

</style>
