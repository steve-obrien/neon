
/* -------------------------------------------------------------------------------------------------------------
 * Fields List
 * -------------------------------------------------------------------------------------------------------------
 */
Vue.component('form-builder-form-fields', {
	template: `
		<div class="formBuilder_fields" ref="drop">
			<div v-if="form.fields.length==0" class="form-group"><p>Drag and drop fields here</p></div>
			<div v-for="field in form.fields" @click.native="select(field)"
				:is="widget(field)"
				v-bind="field"
				v-if="!(field.deleted == '1')"
				:key="field.name"
				:data-ref="field.name"
				:ref="field.name"
				class="formBuilder_field"
				:class="{ 'm_selected': isSelected(field) }">
				<div class="form-group_controls" >Error loading this widget. No component exists with the name: <code>{{widget(field)}}</code></div>
			</div>
		</div>
		`,
	props: {
		form: {
			type: Object,
			required: true
		},
	},
	mounted: function() {
		$(this.$refs.drop).sortable({
			helper: 'clone',
			axis: "y",
			forcePlaceholderSize: true,
			placeholder: 'component-drop-marker',
			stop: this.stop,
			start: function(e, ui) {
				ui.placeholder.height(ui.item.outerHeight());
			},
			over: function () {},
			receive: this.receive
		}).disableSelection();
	},
	methods: {
		select: function(field) {
			this.$store.commit('phoebe/setEditingField', {field: field, edit_part: 'label'});
		},
		isSelected: function (field) {
			return this.$store.state.phoebe.editingField.name === field.name;
		},
		widget: function(field) {
			return field.class.replace(/\\/g, '-').toLowerCase();
		},
		receive: function(event, ui) {
			var type = ui.item.attr('data-component');
			var $field = $('<div class="formBuilder_field .m_loading">Loading...</div>');
			var index = $(ui.helper).index();
			$(ui.helper).replaceWith($field);
			// could: go off and get the field html via ajax
			// Add a new field to the form
			this.$store.commit('phoebe/addField', {form: this.form, fieldType: type, at: index, forceEdit: true});
			$field.remove();
			var that = this;
			Vue.nextTick(function () {
				// DOM updated
				that.updateFieldOrder();
			})

		},
		/**
		 * Update function called after sorting occurs
		 * @param e
		 * @param ui
		 * @returns {boolean}
		 */
		stop: function(e, ui) {
			this.updateFieldOrder();
			return true;
		},
		updateFieldOrder: function() {
			// eww get the item from the DOM
			var that = this;
			$('.formBuilder_field').each(function(index, item){
				var name = $(item).attr('data-ref');
				var field = _.find(that.form.fields, function(item){
					return item.name == name;
				});
				// field.order = index;
				that.$store.commit('phoebe/updateFieldProps', {field: field, update: {order: index}});
			});
		}
	}
});