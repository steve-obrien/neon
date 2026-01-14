/**
 * Created by newicon on 09/05/2017.
 */

/**
 * ------------------------
 * <firefly-img>
 * ------------------------
 */
Vue.component('firefly-img', {
	props: {
		loadingSrc: { type: String },
		item : {type: Object}
	},
	data: function() {
		return {
			loaded: false
		}
	},
	template: `<img @dblclick="dbclick($event)" :src="src+'&w=250&h=250'" class="fireflyThumb_img" :class="{'neonLoadingImg': !loaded}" />`,
	computed: {
		src: function() {
			return neon.base() + '/firefly/file/img?id=' + this.item.id;
		}
	},
	methods:{
		dbclick() {
			// show image modal
			neon.modal.show(
				{
					props: {src:''},
					template:`
						<div style="position:relative;display:flex;width:100%;height:100%;">
							<img style="max-width:100%;max-height:100%;margin:auto;" :src="src" />
						</div>
					`,
				},
				{src: this.src,},
				{
					width: '90%',
					height:'90%',
					buttons: []
				}
			)
		}
	},
	mounted: function() {
		var vm = this;
		vm.loaded = false;
		vm.$el.onload = function() {
			vm.loaded = true;
		}
	}
});