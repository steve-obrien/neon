`<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 11/10/2016 12:27
 * @package neon/phoebe
 */


use \neon\core\helpers\Page;
use \neon\core\form\assets\FormAsset;
use \neon\phoebe\builders\database\assets\FormBuilderAsset;

FormBuilderAsset::register($this);

?>

<!-- The DOM entry point for the Form Builder App -->
<form id="default_fields_form"></form>
<div id="FormBuilder" class="neon-fb"></div>
<style>
	#property_editor .neonFieldSwitch .neonField_content {display:flex; justify-content: space-between;}
	#property_editor .neonFieldSwitch .neonField_label {margin-top:8px;}
</style>
<style>
	.ui-resizable-handle {
		width: 5px; position: fixed; top: 50px; bottom: 0; cursor: col-resize;
	}
	.ui-resizable-handle.ui-resizable-w {
		margin-left: -3px;
	}
</style>
<script>
	neon.phoebe = {
		fields: <?php echo json_encode($fields); ?>,
		definition: <?php echo $definition; ?>,
		dds: <?php echo json_encode($dds); ?>,
		data: <?php echo json_encode($data); ?>
	};
</script>

<?php Page::jsBegin(); ?>
<script>

	window.FormBuilderApp = {
		// define classes
		Class: {},
		widget: {
			defaultState: {
				// default field state all fields extend
				default: {
					class: "\\neon\\core\\form\\fields\\Text",
					label: '',
					hint: '',
					placeholder: '',
					required: false,
					deleted: 0
				}
			}
		},
		Store: {},
		/**
		 * Add a component to the form builder.
		 * Pass an object with the following properties:
		 *
		 * - `ref`  String: A name that identifies this component uniquely (must be appropriate to use directly in html [a-z0-9_-]
		 * - `icon`  Object: An icon object consisting of the following properties:
		 *     - `name`  String: The name shown to the end user for this component for e.g. 'Multi Line Text',
		 *     - `icon`  String:  A css class that will display an icon for e.g. 'fa fa-font',
		 *     - `group`  String:  The group the component belongs in can be 'default' | 'advanced',
		 *     - `order`  Int:  The order of importance - this determines the display order in the builder
		 * - `default`  Object : A default data object that will be used to populate the field component when first adding to the form.
		 *                       this object will extend FormBuilderApp.widget.defaultState.default. For example :
		 *                       `{ class: "neon\\core\\form\\fields\\Text", label: 'Text'}`
		 * - `field`  Vue Object: A Vue object used draw and manage the field
		 * - `form`  Vue Object A Vue object used to draw and manage the form
		 *
		 * An Example object to add a text field:
		 *
		 * ```js
		 * {
		 *     data: {
		 *         class: "neon\\core\\form\\fields\\NewTextBox", // this is an important property!!
		 *         label: 'New Tex Box'
		 *     },
		 *     icon: {name: 'A New Example Text Box',  icon: 'fa fa-font', group: 'default', order: 0},
		 *     form: Vue.component('form-builder-field-form-default').extend({}),
		 *     field: Vue.component('form-builder-widget-default').extend({
		 *         template: `<form-field-text @click="select" :field="data"></form-field-text>`,
		 *     })
		 * }
		 * ```
		 */
		addFieldComponent: function(definition) {
			neon.Store.commit('phoebe/addFieldComponent', definition);
		},

		/**
		 * Set default start up data for a component
		 * @param  {string}  ref  - The component ref
		 * @param  {Object}  data  - default object
		 */
		setDefaultState: function(ref, data)
		{
			var defaultComponent = FormBuilderApp.widget.defaultState.default;
			FormBuilderApp.widget.defaultState[ref] = _.assign({}, defaultComponent, data);
		},

		/**
		 * Get the default start state data for a field component of type `ref`
		 * @param  {string}  ref  - The component unique reference name
		 */
		getDefaultState: function(ref) {
			return FormBuilderApp.widget.defaultState[ref];
		}
	};

	FormBuilderApp.helpers = {

		counter: 1,

		getTypeFromClass: function(className) {
			var type = className.split('\\').pop();
			// long component name =
			type = className.replace(/\\/g, '-');
			// look up map
			return type.toLowerCase();
		},

		makeUniqueName: function(name, count) {
			count = count || 1;
			name = name || 'field';
			if (FormBuilderApp.helpers.isNameUnique(name)) {
				return name;
			} else {
				// If it is not unique append an '_#' number recursively incrementing # until it is unique
				var regex = /_[0-9]+$/g;
				name = name.match(regex) ?  name.replace(regex, '_' + count) : name += '_' + count;
				count ++;
				return this.makeUniqueName(name, count);
			}
		},

		/**
		 * @return boolean true if the name is unique within the field collection
		 */
		isNameUnique: function(name) {
			var result = _.isUndefined(_.find(neon.Store.state.phoebe.form.fields, {name: name}));
			return result;
		}

	};

	neon.Store.registerModule('phoebe', PHOEBE_STORE);
	FormBuilderApp.Store = neon.Store;

	/* -------------------------------------------------------------------------------------------------------------
	 * Main Application
	 * -------------------------------------------------------------------------------------------------------------
	 */
	FormBuilderClass = Vue.extend({
		store: neon.Store,
		template: `
			<div class="workbench">
				<header class="workbenchHeader">
					<div class="workbenchHeader_left">
						<h1 class="workbenchHeader_title">{{ state.form.label }}</h1>
					</div>
					<div class="toolbar">
						<a :href="state.urls.list" class="btn btn-default" :disabled="!state.dds.exists"><i class="fa fa-list"></i> List</a>
						<a :href="state.urls.add" class="btn btn-default"><i class="fa fa-plus"></i> Add New</a>
						<a @click="populate" class="btn btn-default">Populate with fake data</a>
						<span class="toolbar__divider">|</span>
						<a :href="state.urls.contentTypes" class="btn btn-default">&lt; Back to Database Index</a>
					</div>
					<div class="workbenchHeader_right">
						<a :href="state.urls.tableFromJson" class="btn btn-danger"><i class="fa fa-plus-circle"></i> Create Table From JSON</a></a>
					</div>
				</header>
				<div class="workbenchBody">
					<div id="componentSidebar" class="l__form-builder-sidebar  workbenchBody_entity-sidebar">
						<div class="l__form-builder-sidebar__body " v-cloak>
							<form-builder-field-buttons></form-builder-field-buttons>
						</div>
					</div>
					<div class="l__form-builder-preview workbenchBody_content" v-cloak>
						<div class="neonForm neonForm--fancy ">
							<form-builder-form-header :form="state.form"></form-builder-form-header>
							<neon-core-form-form name="builder" id="builder">
								<form-builder-form-fields :form="state.form"></form-builder-form-fields>
							</neon-core-form-form>
							<br />
							<button @click="save" :disabled="state.savingForm" class="btn btn-primary btn-lg">{{ state.savingForm ? 'Saving...' : 'Save &amp; Exit' }}</button>
							<button @click="saveContinue" :disabled="state.savingForm" class="btn btn-primary btn-lg">{{ state.savingForm ? 'Saving...' : 'Save &amp; Continue' }}</button>
							<br/><br/>
						</div>
					</div>
					<div id="editorSidebar" class="l__form-builder-sidebar  workbenchBody_entity-sidebar" style="position:inherit;">
						<div class="l__form-builder-sidebar__body " v-cloak>
							<neon-property-editor v-if="editingField" :editing-field="editingField"></neon-property-editor>
						</div>
					</div>
				</div>
			</div>
		`,
		mounted: function() {
			$("#editorSidebar").resizable({
				handles: "w",
				minWidth: 150,
				resize:function(event, ui){
					ui.element.css('left', '');
				}
			});
		},
		methods: {
			populate: function() {
				//:href="populateUrl"
				var populatePopup = {
					props: {
						dataObject: {}
					},
					template: `
						<div style="padding:20px;">
							<h2 style="margin-top:0;">Populate table with fake data</h2>
							<br/>
							<p>
								<span style="font-size:14px;">Fake rows to generate:</span> <el-input-number size="medium" v-model="dataObject.rowsToGenerate" :min="1"></el-input-number>
								<br/><br/>
								Current number of rows: {{dataObject.startTotal}}
								<br/>
								After populating the table will have: {{dataObject.startTotal + dataObject.rowsToGenerate}} rows
								<br/>
								{{dataObject.percent > 1 ? 'Generated ' + (dataObject.currentTotal - dataObject.startTotal) + ' rows' : ''}}
								<el-progress v-if="dataObject.percent > 1" :percentage="dataObject.percent"></el-progress>
							</p>
						</div>
					`,
				};

				// create a data object to pass between modal buttons and the modal rendered component
				var data = new Vue({
					data: {rowsToGenerate: 100, startTotal:0, currentTotal:0, pollTimer:{}, percent:0},
					created() {
						const url = neon.base()+'/daedalus/index/populate-progress?type='+neon.Store.state.phoebe.dds.class_type;
						$.post(url).done(response => { this.startTotal = response;})
					},
					methods: {
						pollProgressStart() {
							const url = neon.base()+'/daedalus/index/populate-progress?type='+neon.Store.state.phoebe.dds.class_type;

							var updateData = (count) => {
								let target = this.rowsToGenerate;
								this.percent =  Math.round(((this.currentTotal - this.startTotal) / target) * 100);
								this.currentTotal = count;
							}
							$.post(url).done(count => {
								this.startTotal = count; updateData(count);
							})
							this.pollTimer = setInterval(() => { $.post(url).done(updateData)}, 500);
						},
						pollProgressStop(done) {
							clearInterval(this.pollTimer);
						}
					}
				});
				var vm = this;
				neon.modal.show(populatePopup, {dataObject: data}, {
					clickToClose: false,
					buttons: [
						{title: 'Close'},
						{
							title: 'Generate',
							loading: false,
							handler: (event, i) => {
								var thisButton = event.sender.buttons[i];
								thisButton.loading = true;
								let url = neon.base()+'/daedalus/index/populate?type='+vm.$store.state.phoebe.dds.class_type;
								let postData = {rows: data.rowsToGenerate};

								// now lets poll for progress.
								// ideally we would have a socket connection here and we could subscribe to a auto deadlus
								// event that daedalus publishes too.  But we dont have this yet
								data.pollProgressStart();

								// issue command to populate records
								$.post(url, postData)
									.done(reponse => {event.close();})
									.fail(response => {neon.error(response);neon.flash(response.responseText, 'error', 0, true)})
									.always(() => {data.pollProgressStop(); thisButton.loading = false;})
							}
						}
					]
				});
			},
			save: function() {
				this.$store.dispatch('phoebe/save').then(function(data) {
					location.href = neon.phoebe.data.urls.list + '?type='+data.class_type;
				});
			},

			saveContinue: function(){
				this.$store.dispatch('phoebe/save').then(function(data) {
					// flash successfully saved message
				});
			}
		},
		computed: {
			populateUrl: function() {
				return neon.base() + '/daedalus/index/populate?type=' + this.state.dds.class_type;
			},
			state: function() {
				return this.$store.state.phoebe;
			},
			editingField: function() {
				return this.$store.getters['phoebe/editingField']();
			},
			fields: function() {
				return this.$store.getters['phoebe/fields']();
			}
		}
	});

	Vue.component('neon-property-editor', {
		props: {
			editingField: {}
		},
		data: function () {
			return {
				tab: 'editor',
				isDeleting:false
			};
		},
		template: `
			<div>
				<ul class="nav nav-tabs">
					<li @click="tab = 'editor'" :class="tab == 'editor' ? 'active' : ''"><a href="#">Editor</a></li>
					<li @click="tab = 'definition'" :class="tab == 'definition' ? 'active' : ''"><a href="#">Definition</a></li>
				</ul>
				<div>
					<div class="hr-text">{{editingField.classLabel}}</div>
					<div v-if="tab == 'editor'">

						<div v-if="hasCustomPropertyEditor()" :is="getCustomPropertyEditor()" key="customPropertyEditor"></div>
						<div else key="AutoPropertyEditor">

							<neon-core-form-form key="property_editor" name="property_editor" id="property_editor" @onChange="update">
								<neon-core-form-form :key="editingField.name" :name="editingField.name" :id="'form_'+editingField.name" @onChange="update">

									<form-builder-field-form-element-name parent="property_editor" :field="editingField"></form-builder-field-form-element-name>

									<form-builder-edit-property v-for="fieldInput in getFormFields()"
										:field="editingField"
										:key="editingField.name + '_' + fieldInput.name + '_edit'"
										:property="fieldInput.name"
										:editor="getEditor(fieldInput)"
										:editor-props="fieldInput">
									</form-builder-edit-property>

									<div class="form-group">
										<a @click="deleteField(editingField)" class="btn btn-danger" ><i class="fa fa-trash"></i><span v-if="isDeleting">Deleting...</span></a>
									</div>
								</neon-core-form-form>
							</neon-core-form-form>
						</div>
					</div>
					<div v-if="tab == 'definition'">
						<neon-input-json :value="editingField" :options="{tabSize:4}"></neon-input-json>
					</div>
				</div>
			</div>
		`,
		methods: {
			deleteField: function(editingField){
				var that = this;
				that.isDeleting = true;
				this.$store.dispatch('phoebe/deleteField', {field: editingField})
					.then(function() { that.isDeleting = false; }, function() { that.isDeleting = false; });
				return false;
			},
			hasCustomPropertyEditor: function() {
				return !_.isUndefined(this.getCustomPropertyEditor());
			},
			getCustomPropertyEditor: function() {
				var $options = this.getComponentClassOptions();
				return _.result($options, 'propsEditor');
			},
			update: function(value){
				// this function could be used to update the component that is being edited - so the form components can
				// be dumber
			},
			getEditor: function(field) {
				if (field.component) {
					return field.component;
				} else if (field.class) {
					var classRef = neon.classToComponent(field.class);
					return classRef;
				} else {
					console.error('Unknown component');
				}
			},

			getComponentClassOptions: function() {
				var component = neon.classToComponent(this.editingField.class);
				var $options = Vue.options.components[component].options;
				return $options;
			},

			getPropsEditor: function() {
				var $options = this.getComponentClassOptions();
				var editor = _.result($options, 'propsEditor');
				if (!_.isUndefined(editor)) {
					return editor;
				}
				return this.getPropsEditorDefinition($options.props);
			},

			getPropsEditorDefinition: function($propsDefinition) {

				var form = {fields: {}};
				var vm = this;
				// create an editor field for each prop
				_.each($propsDefinition, function(prop, name) {

					if (name == 'name') return;

					// set default properties for the editor of this property
					var cmpDefault = {
						// if the object specified by the editor key has not been given a class or component key
						// then we can look at the type to auto populate it for us
						component: vm.getDefaultEditorForType(prop.type),
						name: name,
						label: _.startCase(name),
						default: _.result(prop, 'default')
					};

					if (!_.isUndefined(prop.editor)) {
						// can use editor: false to prevent an editor appearing for the prop
						if (prop.editor === false)
							return;
						form.fields[name] = _.defaults(prop.editor, cmpDefault);
					} else {
						form.fields[name] = cmpDefault;
					}
				});
				return form;
			},

			getDefaultEditorForType: function(type) {
				switch (type) {
					case Boolean: return 'neon-core-form-fields-switchbutton';
					case String:  return 'neon-core-form-fields-text';
					case Number:  return 'neon-core-form-fields-el-number';
					case Object:  return 'neon-core-form-fields-json';
					default:      return 'neon-core-form-fields-json';
				}
			},

			getFormFields: function() {
				var form = this.getPropsEditor();
				return _.sortBy(form.fields, 'order');
			}
		},
	});

	FormBuilderClass.Widgets = {};

	/**
	 * -------------------------------------------------------------------------------------------------------------
	 * Component Form
	 * -------------------------------------------------------------------------------------------------------------
	 */
	Vue.component('form-builder-field-form', {
		props:{
			field:{
				type: Object,
				required: false
			}
		},
		template: `
			<div>
				<!--<div class="hr-text">Field Editor</div>-->
				<neon-core-form-form id="componentProperties" name="componentProperties">
					<component v-if="field" :is="fieldForm" :field="field" class="neonForm" ></component>
				</neon-core-form-form>
				<div v-if="!field">Select a widget on the left to begin editing</div>
			</div>
		`,
		computed: {
			fieldForm: function() {
				var type = FormBuilderApp.helpers.getTypeFromClass(this.field.class);
				return 'form-builder-field-form-' + type;
			}
		}
	})

	/**
	 * Format a string to comply with the DDS `ref` names.
	 * All lower case a-z0-9 and underscores
	 */
	Vue.directive('ddsformat', {
		bind: function (el, binding, vnode) {
			el.addEventListener("keydown", function (event) {
				if (event.key.match(/[^a-zA-Z0-9_ ]/g)) {
					event.preventDefault();
					$(el).parent().addClass("has-error").delay(150).queue(function(next){
						$(this).removeClass("has-error");
						next();
					});
				}
			});
		}
	});

</script>
<?php Page::jsEnd(); ?>

<?php
// Load in the widget files
//foreach(\yii\helpers\FileHelper::findFiles(__DIR__.'/components') as $file) {
//	$this->render('components/' . basename($file));
//};
?>
<?php Page::jsBegin(); ?>
<script>

	_.forEach(neon.phoebe.fields, function(field) {
		FormBuilderApp.addFieldComponent({

			/* ----------------------------------------------------------------------------------------
			 * Default State Data
			 * ----------------------------------------------------------------------------------------
			 * The default data to populate this component with when it is first added to the form
			 */
			data: field,

			/* ----------------------------------------------------------------------------------------
			 * Icon Definition
			 * ----------------------------------------------------------------------------------------
			 */
			icon: field,

		});
	})

	/**
	 * GO GO GO!
	 * Launches the app and attaches to the DOM
	 */
	window.FormBuilder = new FormBuilderClass({el: '#FormBuilder'});

	/**
	 * =====================================================================
	 * Draggable Field Buttons
	 * =====================================================================
	 * Draggable options for the field buttons
	 */
	$(".btn--component-icon:not([disabled])").draggable({
		connectToSortable: ".formBuilder_fields",
		helper: function (event, element) {
			$current = jQuery(this);
			$clone = jQuery(this).clone();
			return $clone.height($current.outerHeight()).width($current.outerWidth());
		},
		revert: "invalid",
		cancel: !1,
		appendTo: "body",
		containment: "document"
	});

	$('[debug-toggle]').click(function(){$('#debug').toggle();return false;});


</script>
<?php Page::jsEnd(); ?>
