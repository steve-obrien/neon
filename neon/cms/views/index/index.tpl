
<div class="workbench">
	<header class="workbenchHeader">
		<h1 class="workbenchHeader_title">Cobe CMS</h1>
		<div class="toolbar">
			<a class="btn btn-primary " href="{url route='/cms/editor/index'}"><i class="fa fa-pencil fa-btn" aria-hidden="true"></i> Edit Pages</a>
			<a class="btn btn-primary " href="{url route='/cms/redirects/index'}"><i class="fa fa-pencil fa-btn" aria-hidden="true"></i> Redirects</a>
			{if ($can_page_add)}
				<a class="btn btn-primary " href="{url route='/cms/editor/page-add-editor'}"><i class="fa fa-plus fa-btn" aria-hidden="true"></i> Add Page</a>
			{/if}
			{if ($can_develop)}
				<a class="btn btn-info " href="{url route='/cms/editor/page-add'}"><i class="fa fa-plus fa-btn" aria-hidden="true"></i>Developer: Add Page Template</a>
			{/if}
		</div>
	</header>
	<div class="workbenchBody">
		<div class="workbenchBody_content">
			{$grid->run() nofilter}
		</div>
	</div>
</div>