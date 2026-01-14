<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 28/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */
use \neon\core\helpers\Page;
use \neon\core\form\assets\FormAsset;
FormAsset::register($this);
?>


<?php
/**
 * Show the component list
 */
?>
<link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.6.0/pure-min.css">

<style>


	.neon-fb {}

	#neon-fb-sidebar { position: absolute; top: 0; bottom: 0; width: 300px; box-shadow: 0px 0px 3px #7b7b7b; background-color: #fff; overflow-y:scroll; }
	#neon-fb-preview { position: absolute; left: 300px; right: 0px; bottom: 0px; top: 0px; padding: 0 20px 20px 20px; overflow-y:scroll;}
	#neon-fb-sidebar-body {padding:10px;}
	/* - the class defining the droppable area */
	#neon-fb-field-drop {min-height: 50px;}

	.neon-fb-field-btn { cursor: pointer; width: 132px; text-align: left; margin: 4px 3px;}
	.neon-fb-field-placeholder {border: 1px dashed #ccc; min-height: 50px; }
	.neon-fb-empty {padding:0 0; background-color:#eee; content:"Drag and drop fields here to get started"}
	/* wrapper class around a field */
	.neon-fb-field:hover {outline: 1px dashed #aaa;}
	.neon-fb-field.is-loading {padding:10px;}
	/** nice little hr element with text in the center - move out to generic component **/
	.hr-text { padding:5px; overflow: hidden; text-align: center; }
	.hr-text:before,
	.hr-text:after { background-color: #ccc; content: ""; display: inline-block; height: 1px; position: relative; vertical-align: middle; width: 50%; }
	.hr-text:before { right: 0.5em; margin-left: -50%; }
	.hr-text:after { left: 0.5em; margin-right: -50%; }

	.is-selected {background-color:#fffbdf !important}
	.is-selected .is-focus{background-color:#fffbdf !important;}

	#neon-fb-save {}

</style>

<div class="neon-fb">


	<div id="neon-fb-sidebar">

		<div id="neon-fb-sidebar-body">

			<?php foreach ($defaultFields as $key => $field): ?>
				<?php if ($field['group'] == 'basic'): ?>
					<div data-field="<?= $field['key']; ?>" <?= isset($field['disabled']) && $field['disabled'] ? 'disabled' : '' ; ?> class="btn btn-default neon-fb-field-btn">
						<i class="<?= $field['icon'] ?>"></i> <?= $field['name'] ?>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>

			<div class="hr-text">Advanced</div>

			<?php foreach ($defaultFields as $key => $field): ?>
				<?php if ($field['group'] == 'advanced'): ?>
					<div data-field="<?= $field['key']; ?>" <?= isset($field['disabled']) && $field['disabled'] ? 'disabled' : '' ; ?> class="btn btn-default neon-fb-field-btn">
						<i class="<?= $field['icon'] ?>"></i> <?= $field['name'] ?>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>

			<div class="hr-text">Field Editor</div>
			<div id="field-editor"></div>

		</div>

	</div>

	<div id="neon-fb-preview">

		<?= $form['header'];//$form->renderHeader(); ?>

		<div id="neon-fb-header">
			<h4>Speakers</h4>
		</div>

		<div id="neon-fb-field-drop" class="neon-fb-empty">

			<?php foreach($form['fields'] as $field): ?>
				<div class="neon-fb-field" data-component-key="<?= $field['_key'] ?>" >
					<?= $field['html'] ?>
				</div>
			<?php endforeach; ?>

		</div>

		<div class="form-group">
			<div class="controls">
				<button class="btn btn-default">Submit</button>
			</div>
		</div>

		<?= $form['footer'] //$form->renderFooter(); ?>

		<div id="neon-fb-save">
			<button class="btn btn-default">Save</button>
			<button class="btn btn-default">Publish</button>
		</div>

		<button id="serializeForm" class="btn btn-default">Serialize Form</button>
		<pre id="serializedForm">

		</pre>

		<div id="content">REACT TO GO HERE</div>

	</div>






</div>


<?php Page::jsBegin(); ?>
<script>



	$.ajaxPrefilter(function( options, originalOptions, jqXHR ) {
		options.async = true;
	});

	// When clicking on a form element:
	$('#neon-fb-preview').on('click', '.neon-fb-field', function(){
		var key = $(this).attr('data-component-key');
		neonFb.loadPropertyInspector(key);
		// visual style stuff
		$('.neon-fb-field').removeClass('is-selected');
		$(this).addClass('is-selected');
	});




	window.neonFb = {

		/**
		 * @return jQuery.jqXHR
		 */
		getFieldHtmlAjax: function(field){
			return $.ajax({
				method: "POST",
				url: 'http://local.newicon.org/tedxbristol.com/web/cms/data/add-field',
				data: {
					field: field
				},
				dataType:'json'
			});
		},

		/**
		 *
		 */
		loadPropertyInspector: function(key){
			return $.ajax({
				method: "POST",
				url: 'http://local.newicon.org/tedxbristol.com/web/cms/data/get-property-inspector-form',
				data: {key: key},
			})
			.done(function(html){
				$('#field-editor').html(html);
				$('#propertyInspector').keyup(neonFb.applyInspector);
			})
			.fail(function(){
				$('#field-editor').html('-');
			});
		},

		applyInspector: function(){
			var formData = $('#propertyInspector').serialize();
			var formDataArray = $('#propertyInspector').serializeArray();
			return $.ajax({
				method: "POST",
				url: 'http://local.newicon.org/tedxbristol.com/web/cms/data/apply-inspector',
				data: formData
			}).done(function(response){
				$('[data-component-key="'+response['key']+'"').replaceWith('<div class="neon-fb-field" data-component-key="'+response.key+'">' + response.html + '</div>');
			})
		}

	}

	/**
	 * =====================================================================
	 * Sortable Form Fields
	 * =====================================================================
	 */
	$("#neon-fb-field-drop:not()").sortable({
		helper: 'clone',
		out: function(){},
		axis: "y",
		start: function(){},
		forcePlaceholderSize:true,
		placeholder: 'neon-fb-field-placeholder',
		over: function() {},
		receive: function(event, ui){

			var field = ui.item.attr('data-field');


			$field = $('<div class="neon-fb-field is-loading">Loading...</div>');
			$(ui.helper).replaceWith($field);
			// go off and get the field html via ajax
			var html = neonFb.getFieldHtmlAjax(field).done(function(response){
				$field.replaceWith('<div class="neon-fb-field" data-component-key="'+response.key+'">' + response.html + '</div>');
			});

			//neonFb.getFieldHtml(field)

			//$(ui.helper).replaceWith('<div class="neon-fb-field">' + html + '</div>');

		}
	}).disableSelection();



	/**
	 * =====================================================================
	 * Draggable Field Buttons
	 * =====================================================================
	 * Draggable options for the field buttons
	 */
	$(".neon-fb-field-btn:not([disabled])").draggable({
		connectToSortable: "#neon-fb-field-drop",
		helper: function(event, element) {
			$current = jQuery(this);
			$clone = jQuery(this).clone();
			return $clone.height($current.outerHeight()).width($current.outerWidth());
		},
		revert: "invalid",
		cancel: !1,
		appendTo: "body",
		containment: "document",
	})

</script>
<?php Page::jsEnd(); ?>

<?php Page::jsFile('https://code.jquery.com/ui/1.12.0/jquery-ui.min.js', ['depends' => '\yii\web\JqueryAsset']); ?>