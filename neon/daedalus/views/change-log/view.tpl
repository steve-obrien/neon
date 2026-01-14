<div class="workbench">
	{include '../partials/header.tpl' back=$goBackTo title=$headerTitle}
	<div class="workbenchBody">
		<div class="workbenchBody_content workbenchBody_content--form" style="background:#fff">
			<div class="workbenchBody__form">
				{if ($object)}
					{$form->run() nofilter}
				{else}
					<p>The object could not be found</p>
				{/if}
			</div>
		</div>
		{include '../partials/historySidebar.tpl' history=$history}
	</div>
</div>

