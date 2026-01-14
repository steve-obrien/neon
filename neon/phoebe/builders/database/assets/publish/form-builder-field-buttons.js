/**
 * -------------------------------------------------------------------------------------------------------------
 * Field Buttons
 * -------------------------------------------------------------------------------------------------------------
 */
Vue.component('form-builder-field-buttons', {
	template: `
		<div>
			<div v-for="(buttons, group) in groups">
				<div class="hr-text">{{group}}</div>
				<div v-for="btn in buttons" :title="btn.name" class="btn btn-default btn--component-icon" :data-component="btn.component">
					<i :class="btn.icon"></i> {{ btn.label }}
				</div>
			</div>
		</div>
	`,
	computed: {
		groups: function() {
			return _.groupBy(_.orderBy(neon.Store.state.phoebe.widgetComponents, 'order'), 'group');
		},
	}
});