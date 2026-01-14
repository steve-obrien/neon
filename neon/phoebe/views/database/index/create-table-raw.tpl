<div class="workbench">
	{include '@neon/daedalus/views/partials/header.tpl' back="/daedalus/index/index" showAdd=false showEdit=false}
	<div class="workbenchBody">
		<div class="workbenchBody_content workbenchBody_content--form" style="background:#fff">
			<div class='panel'>
				<h3>Table Generation from Raw Definition</h3>
				<p>Use this to create a daedalus table from a definition generated externally to Phoebe.</p>
				<p>The minimum you need per field is the class (e.g. text, textarea) and name. You can also add the label and a hint or any other parameters understood by the form. This is useful for example if you are creating a large table from an external specification.</p>
				<p>Once the base form has been entered you can use Phoebe to further tweak the form, or revert the generated migrations to remove the table.</p>
				<p><strong>Note that curently no checking of the definition is performed, so an error may result in a partially created class.</strong></p>
				<div class="workbenchBody__form">
					{$form->run() nofilter}
				</div>
			</div>
		</div>
		<div class="workbenchBody_entity-sidebar" style="padding:20px; background:white;">
			<div class="dds-sidebar " >
			</div>
		</div>
	</div>
</div>

