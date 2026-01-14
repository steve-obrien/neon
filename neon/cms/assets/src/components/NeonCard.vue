<template>
	<div class="card cardBackground" data-animation="zooming">
		<div class="card_background" :style="overlayStyle"></div>
		<a href="javascript:;" class="card_body">
			<div class="content_foot">
				<h6 class="text-white opacity-8">{{subTitle}} - {{uuid}}</h6>
				<h5 class="card_title">{{title}} selected {{selected}} - hovering  {{hovering}} </h5>
			</div>
		</a>
	</div>
</template>

<script>
	import {GETTERS} from "../store";

	export default {
		name: "NeonCard",
		comments: true,
		props: {
			uuid: {type:String},
			title: {type:String, default:'Some title'},
			subTitle: {type:String, default: 'Some subtitle'},
			imgSrc: {type: String, default: 'https://images.unsplash.com/photo-1568576157197-72cd17d47eed?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1999&q=80'}
		},
		computed: {
			selected: function() {
				return this.$store.getters[GETTERS.isSelected](this.uuid);
			},
			hovering: function() {
				return this.$store.getters[GETTERS.isHovering](this.uuid);
			},
			overlayStyle() {
				return {backgroundImage: 'url('+this.imgSrc+')'}
			}
		},
		methods: {
			save() {
				return this.$el.innerHTML
			}
		}
	}
</script>

<style>
	.card {margin-bottom: 30px;box-shadow: 0 15px 35px rgba(50,50,93,.1), 0 5px 15px rgba(0,0,0,.07);border: 0;}
	.cardBackground[data-animation=zooming] {transition: all .3s cubic-bezier(.2, 1, .22, 1);-webkit-transition: all .3s cubic-bezier(.2, 1, .22, 1);}
	.cardBackground {overflow: hidden;height: 530px;background-position: 50%;background-size: cover;text-align: center;}
	.card { position: relative; display: flex;flex-direction: column;min-width: 0;word-wrap: break-word;background-color: #fff;background-clip: border-box;border: .0625rem solid rgba(0,0,0,.05);border-radius: .5rem;}
	.cardBackground .card_background { background-position: 50%; background-size: cover;text-align: center;margin-bottom: 30px;width: 100%;height: 100%;position: absolute;border-radius: .5rem;backface-visibility: hidden;-webkit-backface-visibility: hidden;transition: all 1s cubic-bezier(.2,1,.22,1);-webkit-transition: all 1s cubic-bezier(.2,1,.22,1);}
	.cardBackground[data-animation=zooming]:hover { transform: scale(1.07);-webkit-transform: scale(1.07);z-index: 2;}
	.cardBackground[data-animation=true]:hover .card_background,
	.cardBackground[data-animation=zooming]:hover .card_background {transform: scale(1.1 );-webkit-transform: scale(1.1);}
	.cardBackground:after {position: absolute;top: 0;bottom: 0;left: 0;height: 100%;z-index: 1;width: 100%;display: block;content: "";background: rgba(0,0,0,.6);}
	.cardBackground[data-animation=zooming] .card_body {padding-bottom: 30px;margin: initial;height: 100%;}
	.cardBackground .card_body {position: relative;z-index: 2;min-height: 330px;padding-top: 60px;padding-bottom: 60px;}
	.card_body {flex: 1 1 auto;padding: 1.5rem;}
	.cardBackground .card_body .content_foot {position: absolute;bottom: 10px;text-align: left;color:white;}
	.card .card_title { margin-bottom: .5rem; }
</style>