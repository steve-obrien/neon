/**
 * Created by newicon on 26/01/2018.
 */
(function() {


Vue.component('neon-core-form-fields-filters-file', Vue.component('neon-core-form-fields-text'));


// region: HtmlRaw
Vue.component('neon-core-form-fields-htmlraw', {
	extends: Vue.component('neon-core-form-fields-base'),
	props: {
		value: String
	},
	template: `
		<neon-core-form-field v-bind="fieldProps" class="neonFieldTextarea">
			<div v-if="isPrintOnly" v-nl2br="value"></div>
			<textarea v-else @focus="onFocus()" @blur="onBlur()"  v-model="modelValue" type="text" class="form-control" :name="inputName" :id="id" :placeholder="placeholder" :readonly="isReadOnly" :disabled="disabled" v-bind="attributes"></textarea>
		</neon-core-form-field>
	`
});
// endregion

// region: Masked Input
	Vue.component('neon-core-form-fields-maskedinput', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			value: String,
			delimiter: {type: String},
			prefix: {type: String},
			blocks: {type: [Object, Array], default: function() { return {}; }}
		},
		data: function () {
			 return { cleave : null};
		},
		methods: {
			update: function () {
				this.modelValue = this.cleave.getRawValue();
			}
		},
		template: `
			<neon-core-form-field v-bind="fieldProps"  class="neonFieldText">
				<div v-if="isPrintOnly">{{value}}</div>
				<input v-else @blur="validate()" type="text" class="form-control" :placeholder="placeholder" :readOnly="isReadOnly"
				:disabled="disabled" v-on:change="update()" :id="id" />
				<input type="hidden" :name="inputName" :value="modelValue" />
			</neon-core-form-field>
		`,
		mounted: function() {
			if (this.cleave)
				return;
			let blockValues = [];
			for (let key in this.blocks) {
				blockValues.push(this.blocks[key]);
			}
			this.cleave = new Cleave('#' + this.id, {delimiter: this.delimiter, blocks: blockValues, prefix: this.prefix});
		}
	});
// endregion

// region: url
	Vue.component('neon-core-form-fields-urllink', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			absolute: { type: Boolean, editor: {order:420, label:'Absolute Link'} }
		},
		template: `
			<neon-core-form-field v-bind="fieldProps" class="neonFieldUrl">
				<div :name="inputName" v-if="isPrintOnly"><a :href='value' target='_blank' noreferrer noopener>{{value}}</a></div>
				<input v-else @focus="onFocus()" @blur="onBlur()" v-model="modelValue" type="text" class="form-control" :name="inputName" :id="id" :placeholder="placeholder" :readOnly="isReadOnly" :disabled="disabled" v-bind="attributes" />
			</neon-core-form-field>
		`,
	});
// endregion

// region: Markdown
	Vue.component('neon-core-form-fields-markdown', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			value: {type: String, default: '' },
			editorOptions: {type: [Object, Array], editor: {name: "editorOptions", label: 'Ace editor config options', hint: 'An object defining various options for the ace editor: https://github.com/ajaxorg/ace/wiki/Configuring-Ace'}}
		},
		created: function() {
			if (_.isNull(this.value)) {
				this.modelValue = '';
			}
		},
		template: `
			<neon-core-form-field v-bind="fieldProps" class="neonFieldMarkdown">
				<div v-if="isPrintOnly"><neon-markdown :markdown="value"></neon-markdown></pre></div>
				<div v-else>
					<neon-input-ide v-model="modelValue" lang="markdown" :options="editorOptions"></neon-input-ide>
					<textarea :value="modelValue" class="hidden" :name="inputName" :id="id" :readonly="isReadOnly" :disabled="disabled"></textarea>
				</div>
			</neon-core-form-field>
		`,
	});
// endregion

// region: Json
	Vue.component('neon-core-form-fields-json', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			parseValue: {type:Boolean, default:false},
			value: [String, Object, Array, Number],
			editorOptions: {type: [Object, String, Array], editor: {name: "editorOptions", label: 'Ace editor config options', hint: 'An object defining various options for the ace editor: https://github.com/ajaxorg/ace/wiki/Configuring-Ace'}}
		},
		data: function() {
			return {
				editor: Object,
				contentBackup: ''
			}
		},
		template: `
			<neon-core-form-field v-bind="fieldProps" class="neonFieldJson">
				<div v-if="isPrintOnly"><pre>{{value}}</pre></div>
				<div v-else>
					<neon-input-ide v-model="jsonValue" lang="json" :options="editorOptions"></neon-input-ide>
					<textarea :value="jsonValue" class="hidden" :name="inputName" :id="id" :readonly="isReadOnly"></textarea>
				</div>
			</neon-core-form-field>
		`,
		mounted() {
			// atempt to parse value
			try {
				if (!_.isString(this.value)) return;
				var json = JSON.parse(this.value);
				this.jsonValue = json;
			} catch (error) {
				// we don't mind - the string value could not be valid json - however we still want to display it
			}
		},
		watch: {
			value: function(newVal) {
				this.jsonValue = newVal
			}
		},
		computed: {
			// this computed property converts the ide string representation back to json
			// and converts json back to string for use in the editor
			jsonValue: {
				get: function() {
					var v = this.modelValue;
					if (!_.isObject(v) && _.isEmpty(v))
						return '';
					if (_.isString(v))
						return v;
					if (_.isObject(v))
						return JSON.stringify(v, null, "\t");
					console.error('Unknown format');
				},
				set: function(value) {
					try {
						if (this.parseValue) {
							this.modelValue = _.isString(value)
								? (_.isEmpty(value) ? '' : JSON.parse(value))
								: value;
						} else {
							this.modelValue = value;
						}
					} catch(err) {
						console.error('Json parse failed', err);

					}
				}
			}
		}
	});

// endregion

// region: Ide
	// Vue.component('neon-core-form-fields-ide', {
	// 	extends: Vue.component('neon-core-form-fields-base'),
	// 	props: {
	// 		value: [String, Object, Array, Number],
	// 		editorOptions: {type: [Object, String, Array], editor: {name: "editorOptions", label: 'Ace editor config options', hint: 'An object defining various options for the ace editor: https://github.com/ajaxorg/ace/wiki/Configuring-Ace'}}
	// 	},
	// 	template: `
	// 		<neon-core-form-field v-bind="fieldProps" class="neonFieldIde">
	// 			<div v-if="isPrintOnly"><pre>{{modelValue}}</pre></div>
	// 			<div v-else>
	// 				<neon-input-ide v-model="modelValue" lang="json" :options="editorOptions"></neon-input-ide>
	// 				<div :id="id+'-editor'">{{modelValue}}</div>
	// 				<textarea :value="modelValue" class="hidden" :name="inputName" :id="id" :readonly="isReadOnly"></textarea>
	// 			</div>
	// 		</neon-core-form-field>
	// 	`,
	// 	// mounted: function() {
	// 	// 	let textarea = $("#"+this.id);
	// 	// 	let editor = ace.edit(this.id + '-editor');
	// 	// 	//editor.setTheme("ace/theme/monokai");
	// 	// 	editor.getSession().setMode("ace/mode/php");
	// 	// 	editor.getSession().setTabSize(4);
	// 	// 	editor.getSession().setLines
	// 	// 	editor.getSession().on('change', function () {
	// 	// 		textarea.val(editor.getSession().getValue());
	// 	// 	});
	// 	// 	editor.setOptions({
	// 	// 		autoScrollEditorIntoView: true,
	// 	// 		maxLines: 30,
	// 	// 		minLines: 1
	// 	// 	});
	// 	// 	editor.renderer.setScrollMargin(10, 10, 10, 10);
	// 	// }
	// });
// endregion

Vue.component('neon-core-form-fields-ide', {
	extends: Vue.component('neon-core-form-fields-base'),
	props: {
		parseValue: {type:Boolean, default:false},
		value: [String, Object, Array, Number],
		editorOptions: {type: [Object, String, Array], editor: {name: "editorOptions", label: 'Ace editor config options', hint: 'An object defining various options for the ace editor: https://github.com/ajaxorg/ace/wiki/Configuring-Ace'}}
	},
	data: function() {
		return {
			editor: Object,
			contentBackup: ''
		}
	},
	template: `
		<neon-core-form-field v-bind="fieldProps" class="neonFieldJson">
			<div v-if="isPrintOnly"><pre>{{value}}</pre></div>
			<div v-else>
				<neon-input-ide v-model="jsonValue" lang="html" :options="editorOptions"></neon-input-ide>
				<textarea :value="jsonValue" class="hidden" :name="inputName" :id="id" :readonly="isReadOnly"></textarea>
			</div>
		</neon-core-form-field>
	`,
	mounted() {
		// atempt to parse value
		try {
			if (!_.isString(this.value)) return;
			var json = JSON.parse(this.value);
			this.jsonValue = json;
		} catch (error) {
			// we don't mind - the string value could not be valid json - however we still want to display it
		}
	},
	watch: {
		value: function(newVal) {
			this.jsonValue = newVal
		}
	},
	computed: {
		// this computed property converts the ide string representation back to json
		// and converts json back to string for use in the editor
		jsonValue: {
			get: function() {
				var v = this.modelValue;
				if (!_.isObject(v) && _.isEmpty(v))
					return '';
				if (_.isString(v))
					return v;
				if (_.isObject(v))
					return JSON.stringify(v, null, "\t");
				console.error('Unknown format');
			},
			set: function(value) {
				try {
					if (this.parseValue) {
						this.modelValue = _.isString(value)
							? (_.isEmpty(value) ? '' : JSON.parse(value))
							: value;
					} else {
						this.modelValue = value;
					}
				} catch(err) {
					console.error('Json parse failed', err);

				}
			}
		}
	}
});



// region: Hidden
	Vue.component('neon-core-form-fields-hidden', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			value: String
		},
		template: `
			<input v-if="!isPrintOnly" v-model="modelValue" type="hidden" :name="inputName" :id="id" :readonly="isReadOnly" />
		`,
	});
// endregion

// region: Color
	Vue.component('neon-core-form-fields-color', {
		extends: Vue.component('neon-core-form-fields-base'),
		template: `
			<neon-core-form-field v-bind="fieldProps" class="neonFieldColor">
				<div v-if="isPrintOnly" :style="{backgroundColor: value, height:'1em', width:'5em;'}"></div>
				<input v-else @focus="onFocus()" @blur="onBlur()" v-model="modelValue" type="color" :name="inputName" :id="id" :placeholder="placeholder" :readonly="isReadOnly" />
			</neon-core-form-field>
		`,
	});
// endregion

// region: Switchbutton
	Vue.component('neon-core-form-fields-switchbutton', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			value: [Boolean, String, Number],
			trueLabel: String,
			falseLabel: String
		},
		template: `
			<neon-core-form-field v-bind="fieldProps" class="neonFieldSwitch">
				<div v-if="(isPrintOnly || isReadOnly)">
					<span v-if="value==true">{{trueLabel}}</span>
					<span v-else>{{falseLabel}}</span>
				</div>
				<neon-switch-input v-else v-model="modelValue" v-bind="$props" :name="inputName" :readOnly="isReadOnly"></neon-switch-input>
			</neon-core-form-field>
		`,
	});
// endregion

// region: SwitchMultipleState

	Vue.component('neon-core-form-fields-switchmultiplestate', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			items: {type: [Object,Array], default: function() {return {}}, editor:{component: 'form-builder-field-form-element-list-items'} },
		},
		template: `
			<neon-core-form-field v-bind="fieldProps" class="neonFieldSwitchMultipleState">
				<div v-if="isPrintOnly">
					{{ value }}
				</div>
				<div v-else class="neonFieldSwitchMultipleState_radio" v-for="(value, key) in items">
					<label class="neonFieldSwitchMultipleState_label">
						<input class="neonFieldSwitchMultipleState_radioInput" v-model="modelValue" :name="inputName" type="radio" :value="key" :disabled="isReadOnly || isPrintOnly" @change="validate"/>
						<span class="neonSwitchMultipleState_radioSpan">{{ value }}</span>
					</label>
				</div>
			</neon-core-form-field>
		`
	});

// endregion

// region: Image
	Vue.component('neon-core-form-fields-image', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			value: String,
			startPath: {type:String, editor: { "label": 'Browser Start Path', hint: 'The folder location the browser will open at'}, default: '/'},
			crop: {type:Boolean, default:false, editor: {"label": 'Aspect Ratio'}},
			cropWidth: {type:Number, default: 200, editor:{
					label: 'Width',
					hint: 'Set the aspect ratio, if the uploaded image does not fit the aspect ratio given by width x height the cropper will appear',
					showIf:  ['crop', '==', true]
				}},
			cropHeight: {type:Number, default: 200, editor:{ "label": 'Height', showIf: ['crop', '==', true]  }}
		},
		template: `
			<neon-core-form-field v-bind="$props" class="neonFieldImage">
				<firefly-form-image
					v-model="modelValue"
					:name="inputName"
					:startPath="startPath"
					:crop="crop"
					:crop-width="cropWidth"
					:crop-height="cropHeight"
					:read-only="isReadOnly">
				</firefly-form-image>
			</neon-core-form-field>
		`,
	});
// endregion

// region: Image
	Vue.component('neon-core-form-fields-image', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			value: String,
			startPath: {type: String, default: '/'},
			crop: {type:Boolean, default:false, editor: {"label": 'Aspect Ratio'}},
			cropWidth: {type:Number, default: 200, editor:{
				label: 'Width',
				hint: 'Set the aspect ratio, if the uploaded image does not fit the aspect ratio given by width x height the cropper will appear',
				showIf:  ['crop', '==', true]
			}},
			cropHeight: {type:Number, default: 200, editor:{ "label": 'Height', showIf: ['crop', '==', true]  }}
		},
		template: `
			<neon-core-form-field v-bind="$props" class="neonFieldImage">
				<firefly-form-image
					v-model="modelValue"
					:name="inputName"
					:startPath="startPath"
					:crop="crop"
					:crop-width="cropWidth"
					:crop-height="cropHeight"
					:read-only="isReadOnly"
					@on-error="onError($event)"
					@click="launchedPicker()">
				</firefly-form-image>
			</neon-core-form-field>
		`,
		methods: {
			launchedPicker: function() {
				this.setFlags({focused: true,  touched: true});
			},
			onError: function($event) {
				var errors = {};
				errors[$event.error.code] = $event.error.message;
				this.$store.commit('FORMS/UPDATE_FIELD', {
					path: this.getPath(),
					updates: {errors: errors}
				});
			}
		}
	});
// endregion

// region: Email
	Vue.component('neon-core-form-fields-email', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			value: String
		},
		template:`
			<neon-core-form-field v-bind="fieldProps" class="neonFieldEmail">
				<div v-if="isPrintOnly">{{value}}</div>
				<input v-else @focus="onFocus()" @blur="onBlur()" v-model="modelValue" type="text" :id="id" :name="inputName" class="form-control" :placeholder="placeholder" :readonly="isReadOnly" v-bind="attributes" />
			</neon-core-form-field>
		`
	});
// endregion

// region: Submit
	Vue.component('neon-core-form-fields-submit', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			/**
			 * The button label text shown to the user when the form is submitted and the button is disabled
			 */
			submittingLabel: {type:String, default:'Submitting...', editor: {"hint": 'The button label text shown to the user when the form is submitted and the button is disabled'}},
			value: {type: [String, Boolean]}
		},
		data: function (){
			return {loading:true};
		},
		template: `
			<neon-core-form-field v-if="!isPrintOnly" :inline="inline" class="neonFieldSubmit">
				<button :name="inputName" @click="modelValue=true" type="submit" :disabled="submitted"  class="neonButton btn btn-primary" :class="attributes.class" :id="id" v-bind="attributes">
					<!--<i class="el-icon-check" v-if="!submitted"></i>-->
					<i class="el-icon-loading" v-if="submitted"></i>
					<template v-if="submitted">{{submittingLabel}}</template>
					<template v-else><slot>{{label ? label : name}}</slot></template>
				</button>
				<span class="text-danger" v-show="hasErrors"> Please fix the issues with the form to submit</span>
			</neon-core-form-field>
		`,
		computed: {
			hasErrors: function() {
				var rootFormId = this.getPath().split('.')[0];
				return this.$store.getters['FORMS/hasErrors'](rootFormId);
			}
		}
	});
// endregion

// region: Checkbox
	Vue.component('neon-core-form-fields-checkbox', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			value: [String, Boolean, Number],
			checkboxLabel: {type: String, editor: {component: 'neon-core-form-fields-wysiwyg', config: 'simple'}}
		},
		template: `
			<neon-core-form-field v-bind="fieldProps" class="neonFieldCheckbox">
				<div class="checkbox">
					<input type="hidden" :name="inputName" value="0" />
					<label><input @change="validate()" v-model="modelValue" type="checkbox" :name="inputName" :id="id" :disabled="isReadOnly || isPrintOnly" /><span class="neonCheckbox_label" v-html="checkboxLabel"></span></label>
				</div>
			</neon-core-form-field>
		`,
	});
// endregion

// region link
	Vue.component('neon-core-form-fields-link',  {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			multiple: {type: Boolean, default: true},
			items: {type: [Object, Array], editor: false},
			// the value may be an array of uuids,
			// or an object of a serialised associative array - the object keys are ignored
			// can also receive an empty string for no data
			value: [Array, Object, String],
			/**
			 * The data map provider for e.g. "cms" or "user" this typically assumes a neon application
			 * accessible via neon($dataMapProvider) neon applications implement the IFormBuilderInterface
			 * which should probably be renamed to IDataProviderMap
			 */
			dataMapProvider: {type: String, editor:{component:'form-builder-field-form-dynamic-map'}},
			/**
			 * Specify the key
			 */
			dataMapKey: {type: String, editor: false},
			endpoint: {type: String, default: '/core/form/get-map-objects', editor: false},
			create: { type: Boolean, default:false }
		},
		data: function() {
			return {selectBoxItems: this.items };
		},
		template: `
			<neon-core-form-field v-bind="fieldProps" class="neonFieldLink">
				<neon-select :load="load" @focus="onFocus()" @blur="onBlur()" :name="inputName" :multiple="true" v-model="modelValue" :items="selectBoxItems" :placeholder="placeholder" :readOnly="isReadOnly" :printOnly="isPrintOnly" :create="create ? doCreate : false" :showAddButton="create" ></neon-select>
			</neon-core-form-field>
		`,
		watch: {
			dataMapKey: function (val) {
				if (val === undefined || val == null) {
					this.selectBoxItems = {};
				} else {
					this.modelValue = null;
					this.load('');
				}
			}
		},
		methods: {
			// if we can create then we return the function that does the creation -
			// this is in format asynchronous
			// https://github.com/selectize/selectize.js/blob/master/docs/usage.md - see create
			doCreate: function(input, callback) {
				let vm = this;
				let popFormModalId = neon.uuid64();
				let popFormId = popFormModalId+'PopForm';
				let savedNewItem = false;
				neon.modal.show(
					{
						props: {title:'', message:'', popFormId:''},
						template:`
						<div style="padding:10px">
							<h4 style="padding:10px;">{{form ? 'Add new ' + form.label : 'Loading...'}}</h4>
							<hr/>
							<div v-if="state=='loading'" style="position:absolute;left:50%;transform:translateX(-50%);top:45%;text-center;">
								<svg  width="44" height="44" viewBox="0 0 44 44" xmlns="http://www.w3.org/2000/svg" stroke="#000"><g fill="none" fill-rule="evenodd" stroke-width="2"><circle cx="22" cy="22" r="1"><animate attributeName="r" begin="0s" dur="1.8s" values="1; 20" calcMode="spline" keyTimes="0; 1" keySplines="0.165, 0.84, 0.44, 1" repeatCount="indefinite" /><animate attributeName="stroke-opacity" begin="0s" dur="1.8s" values="1; 0" calcMode="spline" keyTimes="0; 1" keySplines="0.3, 0.61, 0.355, 1" repeatCount="indefinite" /></circle><circle cx="22" cy="22" r="1">
									<animate attributeName="r" begin="-0.9s" dur="1.8s" values="1; 20" calcMode="spline" keyTimes="0; 1" keySplines="0.165, 0.84, 0.44, 1" repeatCount="indefinite" /><animate attributeName="stroke-opacity" begin="-0.9s" dur="1.8s" values="1; 0" calcMode="spline" keyTimes="0; 1" keySplines="0.3, 0.61, 0.355, 1" repeatCount="indefinite" /></circle></g>
								</svg>
								<div>Loading...</div>
							</div>
							<transition name="fade">
								<neon-core-form-form v-if="form" v-bind="form" @afterSubmit="afterSubmit" :id="popFormId" :enableAjaxSubmission="true" >
									<div slot="header"></div>
								</neon-core-form-form>
							</transition>
						</div>
						`,
						data: function() { return {form:null, state:'loading' } },
						methods: {
							afterSubmit: function(event) {
								if (event.success) {
									vm.selectBoxItems = event.response;
									let value = _.keys(event.response);
									let text = _.values(event.response);
									savedNewItem = true;
									vm.$nextTick(function() {
										callback({value: value, text: text});
									});
									neon.modal.hide(popFormModalId);
								}
							}
						},
						mounted() {
							this.enableAjaxSubmission = true;
							let modalVm = this;
							this.state='loading';
							$.ajax(neon.url('/daedalus/index/pop-form-definition', {'type':vm.dataMapKey})).then(function(data) {
								modalVm.state = 'loaded';
								modalVm.form = data;
							});
						}
					},
					{popFormId:popFormId},
					{
						name: popFormModalId,
						height:'80%',
						width:'80%',
						// This adds save and close modal buttons that look nice.
						// however we end up with duplicate save buttons as phoebe also adds a blue save button at the bottom
						// buttons: [
						// 	{title: 'Close'},
						// 	{title:'Save', handler: () => { neon.form.forms[popFormId].submit(); return false; } }
						// ]
					},
					{
						'before-close': function(event) {
							if (!savedNewItem) {
								// return the focus back to the select box and allow it to continue
								// if this is not called the select box will be in a permanent disabled state
								callback();
							}
						}
					}
				);
			},
			load: function(query, callback) {
				var url = neon.url(this.endpoint, {objectToken: this.rootForm.objectToken, formField: this.getPath(), query:query});
				$.getJSON(url, {}, (data) => {
					if (callback) callback(data);
					this.selectBoxItems = data;
				});
			}
		}
	});


// region: Selectdynamic
	Vue.component('neon-core-form-fields-selectdynamic', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			items: {type: [Object, Array], default: () => {return {};}},
			/**
			 * The data map provider for e.g. "cms" or "user" this typically assumes a neon application
			 * accessible via neon($dataMapProvider) neon applications implement the IFormBuilderInterface
			 * which should probably be renamed to IDataProviderMap
			 */
			dataMapProvider: {type: String, editor:{component:'form-builder-field-form-dynamic-map'}},
			/**
			 * Specify the key
			 */
			dataMapKey: {type: String, editor: false},
			endpoint: {type: String, default: '/core/form/get-map-objects'},
			create:  {type: Boolean, default: false},
			/** default 100 is a dumb default but ensures back compatibility */
			limit: {type: Number, default: 100}
		},
		data() {
			return {
				selectBoxItems: this.items
			};
		},
		template: `
			<neon-core-form-field v-bind="fieldProps" class="neonFieldSelectdynamic">
				<neon-select @focus="onFocus()" @blur="onBlur()" :id="id" v-model="modelValue" :load="load" :name="inputName" :items="selectBoxItems" :placeholder="placeholder" :readOnly="isReadOnly" :printOnly="isPrintOnly" :create="create ? doCreate : false" :showAddButton="create"></neon-select>
			</neon-core-form-field>
		`,
		methods: {
			// if we can create then we return the function that does the creation -
			// this is in format asynchronous
			// https://github.com/selectize/selectize.js/blob/master/docs/usage.md - see create
			doCreate: function(input, callback) {
				let vm = this;
				let popFormModalId = neon.uuid64();
				let popFormId = popFormModalId+'PopForm';
				let savedNewItem = false;
				neon.modal.show(
					{
						props: {title:'', message:'', popFormId:''},
						template:`
						<div style="padding:10px">
							<h4 style="padding:10px;">{{form ? 'Add new ' + form.label : 'Loading...'}}</h4>
							<hr/>
							<div v-if="state=='loading'" style="position:absolute;left:50%;transform:translateX(-50%);top:45%;text-center;">
								<svg  width="44" height="44" viewBox="0 0 44 44" xmlns="http://www.w3.org/2000/svg" stroke="#000"><g fill="none" fill-rule="evenodd" stroke-width="2"><circle cx="22" cy="22" r="1"><animate attributeName="r" begin="0s" dur="1.8s" values="1; 20" calcMode="spline" keyTimes="0; 1" keySplines="0.165, 0.84, 0.44, 1" repeatCount="indefinite" /><animate attributeName="stroke-opacity" begin="0s" dur="1.8s" values="1; 0" calcMode="spline" keyTimes="0; 1" keySplines="0.3, 0.61, 0.355, 1" repeatCount="indefinite" /></circle><circle cx="22" cy="22" r="1">
									<animate attributeName="r" begin="-0.9s" dur="1.8s" values="1; 20" calcMode="spline" keyTimes="0; 1" keySplines="0.165, 0.84, 0.44, 1" repeatCount="indefinite" /><animate attributeName="stroke-opacity" begin="-0.9s" dur="1.8s" values="1; 0" calcMode="spline" keyTimes="0; 1" keySplines="0.3, 0.61, 0.355, 1" repeatCount="indefinite" /></circle></g>
								</svg>
								<div>Loading...</div>
							</div>
							<transition name="fade">
								<neon-core-form-form v-if="form" v-bind="form" @afterSubmit="afterSubmit" :id="popFormId" :enableAjaxSubmission="true" >
									<div slot="header"></div>
								</neon-core-form-form>
							</transition>
						</div>
						`,
						data: function() { return {form:null, state:'loading' } },
						methods: {
							afterSubmit: function(event) {
								if (event.success) {
									vm.selectBoxItems = event.response;
									let value = _.keys(event.response);
									let text = _.values(event.response);
									savedNewItem = true;
									vm.$nextTick(function() {
										callback({value: value, text: text});
									});
									neon.modal.hide(popFormModalId);
								}
							}
						},
						mounted() {
							this.enableAjaxSubmission = true;
							let modalVm = this;
							this.state='loading';
							$.ajax(neon.url('/daedalus/index/pop-form-definition', {'type':vm.dataMapKey})).then(function(data) {
								modalVm.state = 'loaded';
								modalVm.form = data;
							});
						}
					},
					{popFormId:popFormId},
					{
						name: popFormModalId,
						height:'80%',
						width:'80%',
						// This adds save and close modal buttons that look nice.
						// however we end up with duplicate save buttons as phoebe also adds a blue save button at the bottom
						// buttons: [
						// 	{title: 'Close'},
						// 	{title:'Save', handler: () => { neon.form.forms[popFormId].submit(); return false; } }
						// ]
					},
					{
						'before-close': function(event) {
							if (!savedNewItem) {
								// return the focus back to the select box and allow it to continue
								// if this is not called the select box will be in a permanent disabled state
								callback();
							}
						}
					}
				);
			},
			load: function(query, callback) {
				var url = neon.url(this.endpoint, {objectToken: this.rootForm.objectToken, formField: this.getServerPath(), query:query, length: this.limit});
				$.getJSON(url, {}, (data) => {
					if (callback) callback(data);
					this.selectBoxItems = data;
				});
			}
		},
		watch: {
			dataMapKey: function(val) {
				if (val === undefined || val == null) {
					this.selectBoxItems = {};
				} else {
					this.modelValue = null;
					this.load('');
				}
			}
		}
	});
// endregion

	Vue.component('neon-core-form-fields-selectmultiple',  {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			multiple: true,
			// the value may be an array of uuids,
			// or an object of a serialised associative array - the object keys are ignored
			// can also receive an empty string for no data
			value: [Array, Object, String],
			items: {type: [Object, Array], default: function() {return {};}, editor:{component: 'form-builder-field-form-element-list-items'} },
			/**
			 * Whether new options can be created from the typed in input
			 */
			create: {type: [Function, Boolean], default: false}
		},
		template: `
			<neon-core-form-field v-bind="fieldProps" class="neonFieldSelectmultiple">
				<neon-select @focus="onFocus()" @blur="onBlur()" :name="inputName" v-model="modelValue" :multiple="true" :items="items" :placeholder="placeholder" :readOnly="isReadOnly" :create="create" :printOnly="isPrintOnly" ></neon-select>
			</neon-core-form-field>
		`
	});

// region radio
	Vue.component('neon-core-form-fields-radio', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			items: {type: [Object,Array], default: function() {return {
				'Key 1':'Option 1', 'Key 2':'Option 2','Key 3':'Option 3'
			};}, editor:{component: 'form-builder-field-form-element-list-items'} }
		},
		template: `
			<neon-core-form-field v-bind="fieldProps" class="neonFieldRadio">
				<div class="radio" v-for="(value, key) in items">
					<label>
						<input v-model="modelValue" :name="inputName" type="radio" :value="key" :disabled="isReadOnly || isPrintOnly" @change="validate"/>
						<span class="neonRadio_radioText">{{ value }}</span>
					</label>
				</div>
			</neon-core-form-field>
		`
	});
// endregion

// region: Selectpicker
	Vue.component('neon-core-form-fields-selectpicker', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			value: [Array, Object],
			items: {type: [Object, Array], default: () => {return {}}},
			dataMapProvider: {type: String, editor:{component:'form-builder-field-form-dynamic-map'}},
			dataMapKey: {type: String, editor: false},
			searchString: String,
			selectedItemsListLabel: String
		},
		created: function() {
			if (_.isObject(this.value)) {
				// When a definition is serialised arrays can get turned to objects.
				// normally this is preferable, a checklist however works only with arrays
				this.modelValue = Object.values(this.value);
			}
		},
		data: function() {
			return {
				search: ''
			};
		},
		template: `
			<neon-core-form-field v-bind="fieldProps">
				<div class="neonFieldSelectpicker">
					<neon-select style="display:none" :multiple="true" :id="id" v-model="modelValue" :name="inputName" :items="items" :placeholder="placeholder" :readOnly="true" :printOnly="isPrintOnly"></neon-select>
					<div class="neonFieldSelectpicker_unselectedItems">
						<input class="form-control" v-model="search" type="text" :visible="isReadOnly" v-if="modelValue" :placeholder="searchPlaceholder"/>
						<ul class="neonFieldSelectpicker_unselectedItemsList" v-show="_.keys(itemsMatchingSearch).length">
							<li class="neonFieldSelectpicker_unselectedItem" v-for="(value, key) in itemsMatchingSearch" v-on:click="selectItem(key)"> <span v-html="value"></span> <i class="fa fa-check"></i></li>
						</ul>
					</div>
					<div class="neonFieldSelectpicker_selectedItems">
						<div class="neonFieldSelectpicker_selectedItemsLabel" v-html="selectedItemsListLabel" />
						<ul class="neonFieldSelectpicker_selectedItemsList" v-show="_.keys(selectedItems).length">
							<li class="neonFieldSelectpicker_selectedItem" v-for="(value, key) in selectedItems" v-on:click="deselectItem(key)">{{ value }} <i class="fa fa-times"></i></li>
						</ul>
					</div>
				</div>
			</neon-core-form-field>
		`,
		computed: {

			// Map the keys from modelValue (which contains the selected item keys)
			//  and the items themselves (which contain the key => value) to get an
			//  array of active item values
			selectedItems: function() {
				let selectedItems = {};
				for (const key in this.modelValue) {
					const itemKey = this.modelValue[key];
					selectedItems[itemKey] = this.items[itemKey];
				}
				return selectedItems;
			},
			// Map the difference in keys between the current modelValue and the component items,
			//  and return the items matching only those keys
			unselectedItems: function() {
				let unselectedItems = {};
				const unselectedKeys = _.difference(Object.keys(this.items), this.modelValue);
				for (const key in unselectedKeys) {
					const itemKey = unselectedKeys[key];
					unselectedItems[itemKey] = this.items[itemKey];
				}
				return unselectedItems;
			},
			itemsMatchingSearch: function() {
				if (!this.search)
					return this.unselectedItems;
				let matches = {};
				for (const key in this.unselectedItems) {
					if (_.includes(_.lowerCase(this.unselectedItems[key]), _.lowerCase(this.search))) {
						const regex = new RegExp(this.search,"ig");
						matches[key] = _.replace(this.unselectedItems[key], regex, function(match) {
							// todo: potential xss
							return '<span class="neonBgHighlight" style="background-color:#ccc;">'+match+'</span>';
						});
					}
				}
				return matches;
			},
			searchPlaceholder: function() {
				if (this.placeholder)
					return this.placeholder;
				return "Search for "+_.lowerCase(this.dataMapKey);
			}
		},
		methods: {
			// Add the given item to the field value
			selectItem: function(item) {
				var items = _.clone(this.modelValue);
				items.push(item);
				this.modelValue = items;
			},
			// Remove the given item from the field value
			deselectItem: function(item) {
				let value = [...this.modelValue];
				_.remove(value, function(n) {
					return n === item;
				});
				this.modelValue = value;
			}
		}
	});
// endregion

// region: Checklist
	Vue.component('neon-core-form-fields-checklist', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			value: {type: [Array, Object], default: function() { return []; } },
			items: {type: [Object, Array], default: function() { return {} }, editor:{component: 'form-builder-field-form-element-list-items'} }
		},
		created: function() {
			if (_.isObject(this.value)) {
				// When a definition is serialised arrays can get turned to objects.
				// normally this is preferable, a checklist however works only with arrays
				this.$store.commit('FORMS/UPDATE_FIELD', {
					path: this.getPath(),
					updates: {value: Object.values(this.value)}
				});
			}
		},
		template: `
			<neon-core-form-field v-bind="fieldProps" class="neonFieldChecklist">
				<div class="checkbox" v-for="(value, key) in items">
					<label>
						<input v-model="modelValue" :name="inputName+'[]'" type="checkbox" :value="key" :disabled="isReadOnly || isPrintOnly"  /><span class="neonCheckbox_label">{{ value }}</span></label>
					</label>
				</div>
			</neon-core-form-field>
		`
	});
// endregion

// region Select
	Vue.component('neon-core-form-fields-select', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			value: {type: [String, Number]},
			items: {type: [Object,Array], default: function() {return {};}, editor:{component: 'form-builder-field-form-element-list-items'} },
			allowClear: {type: Boolean, default: true},
			create: {type: Boolean, default: false, editor: {hint: 'Allow users to create new options based on what they have typed'}}
		},
		template: `
			<neon-core-form-field v-bind="fieldProps" class="neonFieldSelect">
				<neon-select @focus="onFocus()" @blur="onBlur()" :name="inputName" :id="id" v-model="modelValue" :items="items" :placeholder="placeholder" :readOnly="isReadOnly" :printOnly="isPrintOnly" :allow-clear="allowClear" :create="create"></neon-select>
			</neon-core-form-field>
		`
	});
// endregion

// region SelectChain
	Vue.component('neon-core-form-fields-selectchain', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			chainValues: {
				type: [Object, Array],
				default: function() { return []; }
			},
			endPoint: {
				type: String,
				default: function() { return '';},
				editor: {
					component: 'neon-core-form-fields-text'
				}
			},
			classMemberMap: {
				type: Object,
				default: function() {
					return {};
				},
				editor: {
					component: 'neon-core-form-fields-json'
				}
			},
			allowClear: {type: Boolean, default: true}
		},
		template: `
			<neon-core-form-field v-bind="fieldProps" class="neonFieldSelect">
				<neon-select-chain v-bind="$props" @focus="onFocus()" @blur="onBlur()" :id="id" :placeholder="placeholder" :inputName="inputName" v-model="modelValue" :readOnly="isReadOnly" :printOnly="isPrintOnly" :allow-clear="allowClear"></neon-select-chain>
			</neon-core-form-field>
		`,
	});
// endregion

// region: AppFormClass Selector
	Vue.component('neon-phoebe-form-fields-appformclassselector', {
		extends: Vue.component('neon-core-form-fields-selectdynamic')
	});
// endregion

// region: AppFormObject Selector
	Vue.component('neon-phoebe-form-fields-appformobjectselector', {
		extends: Vue.component('neon-core-form-fields-selectdynamic')
	});
// endregion

// region: Pageselector
	Vue.component('neon-cms-form-fields-pageselector', {
		extends: Vue.component('neon-core-form-fields-selectdynamic'),
	});
// endregion

// region: Currency
	Vue.component('neon-core-form-fields-currency', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			value: [Number,String]
		},
		template: `
			<neon-core-form-field v-bind="fieldProps" class="neonFieldCurrency">
				<div v-if="isPrintOnly">{{value}}</div>
				<input v-else @focus="onFocus()" @blur="onBlur()" v-model="modelValue" :name="inputName" :id="id" type="number" step="0.01" class="form-control" :placeholder="placeholder" :readOnly="isReadOnly" v-bind="attributes" />
			</neon-core-form-field>
		`
	});
// endregion

// region: FileBrowser
	Vue.component('neon-core-form-fields-filebrowser', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			value: String,
			startPath: {type: String, editor: {label: 'Browser Start Path', hint: 'The folder location the browser will open at'}, default: '/'}
		},
		template: `
			<neon-core-form-field v-bind="fieldProps" class="neonFieldFilebrowser">
				<firefly-form-file-browser v-model="modelValue" :name="inputName" :id="id" :readOnly="isReadOnly || isPrintOnly" :startPath="startPath"></firefly-form-file-browser>
			</neon-core-form-field>
		`
	});
// endregion


// region: Uuid64
	Vue.component('neon-core-form-fields-uuid64', {
		extends:  Vue.component('neon-core-form-fields-base'),
		template: `
			<neon-core-form-field v-bind="fieldProps" class="neonFieldText">
				<div :name="inputName" v-if="isPrintOnly">{{value}}</div>
				<input v-else @focus="onFocus()" @blur="onBlur()" v-model="modelValue" type="text" class="form-control" :name="inputName" :id="id" :placeholder="placeholder" :readOnly="isReadOnly" :disabled="disabled" v-bind="attributes" />
			</neon-core-form-field>
		`,
	});
// endregion

// region: File Single
	Vue.component('neon-core-form-fields-file', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			value: {type: [String, Array, Object]},
			multiple: {type: Boolean, editor:false, default:false},
			startPath: {type: String},
			urlUpload: {type: String},
			urlDownload: {type: String},
			urlMeta: {type: String},
			uploading: {type: Boolean, editor: false}
		},
		template: `
			<neon-core-form-field v-bind="fieldProps" class="neonFieldFile">
				<firefly-form-input-file-upload @input="validate()" @uploading="onUploading" v-model="modelValue" :id="id" v-bind="$props" :name="inputName" :readOnly="isReadOnly" :printOnly="isPrintOnly" :multiple="multiple"></firefly-form-input-file-upload>
			</neon-core-form-field>
		`,
		mounted: function() {
			var vm = this;
			this.rootForm.onBeforeSubmit(function (event) {
				if (vm.uploading === true) {
					event.preventDefault();
					vm.$store.commit('FORMS/UPDATE_FIELD', {
						path: vm.getPath(),
						updates: {errors: {uploading: 'All uploads must be complete before you can submit the form' } }
					});
				}
			});
		},
		methods: {
			onUploading: function(value) {
				this.$store.commit('FORMS/UPDATE_FIELD', {
					path: this.getPath(),
					updates: {uploading: value}
				});
			}
		}
	});
// endregion
//
// region: File Multiple
	Vue.component('neon-core-form-fields-filemultiple', Vue.component('neon-core-form-fields-file'));
// endregion

// region: Integer
	Vue.component('neon-core-form-fields-integer', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			value: [String, Number]
		},
		template: `
			<neon-core-form-field v-bind="fieldProps" class="neonFieldInteger">
				<div v-if="isPrintOnly">{{value}}</div>
				<input v-else @focus="onFocus()" @blur="onBlur()" v-model="modelValue" :name="inputName" :id="id" type="number" class="form-control" :readOnly="isReadOnly" v-bind="attributes" />
			</neon-core-form-field>
		`
	});
// endregion

	/**
	 * neon-time-input
	 */
	Vue.component('neon-core-form-fields-time', Vue.component('neon-core-form-fields-base').extend({
		props: {
			value: String,
			fieldType: {
				type: String,
				default: 'input',
				validator: function (value) {
					return ['input', 'select',].indexOf(value) !== -1;
				},
				editor: {component:'neon-core-form-fields-radio', label:'Type', items:{input:'input', 'select':'select'}}
			},
			/**
			 * selectSeconds - {Boolean} whether or not we want to show a seconds input
			 */
			showSeconds: {type: Boolean, default: false},
			inline: {type: Boolean, default: false}
		},
		template:`
			<neon-core-form-field v-bind="fieldProps" :inline="inline" :labelFor="id+'-hh'"   class="neonFieldTime">
				<div v-if="isPrintOnly">{{value}}</div>
				<neon-input-time v-else v-model="modelValue" :name="inputName" :field-type="fieldType" :show-seconds="showSeconds" :readOnly="isReadOnly"></neon-input-time>
			</neon-core-form-field>
		`,
	}));

	/**
	 * @deprecated use neon-core-form-fields-date
	 */
	Vue.component('neon-core-form-fields-date', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			placeholder: {type: String, default: 'DD/MM/YYYY'},
			value: String,
			datePickerFormat: String,
			tabindex: 0,
			inline: {type: Boolean, default: () => false},
			yearRange: {type: String, default: 'c-10:c+10'}
		},
		template: `
			<neon-core-form-field v-bind="fieldProps" class="neonFieldDate">
				<div v-if="isPrintOnly">{{value}}</div>
				<neon-input-date v-else v-model="modelValue" @input="validate()" :id="id" :name="inputName" :datePickerFormat="datePickerFormat" :placeholder="placeholder" :tabindex="tabindex" :readOnly="isReadOnly" :yearRange="yearRange"></neon-input-date>
			</neon-core-form-field>
		`
	});

	Vue.component('neon-core-form-fields-daterange', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			value: {type: [Array, Object], default: function() { return {"from": "", "to": ""}; }},
			datePickerFormat: {type: String, default: 'dd/mm/yy'},
			yearRange: {type: String, default: 'c-10:c+10'}
		},
		created: function() {
			if (this.value === '') {
				this.modelValue = {"from": "", "to": ""};
			}
		},
		template: `
			<neon-core-form-field v-bind="fieldProps" class="neonFieldDaterange">
				<neon-input-date v-model="from" :id="id+'-from'" :name="inputName+'[from]'" :datePickerFormat="datePickerFormat" :placeholder="placeholder" :readOnly="isReadOnly" :yearRange="yearRange"></neon-input-date>
				<neon-input-date v-model="to" :id="id+'to'" :name="inputName+'[to]'" :datePickerFormat="datePickerFormat" :placeholder="placeholder" :readOnly="isReadOnly" :yearRange="yearRange"></neon-input-date>
			</neon-core-form-field>
		`,
		computed: {
			from: {
				set(value) {
					this.modelValue = {from: value, to: this.to};
				},
				get() {
					var d = this.modelValue;
					if (!_.isObject(d))
						return '';
					return this.modelValue['from'];
				}
			},
			to: {
				set(value) {
					this.modelValue = {from: this.from, to: value};
				},
				get() {
					var d = this.modelValue;
					if (!_.isObject(d))
						return '';
					return this.modelValue['to'];
				}
			}
		}
	});

	Vue.component('neon-core-form-fields-datetime', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			placeholder: {type: String, default: ''},
			value: String,
			datePickerFormat: String,
			yearRange: {type: String, default: 'c-10:c+10'},
			// options for the time input
			timeOptions: {
				type: Object,
				default: function () {
					return {
						showSeconds: false,
						fieldType: 'select'
					};
				},
				editor: {
					component: 'neon-core-form-form',
					fields: {
						showSeconds: {
							label: 'Show Seconds',
							class: 'neon\\core\\form\\fields\\SwitchButton',
							name: 'showSeconds'
						},
						fieldType: {
							label: 'Field Type',
							class: 'neon\\core\\form\\fields\\Select',
							name: 'fieldType',
							allowClear: false,
							items: {'input' : 'Input Box', 'select': 'Drop down'},
							value: 'select'
						}
					}
				}
			},
		},
		template: `
			<neon-core-form-field v-bind="fieldProps" :labelFor="id+'-date'" class="neonFieldDatetime" style="overflow:hidden">
				<div class="clearfix">
					<neon-input-date style="display:block;float:left;" class="mr-1" v-model="date" :id="id" :datePickerFormat="datePickerFormat" :placeholder="placeholder" :readOnly="isReadOnly" :yearRange="yearRange"></neon-input-date>
					<neon-input-time style="display:block;float:left;" v-model="time" v-bind="timeOptions" :readOnly="isReadOnly"></neon-input-time>
					<input style="display:none;" type="hidden" :name="inputName" :value="modelValue" />
				</div>
			</neon-core-form-field>
		`,
		computed: {
			time: {
				set(value) {
					this.modelValue = this.date + ' ' + value;
				},
				get() {
					if (!_.isString(this.modelValue))
						return '';
					var time = this.modelValue.split(' ')
					return time[1];
				}
			},
			date: {
				set(value) {
					this.modelValue = value + ' ' + this.time;
				},
				get() {
					if (!_.isString(this.modelValue))
						return '';
					var date = this.modelValue.split(' ');
					return date[0];
				}
			}
		}
	});

	/**
	 * Real Field
	 */
	var FieldReal = {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			decimal: { type: Number, default: 1 },
			value: { type:[Number,String] }
		},
		template: `
			<neon-core-form-field v-bind="fieldProps" class="neonFieldReal">
				<div v-if="isPrintOnly">{{value}}</div>
				<input v-else type="number" :min="options.min" :max="options.max" :step="options.step"
					:value="modelValue" @focus="onFocus()" @blur="setValue($event.target.value)"
					class="form-control" :name="inputName" :id="id" :placeholder="placeholder" :readOnly="isReadOnly" v-bind="attributes" />
			</neon-core-form-field>
		`,
		methods: {
			setValue(value) {
				this.modelValue = parseFloat(value);
				this.onBlur();
			}
		},
		computed: {
			options: function(){
				let options = {};
				options.max = Math.pow(10, (16-this.decimal));
				options.min = -1*options.max;
				options.step = (this.decimal >= 0) ? Math.pow(10, -1*this.decimal) : 1;
				return options;
			}
		}
	};
	Vue.component('neon-core-form-fields-real', FieldReal);
	Vue.component('neon-core-form-fields-integer', FieldReal);

	/**
	 * Display Text Field
	 */
	Vue.component('neon-core-form-fields-display-text', {
		props: {
			text: String,
		},
		template: `
			<div class="form-group neonFormDisplayText">
				<div class="neonFormDisplayText_text">{{text}}</div>
			</div>
		`,
	});

	Vue.component('neon-core-form-fields-heading', {
		props: {
			text: String,
		},
		template: `
			<div class="form-group neonFormHeading">
				<div class="neonFormHeading_content">
					<h2>{{text}}</h2>
				</div>
			</div>
		`
	});

	Vue.component('neon-core-form-fields-guidance', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			text: String,
			showIf: {type: [Array, Object]}
		},
		template: `
			<neon-core-form-field v-bind="fieldProps">
				<div class="neonFormGuidance form-group">
					<div class="neonFormGuidance_text"><neon-markdown :markdown="text"></neon-markdown></div>
				</div>
			</neon-core-form-field>
		`
	});

	Vue.component('neon-core-form-fields-description', {
		props: {
			text: String
		},
		template: `
			<div class="neonFormDescription form-group">
				<div class="neonFormDescription_text">{{text}}</div>
			</div>
		`
	});


	Vue.component('neon-core-form-fields-spacer', {
		props: {
			height: Number
		},
		template: `<div class="neonFormSpacer form-group" :style="{height:height+'px'}"></div>`
	});

	/**
	 * TODO - 20180828 NJ - Fix firefly image to allow for chrooted directories
	 * so that members can only access their files in the system. Then uncomment
	 * these in the wysiwyg. Also remove the simpleImageUpload plugin added as a temporary fix for Palladium
	 */
	Vue.component('neon-core-form-fields-wysiwyg', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			value: {type: String},
			/**
			 * The wysiwyg config options - If this is an object this is passed to Ckeditor's config
			 * If this is a string, then it assumes it is the name of a preconfigured config set.
			 * By default it returns the config string of 'default' - this is looked up in the components configSets.
			 * If the string config key is found in the configSets object it uses the returned object as the config object.
			 * If the key is not found then it will use the 'default' configSet option e.g. `this.configSets['default']`
			 */
			config: {
				type: String,
				default: 'default',
				editor: {
					order: 1,
					component: 'neon-core-form-fields-select',
					items: {'simple': 'Simple', 'default': 'Default'},
					allowClear: false,
					create: true,
					label: 'Toolbar Set',
					hint: 'Select the toolbar set to use. You can add a custom one which must exist in the provided config sets',
				}
			},

			useMediaBrowser: {
				type: Boolean,
				default: false,
				editor: {
					hint: 'Uses the firefly media browser when uploading files.',
				}
			},

			configSets: {
				type: [Object, Array, String],
				default: function() { return []; },
				editor: false
			}
		},
		watch: {
			isReadOnly: function(value){
				this.toggleReadOnly(value);
			},
			config: function(value) {
				if (CKEDITOR.instances[this.id]) {
					CKEDITOR.instances[this.id].destroy();
				}
				this.attachEditor();
			}
		},
		template: `
			<neon-core-form-field v-bind="$props" class="neonFieldWysiwyg">
				<div v-if="isPrintOnly" v-html="value"></div>
				<div v-else>
					<textarea ref="editor" :name="inputName" :value="value" :id="id" ></textarea>
				</div>
			</neon-core-form-field>
		`,
		computed: {
			resolvedConfig: function() {
				var configSets = this.configSets;
				if (_.isString(configSets))
					configSets = JSON.parse(configSets);
				if (_.isString(this.config) && _.isDefined(configSets[this.config]))
					return configSets[this.config];
				if (_.isDefined(configSets[this.config]))
					return configSets[this.config];
				return [];
			}
		},
		mounted: function() {
			window.wysiwyg = this;
			this.attachEditor();
		},
		methods: {
			attachEditor: function() {
				var config = this.resolvedConfig;
				config.readOnly = this.isReadOnly;
				var editor = $(this.$refs.editor).ckeditor(config).editor;
				if (typeof editor !== 'undefined') {
					editor.on('focus', (e) => {
						this.onFocus();
					});
					editor.on( 'blur', (e) => {
						this.onBlur();
					});
					editor.on('change', () => {
						let html = editor.getData();
						if (html !== this.value) {
							this.modelValue = html;
							this.$emit('input', html);
						}
					});
				}
			},
			toggleReadOnly: function(value){
				var editor = $(this.$refs.editor).ckeditor().editor;
				if (typeof editor !== 'undefined')
					editor.setReadOnly(value);
			}
		},
		beforeDestroy: function () {
			if (CKEDITOR.instances[this.id]) {
				CKEDITOR.instances[this.id].destroy();
			}
		}
	});

	Vue.component('neon-wysiwyg', Vue.component('neon-core-form-fields-wysiwyg'));


// region: User Selector
	Vue.component('neon-user-form-fields-userselector', {
		extends: Vue.component('neon-core-form-fields-selectdynamic')
	});
// endregion

// region: User Selector
	Vue.component('neon-user-form-fields-userselectormultiple', {
		extends: Vue.component('neon-core-form-fields-link'),
		props: {
			multiple: {type: Boolean, default: true, name:'boo'}
		}
	});
// endregion


	const MAP_STATES = {
		IDLE:'IDLE',
		ADDRESS_LOOKUP:'ADDRESS_LOOKUP',
		ADDRESS_LOOKUP_ERROR: 'ADDRESS_LOOKUP_ERROR'
	}
	Vue.component('neon-core-form-fields-locationmap', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			settingsApp: {type: String, editor: { hint: 'The app within neon responsible for the Google API key setting', required: true }},
			settingsKey: {type: String, default:"google_api", editor: { hint: 'The string key name that holds the Google API key value this calls `setting("app", "google_api")` the default key is `google_api`', required: true}},
			value: {type: Object, default:function() { return {lat:0,lng:0,address:''} }},
		},
		data: function() {
			return {
				// handle the core data value is an object containing lat, lng, address
				lat: this.value.lat,
				lng: this.value.lng,
				address: this.value.address,

				// internal state
				map: false,
				geocoder: false,
				status: MAP_STATES.IDLE,
				activeTab: 'coorodinates',
				addressSuggestions: []
			};
		},
		template: `
			<neon-core-form-field v-bind="fieldProps" class="neonFormLocationMap">

				<input :name="inputName+'[lat]'" :value="modelValue.lat" type="hidden" />
				<input :name="inputName+'[lng]'" :value="modelValue.lng" type="hidden" />
				<input :name="inputName+'[address]'" :value="modelValue.address" type="hidden" />

				<div ref="map" style="width: 100%; height: 400px;margin: 0 auto;background: gray;" class="google-map" ></div>
				<el-tabs v-model="activeTab" >
					<el-tab-pane label="Address" name="address">
						<div style="display:flex;align-items: center;">
							<div class="neonInput" style="width:100%;" :class="{'has-error': isAddressLookupError, 'isLoading': isAddressLoading}">
								<input name="inputName+'[address]" type="text" :value="address" @input="addressSearch($event)" placeholder="Type an address or click on the map" class="form-control">
								<div v-if="addressSuggestions.length" style="padding: 8px 0 6px 0; border: 1px solid #d8dde6;border-top: 0;border-radius: 0 0 3px 3px;margin-top: -2px;" >
									<a @click="setAddress(address)" class="btn btn-link" style="width:100%;text-align:left;outline:0" v-for="address in addressSuggestions">{{address.formatted_address}}</a>
								</div>
								<div v-if="status=='ADDRESS_LOOKUP_ERROR'" class="neonField_error help-block">
									<i class="fa exclamation"></i> No results found for <span style="color:black">{{address}}</span>. Please make sure that the address is spelled correctly.
								</div>
							</div>
							<a class="btn" @click="clear">Clear</a>
						</div>
					</el-tab-pane>
					<el-tab-pane label="Coordinates" name="coorodinates">
						<div style="display:flex;justify-content: space-evenly;align-items: center;">
							<label style="padding:8px;">Lattitude</label>
							<input name="inputName+'[lat]" type="number" class="form-control" placeholder="Between -90 and 90" v-model.number="lat" max="90" min="-90" step="any"  />
							<label style="padding:8px;" >Longitude</label>
							<input name="inputName+'[lng]"  type="number" class="form-control" placeholder="Between -180 and 180" v-model.number="lng" max="180" min="-180" step="any"" />
							<a class="btn" @click="clear">Clear</a>
						</div>
					</el-tab-pane>
				</el-tabs>
			</neon-core-form-field>
		`,
		watch: {
			lat: function(lat) {
				this.setPosition(lat, this.lng);
				this.modelValue = {lat: lat, lng:this.lng, address:this.address};
			},
			lng: function(lng) {
				this.setPosition(this.lat, lng);
				this.modelValue = {lat: this.lat, lng:lng, address:this.address};
			},
			address: function(address) {
				this.modelValue = {lat: this.lat, lng:this.lng, address:this.address};
			}
		},
		mounted() {
			var vm = this;
			// creates a map object and stores in this.map
			this.createMap();
			// Create a marker to represent the location
			this.marker = new google.maps.Marker({map: this.map, position: this.map.getCenter(), draggable: true});
			// Move marker to where the map was clicked
			google.maps.event.addListener(this.map, 'click', function(event) {
				vm.setPosition(event.latLng.lat(), event.latLng.lng());
			});
			// After the marker is dragged
			google.maps.event.addListener(this.marker, "dragend", function(event) {
				vm.setPosition(event.latLng.lat(), event.latLng.lng());
			})
		},
		computed: {
			isAddressLookupError: function() { return this.status === MAP_STATES.ADDRESS_LOOKUP_ERROR; },
			isAddressLoading: function() {return this.status === MAP_STATES.ADDRESS_LOOKUP; },
		},
		methods: {
			createMap() {
				var myStyles =[{featureType: "poi", elementType: "labels", stylers: [{ visibility: "off" }]}];
				this.geocoder = new google.maps.Geocoder;
				this.map = new google.maps.Map(this.$refs.map, {
					center: {lat: this.lat, lng: this.lng},
					zoom: 7,
					draggable: !this.disabled,
					options: {maxZoom: 20, minZoom: 3, scrollwheel: false, mapTypeId: "roadmap", styles: myStyles, zoomControl: true, mapTypeControl: false, scaleControl: true, streetViewControl: false, rotateControl: false, fullscreenControl: true},
					defaultZoom: 6,
					yesIWantToUseGoogleMapApiInternals: true,
				});
			},
			clear: function() {
				this.lat = null;
				this.lng = null;
				this.address = '';
				this.addressSuggestions = [];
			},
			/**
			 * Adds a marker to the map
			 * @param location - latLng
			 * @param map - the google map object
			 */
			addMarker: function(location, map) {
				this.marker = new google.maps.Marker({
					position: location,
					draggable:true,
					map: map
				});
			},
			setPosition: function(lat, lng, lookupAddress) {
				lookupAddress = lookupAddress || true;
				var latLng = new google.maps.LatLng({lat: lat, lng: lng})
				this.marker.setPosition(latLng);
				this.map.panTo(latLng);
				if (lookupAddress)
					this.getAddressFromLocation();
				this.addressSuggestions = [];
				this.lat = lat;
				this.lng = lng;
			},
			/**
			 * Handles the user address input field
			 * @param event
			 */
			addressSearch: function(event) {
				this.address = event.target.value;
				var vm = this;
				this.status = MAP_STATES.ADDRESS_LOOKUP;
				this.lookupAddress();
			},
			lookupAddress: _.debounce(function() {
				var vm = this;
				this.geocoder.geocode({address: this.address}, function(results, status) {
					if (status !== 'OK') {
						vm.status = MAP_STATES.ADDRESS_LOOKUP_ERROR;
						return;
					}
					vm.addressSuggestions = results;
					vm.status = MAP_STATES.IDLE;
				});
			}, 100),
			getAddressFromLocation: function() {
				var latLng = new google.maps.LatLng({lat: this.lat, lng: this.lng})
				var vm = this;
				this.geocoder.geocode({location: latLng}, function(results, status) {
					if (status === 'OK' && results && results.length > 0) {
						vm.address = results[0].formatted_address;
					}
				});
			},
			setAddress: function(address) {
				this.address = address.formatted_address;
				var loc = address.geometry.location;
				this.setPosition(loc.lat(), loc.lng(), false);
			}
		}
	});


	// region: Multiple Image selector - usefull for image galleries - allows reordering ideally store as json array of
	// firefly ids
	Vue.component('neon-core-form-fields-imagemultiple', {
		extends: Vue.component('neon-core-form-fields-base'),
		props: {
			value: {type: [String, Array, Object]},
		},

		template: ' \
			<neon-core-form-field v-bind="fieldProps" class="neonFieldJson"> \
				<input style="display:none;" type="hidden" :name="inputName" :value="modelValue" /> \
				<div @click="addImage" class="btn btn-default" style="margin-bottom:8px;"><i class="fa fa-plus"></i> Image(s)</div> \
				<draggable ref="sortable" style="display:flex;flex-wrap:wrap" @end="onEnd"> \
					<div :id="image" v-for="(image, key) in images()" :key="image" style="width:100px; height:100px; border: 1px solid #ccc; position:relative;border-radius:4px;margin-right:8px;cursor:move"> \
						<img :src="url(image)" style="max-width:100px;max-height:100px;margin:auto;height:auto;position:absolute;top:0;left:0;right:0;bottom:0;padding:4px;"/> \
						<div @click="deleteImage(key)" class="btn btn-xs btn-danger" style="position:absolute;top:-5px;right:-5px;border-radius:20px;"><i class="fa fa-times"></i></div> \
					</div> \
				</draggable> \
			</neon-core-form-field> \
		',
		created: function() {
			// to cope with json and php mapping - unfortunately serialising from a php array of:
			// ['one', 'two'] actually generates an object in javascript like {'0': 'one', '2': 'two'}
			if (_.isObject(this.value))
				this.modelValue = JSON.stringify(_.values(this.value));
			// we will assume this is valid json that needs parsing
			if (_.isArray(this.value))
				this.modelValue = JSON.stringify(this.value);
		},
		methods: {
			onEnd: function(evt) {
				var images = JSON.parse(this.modelValue);
				images.splice(evt.newIndex, 0, images.splice(evt.oldIndex,1)[0]);
				this.modelValue = JSON.stringify(images);
			},
			images: function() {
				return JSON.parse(this.modelValue);
			},
			addImage: function() {
				var vm = this;
				FIREFLY.pickerMultiple(this.path, function(items) {
					items.forEach(function(item) {
						vm.addItem(item);
					})
				})
			},
			addItem: function(item) {
				var images = this.images();
				if (!_.isArray(images)) {
					images = [];
				}
				images.push(item.id);
				this.modelValue = JSON.stringify(images);
			},
			deleteImage: function(key) {
				var images = this.images();
				images.splice(key, 1)
				this.modelValue = JSON.stringify(images);
			},
			url (image) { return FIREFLY.getImageUrl(image) },
		}

	});
// endregion


})();



