
Vue.component('form-builder-field-form-dynamic-map', Vue.extend({
	props: {
		field: Object
	},
	template: `
			<div>
				<div class="form-group" >
					<label class="control-label" for="placeholder">Data Map Provider</label>
					<neon-select v-model="dataMapProvider" :allow-clear="false">
						<option v-for="provider in providers" :value="provider.key">
							{{ provider.name }}
						</option>
					</neon-select>
					<neon-select v-model="dataMapKey" :allow-clear="false" :items="maps"></neon-select>
				</div>
			</div>
		`,
	computed: {
		dataMapProvider: {
			get: function(){
				return this.field.dataMapProvider;
			},
			set: function(value){
				// set the mapkey to the first one available so it always has a value
				var map = Object.keys(this.providers[value]['maps'])[0];
				this.$store.commit('phoebe/updateFieldProps', {field: this.field, update: {
					dataMapProvider:value,
					dataMapKey: map
				}});
			}
		},
		dataMapKey:{
			get: function(){
				return this.field.dataMapKey;
			},
			set: function(value){
				this.$store.commit('phoebe/updateFieldProps', {field: this.field, update: {dataMapKey:value}});
			}
		},
		providers: function() {
			return this.$store.state.phoebe.dds.data_map_info;
		},
		maps: function(){
			if (this.providers[this.field.dataMapProvider]) {
				var maps = this.providers[this.field.dataMapProvider]['maps'];
				return maps;
			}
			return [];
		},
	},
}));