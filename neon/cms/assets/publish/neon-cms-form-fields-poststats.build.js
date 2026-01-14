Vue.component('neon-cms-form-fields-poststats', {
	inject: ['parentForm'],
	props: {
		linkedField: {type: String, default:undefined, editor: {label: 'The field name of the linked field'}}
	},
	render: function(h) {
		return h('div', {}, [
			this.linked
				?
				h('dl', {}, [h('dt', {}, ['Time to read']),
					h('dd', {}, [this.info.mins_to_read]),
					h('dt', {}, ['Word Count']),
					h('dd', {}, [this.info.word_count]),
				])
				:
				h('div', {class:"alert alert-warning"}, ['Link this field to a text field in order to display the word count and time to read information'])
		]);
	},
	computed: {
		linked: function() {
			return ! _.isUndefined(this.linkedField);
		},
		info: function() {
			var data = this.parentForm.getData();
			if (! this.linked) return;
			var value = data[this.linkedField];
			if (!value) return '';
			var words = value.replace(/(<([^>]+)>)/ig, "");
			var count = words.split(' ').length;
			var averageWordsPerMinute = 265;
			var minsToRead = Math.round(count / averageWordsPerMinute);
			return {mins_to_read: minsToRead, word_count: count}
		}
	}
});
