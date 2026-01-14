Vue.component('form-builder-form-header', {
	props:{
		form: Object
	},
	data: function () {
		return {showJson: false}
	},
	template:`
		<div class="form-group form-group__header" :class="{ 'is-selected' : state.editingForm }">
			<div @click="select">
				<h3 class="form-group__title">{{ form.label ? form.label : 'Untitled Content' }} - <code>{{ form.name }}</code></h3>
				<div class="hint-block">{{ form.hint ? form.hint : 'This is a description of your content - click here to edit'}}</div>
				<div v-if="state.editingForm">
					<div class="form-group">
						<input :value="form.label" @input="updateLabel"> - <input :value="form.name" @input="updateName" v-ddsformat> <br>
						<textarea :value="form.hint" @input="updateHint"></textarea>
						<label>Set map field</label>
						<select name="mapField" @change="updateMapField($event.target.value)">
							<option>-- Select a map field --</option>
							<option id="field.name" v-for="field in availableMapFields" :selected="field.mapField">{{field.name}}</option>
						</select>
					</div>
				</div>
			</div>
			<button @click="showJson = !showJson" class="btn btn-default">{}</button>
			<span v-if="deletedFieldCount" ><span class="text-danger">({{ deletedFieldCount }})</span> deleted fields</span>
			<div v-if="showJson">
				<pre>{{form}}</pre>
			</div>
		</div>
	`,
	methods: {
		showJsonClick: function(){
			this.showJson = !this.showJson;
		},
		updateMapField: function(value) {
			this.$store.dispatch('phoebe/updateMapField', {mapField: value});
		},
		updateLabel: function(e) {
			this.form.label = e.target.value;
			this.$store.commit('phoebe/updateForm', {form: this.form});
		},
		updateName: function(e) {
			this.form.name = e.target.value;
			this.$store.commit('phoebe/updateForm', {form: this.form});
		},
		updateHint: function(e) {
			this.form.hint = e.target.value;
			this.$store.commit('phoebe/updateForm', {form: this.form});
		},
		select: function() {
			// this.$store.commit('phoebe/setEditingField', {field: this.form, edit_part: 'label'});
			this.$store.commit('phoebe/editForm');
		}
	},
	computed: {
		availableMapFields: function() {
			var fields = [];
			_.forEach(this.state.form.fields, function(item) {
				fields.push({name: item.name, mapField: item.mapField});
			});
			fields.push({name: '_uuid'});
			return fields;
		},
		deletedFieldCount: function() {
			return this.$store.getters['phoebe/deletedFields']().length;
		},
		state: function() {
			return this.$store.state.phoebe;
		},
		type: function() {
			return this.$store.state.phoebe.type;
		}
	}
});
