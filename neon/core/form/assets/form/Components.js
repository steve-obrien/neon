/**
 * Created by steve on 27/01/2018.
 */
// region: switch
Vue.component('neon-switch-input', {
	props: {
		name: String,
		size: {type: String, default: ''},
		value: [String, Boolean, Number],
		trueLabel: {type: String, default: 'Yes'},
		falseLabel: {type: String, default: 'No'},
		readOnly: {type: Boolean, default: false},
		disabled: {type: Boolean, default: false}
	},
	template:`
		<label class="neonSwitch" :class="classes" role="switch">
			<input :name="name" type="hidden" value="0" />
			<input :name="name" type="checkbox" value="1" @change="update" :checked="isChecked" :readonly="readOnly" @keydown="detectEnter($event)">
			<div class="neonSwitch_slider _neonRound">
				<div class="neonSwitch_true">{{ trueLabel }}</div>
				<div class="neonSwitch_false">{{ falseLabel }}</div>
			</div>
		</label>
	`,
	computed: {
		isChecked() {
			return _.bool(this.value);
		},
		classes() {
			return {
				'_isChecked': this.isChecked
			};
		}
	},
	methods: {
		/**
		 * handles the enter key press when input is focused.
		 * We want the enter keypress to toggle the switch and NOT submit the form (if the input is focused)
		 * @param e
		 * @returns {boolean}
		 */
		detectEnter: function (e) {
			if (e.keyCode === 13) {
				e.preventDefault();
				this.$emit('input', this.isChecked ? false : true);
			}
		},
		update() {
			this.$emit('input', this.isChecked ? false : true);
		}
	}
});
// endregion

Vue.component('neon-input-date', {
	props: {
		name: String,
		placeholder: {type: String, default: 'DD/MM/YYYY'},
		value: String,
		datePickerFormat: String,
		tabindex: 0,
		readOnly: {type: Boolean, default: false},
		printOnly: {type: Boolean, default: false},
		yearRange: {type: String, default: 'c-10:c+10'}
	},
	template: `
		<div class="neonDate">
			<template v-if='!printOnly'>
				<input @input="updateValue()" autocomplete="false" type="text" style="width:100%" class="form-control" ref="datePicker" :placeholder="placeholder" :tabindex="tabindex" :readonly="readOnly" />
				<input :value="value" @input="$emit('input', $event.target.value)" type="hidden" style="width:100%" :name="name" class="form-control" ref="date" :placeholder="placeholder" :tabindex="tabindex" :readonly="readOnly" />
				<i @click="showDatePicker" class="fa fa-calendar neonDate_icon"></i>
			</template>
			<template v-else>{{value}}</template>
		</div>
	`,
	mounted: function () {
		var vm = this;
		$(vm.$refs.datePicker).datepicker({
			altField: this.$refs.date,
			altFormat: 'yy-mm-dd',
			buttonImageOnly: true,
			dateFormat: this.datePickerFormat,
			changeMonth: true,
			changeYear: true,
			firstDay: 1,
			yearRange: this.yearRange,
			beforeShow: function () { return !vm.readOnly; },
			onClose: function () {
				this.focus();
			},
			onSelect: function (value, other) {
				vm.updateValue();
			}
		});
		// set the initial value
		if (this.value)
			$(vm.$refs.datePicker).datepicker('setDate', $.datepicker.parseDate("yy-mm-dd", this.value));
	},
	beforeDestroy: function() {
		$(this.$refs.datePicker).datepicker('hide').datepicker('destroy');
	},
	methods: {
		updateValue: function(e) {
			var altFormat = $(this.$refs.datePicker).datepicker('option', 'altFormat');
			//get current date in user format
			var currentDate = $(this.$refs.datePicker).datepicker('getDate');
			var date = $.datepicker.formatDate(altFormat, currentDate);
			this.$emit('input', date);
		},
		showDatePicker: function () {
			$(this.$refs.datePicker).datepicker("show");
		}
	}
});

/**
 * neon-time-input
 */
Vue.component('neon-input-time', {
	props: {
		name: {type: String},
		value: String,
		fieldType: {
			type: String,
			default: 'select',
			validator: function (value) {
				return ['input', 'select'].indexOf(value) !== -1;
			}
		},
		/**
		 * selectSeconds - {Boolean} whether or not we want to show a seconds input
		 */
		showSeconds: {type: Boolean, default: false}
	},
	data: function () {
		return {
			hourOptions: ['00','01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23'],
			minuteOptions: ['00','01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31','32','33','34','35','36','37','38','39','40','41','42','43','44','45','46','47','48','49','50','51','52','53','54','55','56','57','58', '59']
		};
	},
	template:`
		<div class="form-inline">
			<!--<input :value="hours" ref="hours" @keyup="keyupHours($event.which)"  style="width:6em" maxlength="2" type="text" :id="id" class="form-control" :name="name+'[hh]'" placeholder="HH:MM" tabindex="0" /> :-->
			<template v-if="fieldType == 'select'">
				<neon-select ref="hours" v-model="hours" style="width:8em;display:inline-block;"  :name="name+'[hh]'" placeholder="HH">
					<option value=""  selected>HH</option>
					<option v-for="hour in hourOptions">{{hour}}</option>
				</neon-select>
				<neon-select ref="minutes" v-model="minutes" style="width:8em;display:inline-block;" :name="name+'[mm]'" placeholder="MM">
					<option value=""  selected>MM</option>
					<option v-for="min in minuteOptions">{{min}}</option>
				</neon-select>
				<template v-if="showSeconds">
					<neon-select ref="seconds" v-model="seconds" style="width:8em;display:inline-block;" :name="name+'[ss]'" placeholder="SS">
						<option value="" selected>SS</option>
						<option v-for="sec in minuteOptions">{{sec}}</option>
					</neon-select>
				</template>
			</template>
			<template v-if="fieldType == 'input'">
				<input v-model="hours" type="number" ref="hours" @keyup="keyupHours($event.which)"  style="width:4.5em" maxlength="2" class="form-control" :name="name+'[hh]'" placeholder="HH" tabindex="0" min="0" max="24" step="1"/> :
				<input v-model="minutes" type="number" ref="minutes" @keyup="keyupMinutes($event.which)" style="width:4.5em" maxlength="2" class="form-control" :name="name+'[mm]'" placeholder="MM" tabindex="0" min="0" max="59" step="1" />
				<template v-if="showSeconds">
					: <input v-model="seconds" ref="seconds" style="width:4.5em" maxlength="2" type="text" class="form-control" :name="name+'[ss]'" placeholder="SS" />
				</template>
			</template>
		</div>
	`,
	computed: {
		timeArray: function () { return (!_.isUndefined(this.value) && !_.isNull(this.value)) ? this.value.split(':') : []; },
		hours: {
			get: function () { return this.timeArray[0]; },
			set: function(value) {
				var time = value + ':' + this.minutes + ':' + this.seconds;
				this.$emit('input', time);
			}
		},
		minutes: {
			get: function () { return this.timeArray[1] || ''; },
			set: function(value) {
				var time = this.hours + ':' + value + ':' + this.seconds;
				this.$emit('input', time);
			}
		},
		seconds: {
			get: function () { return this.timeArray[2] || '00'; },
			set: function(value) {
				var time = this.hours + ':' + this.minutes + ':' + value;
				this.$emit('input', time);
			}
		}
	},
	methods: {
		update() {
			let value = (this.hours || (this.minutes ? '00:' + this.minutes : '')) + (this.minutes ?
					':' + this.minutes : '') + (this.seconds ? ':' + this.seconds : '');
			this.$emit('input', value);
		},
		/**
		 * Check an input box to determine if we can automatically re-adjust the focus for example once we have typed
		 * '08' the cursor jumps to the minutes box and so on.
		 * @param {string} currentBoxValue - the value of the input box
		 * @param {int} keyCode - the key code from the javascript events `which` property
		 * @returns {boolean}
		 */
		shouldFocusNextBox: function (currentBoxValue, keyCode) {
			var $keys = [48,49,50,51,52,53,54,55,56,57];
			// if we have entered two characters
			// and is a character from 0-9
			return (currentBoxValue.length === 2 && $keys.indexOf(keyCode) !== -1);
		},
		keyupHours: function(keyCode) {
			if (this.shouldFocusNextBox($(this.$refs.hours).val(), keyCode)) {
				$(this.$refs.minutes).focus();
			}
		},
		keyupMinutes: function(keyCode) {
			if (this.showSeconds && this.shouldFocusNextBox($(this.$refs.minutes).val(), keyCode)) {
				$(this.$refs.seconds).focus();
			}
		}
	}
});

Vue.component('neon-select-multiple', {
	props: {
		value: [String, Array, Object],
		name: String,
		multiple: {type: Boolean, default: true},
		disabled: {type: Boolean, default: false},
		items: {type: Object, default: function () { return {}; }},
		placeholder: String,
		readOnly: {type: Boolean, default: false},
		id: {type: String}
	},
	template: `<neon-selectize @focus="$emit('focus')" @blur="$emit('blur')" v-model="selected" v-bind="$props" :id="id"><slot/></neon-selectize>`,
	computed: {
		selected: {
			get() { return this.value; }, set(value) { this.$emit('input', value); }
		}
	}
});

Vue.component('neon-select-chain', Vue.extend({
	props: {
		id: {type: String},
		field: Object,
		name: String,
		inputName: String,
		endPoint: {
			type: String,
			default: ''
		},
		items: {type: [Object, Array], default: function () { return {}; }},
		classMemberMap: {
			type: Object,
			default: {}
		},
		chainValues : {
			type: Object,
			default: function() { return []; }
		}
	},
	data() {
		return {
			selectBoxItems: [],
			modelValue: '',
			selectedChainValues: []
		};
	},
	template: `
		<div>
			<div class="form-group" >
				<label class="control-label" for="placeholder">Chain Selector</label>
				<neon-select v-for="box, k in selectBoxItems" :key="'selectChain'+k" :items="selectBoxItems[k]" :value="selectedChainValues[k]" :load="load(k)" @input="onChange($event, k)"></neon-select>
				<input type="hidden" :name="inputName" :value="modelValue" :id="id" />
			</div>
		</div>
	`,
	mounted: function() {
		// Iterate through the object and assign values to the selectedChainValues array
		var chainValueCount = Object.keys(this.chainValues).length;
		if (chainValueCount) {
			for (var k in this.chainValues) {
				if (this.chainValues.hasOwnProperty(k) && !isNaN(Number(k))) {
					var cv = this.chainValues[k];
					// Set the selected value.
					this.selectedChainValues.push(Object.keys(cv)[0]);
					// Set the available choice items. In vue we need to splice in order to allow vue to register the
					// change. This is the equivalent of this.selectBoxItems[k] = cv
					this.selectBoxItems.splice(k, 0, cv);
				}
			}
			this.modelValue = this.selectedChainValues[chainValueCount-1];
		} else {
			// If no data has been given then we can ajax the a set of data to fill the choice items.
			this.updateItems(0, '');
		}
	},
	methods: {
		/**
		 * The load method is called whenever someone has typed into the select list. It is used to call
		 * an updateItems function which makes an ajax call for data matching the typed string.
		 * @param idx {number} The index of the select list (starting at 0).
		 * @returns {Function} The function that gets called when someone types in the select list.
		 */
		load: function(idx) {
			/**
			 * @param query {string} The string to be searched for in the database
			 */
			return (query) => {
				this.updateItems(idx, query);
			};
		},
		/**
		 * The onChange method is called whenever the value of a select box is changed.
		 * @param key {string}  The key of the key/value pair selected in the neon-select list.
		 * @param idx {number}  The index of the select list.
		 */
		onChange: function(key, idx) {
			// Set the selected key as the selectedChainValue for the index of the select list and also delete any
			// entries in the array with a higher index.
			this.selectedChainValues.splice(idx, this.selectedChainValues.length - idx, key);
			// We will need to be editing the selectBoxItems for the next select box
			var nextId = idx+1;
			if (key === undefined || key === '' || key === null) {
				// If we have emptied the search box then we want to clear all the selectBoxItems after this index and
				// make sure we don't have a value stored.
				this.selectBoxItems.splice(nextId, this.selectBoxItems.length - nextId);
				this.modelValue = null;
			}
			else if (Object.keys(this.classMemberMap).length <= nextId) {
				// If the nextId is larger than the max number of select lists that we have configured for, then we can
				// assume that the one we have changed is the last one and so we set the value.
				this.modelValue = key;
			} else {
				// Get the next set of items and make sure we don't have a value set (since we shouldn't be on the last
				// on at this point).
				this.updateItems(nextId, '');
				this.modelValue = null;
			}
		},
		/**
		 * Set the params in the endpoint string and make an ajax call to get the items for the select list at the index
		 * of idx.
		 * @param idx {number} The index of the select list
		 * @param query {string} The string to search the database for.
		 */
		updateItems(idx, query) {
			var params = this.classMemberMap['select'+idx];
			var ep = this.endPoint;
			var previousSelectValue = '';

			// Member value is equal to the key of the previous select list.
			// If this is the first index or the key is not a string then we skip it.
			if (idx > 0 && typeof this.selectedChainValues[idx - 1] === 'string') {
				previousSelectValue = this.selectedChainValues[idx - 1];
			}

			// Replace params in the endpoint string with the values we need.
			// Would be nice to make this loop through and find the {blah} params and search the classMap for those values
			ep = ep.replace(/{action}/g, params && params.action ? params.action : '');
			ep = ep.replace(/{class}/g, params && params.class ? params.class : '');
			ep = ep.replace(/{member}/g, params && params.member ? params.member : '');
			ep = ep.replace(/{value}/g, previousSelectValue);
			ep = ep.replace(/{filter}/g, (typeof query === 'string' && query.length > 0) ? query : '');

			// Go get the data
			$.getJSON(ep, {}, (data) => {
				if (typeof data === 'object' && !$.isEmptyObject(data)) {
					// Append the retrieved data to the array at the specified index.
					this.selectBoxItems.splice(idx, this.selectBoxItems.length - idx, data);
				} else {
					// If we dont get an object back, we want to unset the element at the index.
					this.selectBoxItems.splice(idx, this.selectBoxItems.length - idx);
				}
			});
		}
	}
}));

Vue.component('neon-select', {
	props: {
		value: [String, Number, Array, Object],
		name: String,
		multiple: {type: Boolean, default: false},
		disabled: {type: Boolean, default: false},
		items: {type: [Object, Array], default: function () { return {}; }},
		placeholder: String,
		readOnly: {type: Boolean, default: false},
		printOnly: {type: Boolean, default: false},
		allowClear: {type: Boolean, default: true},
		id: {type: String},
		/**
		 * A render function for the option or false to use the default option rendering function
		 * the function takes two params function(item, escape) - item being the option item object, and escape being
		 * an escape function to use on untrusted data
		 * https://github.com/selectize/selectize.js/blob/master/docs/usage.md#rendering
		 * function to render an option in the dropdown list of available options.
		 * must return a string or dom element representing a single node
		 */
		renderOption: {type: [Function,Boolean], default: false},
		/**
		 * An item the user has selected.
		 * must return a string or dom element representing a single node
		 */
		renderItem: {type: [Function,Boolean], default: false},
		/**
		 * Supply class string to be applied to the selectize dropdown
		 */
		classDropdown: {type: String, default: ''},
		/**
		 * Array list of fields keys in the items object to search
		 * https://github.com/selectize/selectize.js/blob/master/docs/usage.md
		 */
		searchField: {type: Array, default:function() {return ['label', 'value'];}},
		/**
		 * A load function of `function(query, callback)` where query is the current text entered as the dropdown search
		 * filter, and callback a the function to pass the returned dropdown items to.
		 */
		load: {type: [Function, Boolean], default: false},
		/**
		 * Whether new options can be created from the typed in input
		 */
		create: {type: [Function, Boolean], default: false},

		showAddButton: {type:Boolean, default: false},
	},
	data: function() {
		return { selectize: '' }
	},
	template: `
		<div :class="create?'selectize_hasAdd':''">
			<div v-if="printOnly">{{printSelected()}}</div>
			<neon-selectize @mounted="selectizeMounted" style="flex-grow:1" v-if="!printOnly" @focus="$emit('focus')" @blur="$emit('blur')" :class="classDropdown"  v-model="selected" v-bind="$props" :id="id"><slot/></neon-selectize>
			<button v-if="showAddButton && !printOnly && create" type="button" @click="addClicked" class="btn">
				<svg style="height:20px;width:20px;" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" ><path d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
			</button>
		</div>
	`,
	computed: {
		selected: {
			get() {
				if (this.multiple) {
					if (_.isArray(this.value))
						return this.value;
					if (_.isObject(this.value))
						return Object.values(this.value);
					return [];
				}
				return this.value;
			},
			set(value) { this.$emit('input', value); this.$emit('change', value);  }
		}
	},
	methods: {
		selectizeMounted: function(instance){
			// store the selectize instance
			this.selectize = instance;
			neon[this.id] = this;
		},
		addClicked() {
			this.selectize.createItem(' ');
		},
		printSelected: function() {
			var selected = [];
			if (_.isString(this.value)) {
				return this.items[this.value];
			}
			// is a multiple field
			var vm = this;
			_.each(this.value, function(key) { selected.push(vm.items[key]); });
			return selected.join(', ');
		}
	}
});

Vue.component('neon-selectize', {
	props: {
		value: {default: ''},
		name: String,
		multiple: {type: Boolean, default: false},
		settings: {type: Object, default: function () { return {}; }},
		disabled: {type: Boolean, default: false},
		items: {type: [Object, Array], default: function () { return {}; }},
		placeholder: String,
		readOnly: {type: Boolean, default: false},
		allowClear: {type: Boolean, default: true},
		id: {type: String},
		loadThrottle: {type: Number, default: 50},
		preload: {type: [String, Boolean], default: 'focus'},
		/**
		 * A render function for the option or false to use the default option rendering function
		 * the function takes two params function(item, escape) - item being the option item object, and escape being
		 * an escape function to use on untrusted data
		 */
		renderOption: {type: [Function,Boolean], default: false},
		/**
		 * An item the user has selected.
		 * must return a string or dom element representing a single node
		 */
		renderItem: {type: [Function,Boolean], default: false},
		/**
		 * Array list of fields keys in the items object to search
		 * https://github.com/selectize/selectize.js/blob/master/docs/usage.md
		 */
		searchField: {type: Array, default:function() {return ['label', 'value'];}},

		/**
		 * A load function of `function(query, callback)` where query is the current text entered as the dropdown search
		 * filter, and callback a the function to pass the returned dropdown items to.
		 */
		load: {type: [Function, Boolean], default: false},

		/**
		 * Whether new options can be created from the typed in input
		 */
		create: {type: [Function, Boolean], default: false}

	},
	watch: {
		items (value) {
			//this.$refs.select.selectize.clearOptions();
			this.$refs.select.selectize.addOption(this.getOptions());
			this.$refs.select.selectize.refreshOptions(false);
		},
		value (value, old) {
			this.setValue();
		},
		readOnly (value) {
			this.toggleReadOnly(value);
		},
		disabled (value) {
			this.toggleDisabled(value);
		},
		allowClear (value) {
			this.detachSelectize();
			this.attachSelectize();
		}
	},
	template: `
		<div>
			<!-- add empty input to manage the case where nothing is selected -->
			<input type='hidden' :name="multiple ? name+'[]' : name" value=''/>
			<select ref="select" :placeholder="placeholder" :multiple="multiple" :name="multiple ? name+'[]' : name" :id="id"><slot/></select>
		</div>
	`,
	mounted: function() {
		this.attachSelectize();
	},
	beforeDestroy () { this.detachSelectize(); },
	methods: {
		detachSelectize: function() {
			if (this.$refs.select.selectize) {
				this.$refs.select.selectize.destroy();
			}
		},
		attachSelectize: function() {
			var vm = this;
			var config = {
				options: this.getOptions(),
				optgroups: this.getOptGroups(),
				dropdownParent: 'body',
				optgroupField: 'optgroup',
				//create: false,
				labelField: 'label',
				copyClassesToDropdown: true,
				valueField: 'value',
				searchField: this.searchField,
				onInitialize: function() {
					vm.$nextTick(function() {
						vm.$refs.select.selectize.setValue(vm.value, true);
					});
				},
				onChange: function(value) {
					vm.$emit('input', value);
				},
				onBlur: function() {
					vm.$emit('blur', vm.value);
				},
				onFocus: function() {
					vm.$emit('focus');
				},
				render: {},
				plugins: [],
				loadThrottle: this.loadThrottle,
				preload: this.preload
			};

			if (this.renderOption) {
				config.render.option = this.renderOption;
			}
			if (this.renderItem) {
				config.render.item = this.renderItem;
			}
			// add a clear option when in single select mode
			if (this.allowClear  && !this.multiple)
				config.plugins.push("clear_button");
			else {
				config.plugins.push('remove_button');
			}
			/**
			 * If we can create options - combo box style
			 */
			if (this.create) {
				if (_.isFunction(this.create)) {
					config.create = this.create;
				} else {
					config.create = function(input) { return {value: input, label: input}; };
				}
			}
			// attach ajax load function @see selectize load
			if (this.load) {
				config.load = this.doLoad;
			}

			// initialize the Selectize control
			$(this.$refs.select).selectize(config);

			if (this.disabled) {
				this.$refs.select.selectize.disable();
			}
			if (this.readOnly) {
				this.$refs.select.selectize.lock();
			}

			// fire a mounted event so parent components can get access to the selectize instance
			this.$emit('mounted', this.$refs.select.selectize);

		},
		/**
		 * Calls the provided async ajax load function
		 */
		doLoad(query, callback) {
			var vm = this;
			this.load(query, function(data){
				callback(data);
				setTimeout(() => {
					vm.$refs.select.selectize.refreshOptions(true);
				}, 0);
			});
		},
		getReadOnlyValue: function(){
			if (!this.multiple)
				return this.items[this.value];
			var valueDisplay = [];
			_.each(this.value, (value) => {
				valueDisplay.push(this.items[value]);
			});
			return valueDisplay.join(', ');
		},
		getOptGroups: function() {
			var data = [];
			var optGroups = [];
			var vm = this;
			_.each(this.items, function(label, key) {
				// the items array had groups
				if (_.isObject(label)) {
					// the key is used as the label for the optgroup
					//vm.$refs.select.selectize.addOptionGroup(key, {label: key});
					optGroups.push({label: key, value: key});
					_.each(label, function(l, v) { data.push({label:l, value:v, optgroup: key}); })
				} else {
					data.push({label:label, value: key});
				}
			});
			return optGroups;
		},
		getOptions: function() {
			var data = [];
			var optGroups = [];
			var vm = this;
			_.each(this.items, function(label, key) {
				// the items array had groups
				if (_.isObject(label)) {
					data.push(label);
					// the key is used as the label for the optgroup
					//vm.$refs.select.selectize.addOptionGroup(key, {label: key});
					// optGroups.push({label: key, value: key});
					// _.each(label, function(l, v) { data.push({label:l, value:v, optgroup: key}); })
				} else {
					data.push({label:label, value: key});
				}
			});
			return data;
		},
		toggleDisabled (value) {
			if (value) {
				this.$refs.select.selectize.disable();
			} else {
				this.$refs.select.selectize.enable();
			}
		},
		toggleReadOnly (value) {
			if (value) {
				this.$refs.select.selectize.lock();
			} else {
				this.$refs.select.selectize.unlock();
			}
		},
		setValue () {
			var vm = this;
			this.$nextTick(function() {
				if (vm.$refs.select)
					vm.$refs.select.selectize.setValue(vm.value, true);
			});
		}
	}
});


Vue.component('neon-input-ide', {
	props:{
		value: {type: [String, Object], required:true, default: ''},
		lang: String,
		theme: String,
		height: true,
		width: true,
		options: {tpye: Object, default: function() {return {}; }}
	},
	data: function () {
		return {
			editor:null,
			contentBackup:""
		};
	},
	render: function (h) {
		var height = this.height ? this.px(this.height) : '100%';
		var width = this.width ? this.px(this.width) : '100%';
		return h('div',{
			attrs: {
				style: "height: " + height  + '; width: ' + width
			}
		});
	},
	methods: {
		px:function (n) {
			if( /^\d*$/.test(n) ){
				return n+"px";
			}
			return n;
		}
	},
	watch:{
		value:function (val) {
			if (this.contentBackup !== val) {
				this.editor.setValue(val,1);
				this.contentBackup = val;
			}
		},
		theme:function (newTheme) {
			this.editor.setTheme('ace/theme/'+newTheme);
		},
		lang:function (newLang) {
			this.editor.getSession().setMode('ace/mode/'+newLang);
		},
		options:function(newOption) {
			this.editor.setOptions(newOption);
		},
		height:function() {
			this.$nextTick(function() {
				this.editor.resize();
			});
		},
		width:function() {
			this.$nextTick(function(){
				this.editor.resize();
			});
		}
	},
	beforeDestroy: function() {
		this.editor.destroy();
		this.editor.container.remove();
	},
	mounted: function () {
		var vm = this;
		var lang = this.lang||'text';
		var theme = this.theme||'chrome';

		var editor = vm.editor = ace.edit(this.$el);
		this.$emit('init',editor);

		editor.$blockScrolling = Infinity;
		editor.getSession().setMode({path:"ace/mode/"+lang, inline:true});
		editor.setTheme('ace/theme/'+theme);
		editor.setValue(this.value,1);

		vm.editor.renderer.setScrollMargin(10, 10, 10, 10);
		var optionsProps = _.isString(this.options) ? JSON.parse(this.options) : this.options;
		var options = _.defaults({}, optionsProps, {maxLines: 30, minLines: 1, tabSize: 4, useSoftTabs: false});
		vm.editor.setOptions(options);

		this.contentBackup = this.value;

		editor.on('change',function () {
			var content = editor.getValue();
			vm.$emit('input',content);
			vm.contentBackup = content;
		});
	}
});

Vue.component('neon-input-json', {
	props:{
		value:{
			type:[String, Object],
			required:true
		},
		lang: String,
		theme: String,
		height: true,
		width: true,
		options: Object
	},
	template:`<neon-input-ide v-model="jsonValue" v-bind="$props" lang="json"></neon-input-ide>`,
	computed:{
		jsonValue: {
			get: function() {
				var v = this.value;
				if (!_.isObject(v) && _.isEmpty(v))
					return '';
				return JSON.stringify(v, null, "\t");
			},
			set: function(value) {
				try {
					this.$emit('input', _.isString(value)
						? (_.isEmpty(value) ? '' : JSON.parse(value))
						: value);
				} catch(err) {
					console.error('Json parse failed', err);
					return;
				}
			}
		}
	}
});

Selectize.define('clear_button', function (options) {
	/**
	 * Escapes a string for use within HTML.
	 *
	 * @param {string} str
	 * @returns {string}
	 */
	var escape_html = function (str) {
		return (str + '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	};

	options = $.extend({
		label: '&times;',
		title: 'Remove',
		className: 'selectizeClearAll',
		append: true,
		hideWhenEmpty: true,
		leaveOpen: false
	}, options);

	var self = this,
		$html = $('<span class="' +
			options.className +
			'" tabindex="-1" title="' +
			escape_html(options.title) +
			'">' +
			options.label +
			'</span>');


	this.setup = (function () {
		var original = self.setup;
		return function () {
			// override the item rendering method to add the button to each
			original.apply(this, arguments);

			this.$wrapper.append($html);

			if (options.hideWhenEmpty) {
				var $input = this.$input;
				var hideShowClrBtn = function ($inpt) {
					var val = $inpt.val();
					if (val) {
						$html.addClass('selectizeClearAllShow');
					} else {
						$html.removeClass('selectizeClearAllShow');
					}
				};

				hideShowClrBtn($input);
				$input.change(function () {
					hideShowClrBtn($input);
				});
			}

			// add event listener
			this.$wrapper.on('click', '.' + options.className, function (e) {
				e.preventDefault();
				if (self.isLocked) return;
				self.clear();
				if (options.leaveOpen) {
					self.$control_input.focus();
				}
			});
		};
	})();
});