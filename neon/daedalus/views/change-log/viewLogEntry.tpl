<div class="workbench">
	{include '../partials/header.tpl' back=$goBackTo title=$headerTitle}
	<div class="workbenchBody">
		<div class="workbenchBody_content workbenchBody_content--form" style="background:#fff">
			<div class="workbenchBody__form">
				<legend>{$title}</legend>
				<h4>Comment:</h4>
				<p>{$logEntry.description}</p>
				{if (!empty($associated_objects))}
					<h4>Associated objects:</h4>
					<p>The following objects are associated with this comment</p>
					<p>{$associated_objects}</p>
				{/if}
			</div>
		</div>
		{include '../partials/historySidebar.tpl' history=$history}
	</div>
</div>

