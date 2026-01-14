Vue.component('form-builder-field-form-element-list-items',  Vue.extend({
	props: {
		field: Object,
		default: Object
	},
	template: `
			<div class="form-group">
				<label class="control-label" for="form-text">Options <button data-test-items-add type="button" @click="addOption()" class="btn btn-default" style="border-radius:100px;">+</button></label>
				<draggable v-model="choicesList" :options="dragOptions">
					<div class="input-group" v-for="(item, index) in choicesList" style="padding:5px 0;">
						<div class="dragGrip" style="display: table-cell;"></div>
						<input placeholder="Key, e.g GB" :value="item.key" @input="updateOptionKey($event, item, index)" type="text" name="options" class="form-control" style="width:50%">
						<input placeholder="Description, e.g Great Britain" :value="item.value" @input="updateOptionValue($event, item, index)" type="text" name="options" class="form-control" style="width:50%">
						<span :disabled="field.items.length == 1" @click="removeOption(item, index)" data-remove-btn="" class="btn btn-default input-group-addon" style="padding:8px 8px;">-</span>
						<span @click="addOption(item, index)" data-add-btn="" class="btn btn-default input-group-addon " style="padding:8px 8px;border-radius: 0 3px 3px 0;">+</span>
					</div>
				</draggable>
			</div>
		`,
	created(){
		if (!this.field.items) {
			this.$store.commit('phoebe/updateFieldProps', {field: this.field, update: {
				items: this.default
			}});
		}
	},
	computed: {
		dragOptions: function () {
			return  {
				animation: 100,
				//group: 'description',
				//disabled: !this.editable,
				ghostClass: 'ghost'
			};
		},
		/**
		 * gets the list of choices as an array of
		 * [{key:'the key', value:'the value}, ...]
		 */
		choicesList: {
			get: function() {
				let items = [];
				_.each(this.field.items, function(value, key) {
					items.push({key: key, value: value});
				});
				return items;
			},
			set: function(list) {
				this.$store.commit('phoebe/updateFieldProps', {field: this.field, update: {
					items: this.convertItemsArrayToObject(list)
				}});
			}
		}
	},
	methods: {
		addOption: function(item, index) {
			let items = this.choicesList;
			let newNumber = (items.length + 1);
			let newItem = { key: "Key " + newNumber, value: "Option " + newNumber };
			if (_.isUndefined(item) && _.isUndefined(index)) {
				items.unshift(newItem);
			} else {
				items.splice(index + 1, 0, newItem);
			}
			//'phoebe/updateFieldAddItem'
			this.$store.commit('phoebe/updateFieldProps', {field: this.field, update:{
				items: this.convertItemsArrayToObject(items)
			}});
		},
		removeOption:function(item, index) {
			let items = this.choicesList;
			if (items.length > 1) items.splice(index, 1);
			//'phoebe/updateFieldRemoveItem'
			this.$store.commit('phoebe/updateFieldProps', {field: this.field, update: {
				items: this.convertItemsArrayToObject(items)
			}});
		},
		/**
		 * Convert from array of objects to a single keyed object
		 * @param items
		 * @returns {Object}
		 */
		convertItemsArrayToObject: function(items) {
			let newItems = {}; _.each(items, item => {newItems[item.key] = item.value;});
			return newItems;
		},
		updateOptionKey: function (event, item, index) {
			let items = this.choicesList;
			items[index].key = event.target.value;
			// 'phoebe/updateFieldItemKey'
			this.$store.commit('phoebe/updateFieldProps', {field: this.field, update: {
				items: this.convertItemsArrayToObject(items)
			}});
		},
		updateOptionValue: function (event, item, index) {
			let items = this.choicesList;
			items[index].value = event.target.value;
			// 'phoebe/updateFieldItemValue'
			this.$store.commit('phoebe/updateFieldProps', {field: this.field, update: {
				items: this.convertItemsArrayToObject(items)
			}});
		}
	}
}));
