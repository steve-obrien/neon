<template>
	<div>
		<div ref="selector"></div>
		<div ref="dragHandle" id="responsiveHandle" @mousedown="handleMouseDown" @dragstart="handleDragStart"></div>
	</div>
</template>

<script>
	export default {
		name: "StageSelector",
		data() {
			return {
				target: null,
				previewBounds: {}
			}
		},
		template: `
			<div>
				<div ref="selector"></div>
				<div ref="dragHandle" id="responsiveHandle" @mousedown="handleMouseDown" @dragstart="handleDragStart"></div>
			</div>
		`,
		watch: {
			target: function() {
				console.log('tagrrte changes');
			}
		},
		mounted() {
			// setTimeout(() => {
			// 	$($('#siteFrame')[0].contentWindow.document.body).on('mousemove', e => {
			// 		this.target = e.target;
			// 		var rect = e.target.getBoundingClientRect();
			// 		$(this.$refs.selector).css({left:rect.left, position:'absolute', right:rect.right, top:rect.top, width:rect.width+'px', height: rect.height+'px', border:'1px solid #0000ff'})
			// 		$('#stage_overlay')[0]._target = e.target
			// 		console.log($('#stage_overlay')[0]);
			// 		window.overlay = $('#stage_overlay')[0];
			// 	})
			// }, 1000);
		},
		computed: {
			styles() {
				console.log('get styles', this.target);
				if (_.isUndefined(this.target.getBoundingClientRect))
					return {};
				var rect = this.target.getBoundingClientRect();
				return {
					position: 'absolute',
					left: rect.left,
					right: rect.right,
					top: rect.top,
					width: rect.width+'px',
					height: rect.height+'px',
					border: '1px solid #0000ff'
				}
			}
		},
		methods: {
			handleMouseDown(event){
				$('#stage_overlay')[0].style.pointerEvents='all'
				this.previewBounds = this.$el.getBoundingClientRect();

				var vm = this;
				vm.$el.style.cursor = 'col-resize';

				const stageResize = _.throttle(function onMouseMove(event) {
					const currentWidth = vm.previewBounds.width;
					const newWidth = event.clientX - vm.previewBounds.x;
					const nWidth = (newWidth-currentWidth)*2;
					const w = currentWidth + nWidth;
					document.getElementById('stagePreview').style.width = w + 'px';
				},50);

				const mouseUp = function() {
					document.removeEventListener('mousemove', stageResize);
					document.removeEventListener('mouseup', mouseUp);
					$('#stage_overlay')[0].style.pointerEvents='none'
					vm.$el.style.cursor = '';
				};

				// handle global event listeners
				document.addEventListener('mousemove', stageResize);
				document.addEventListener('mouseup', mouseUp);
			},
			handleDragStart() {
				return false;
			}
		}
	}
</script>

<style scoped>

</style>
