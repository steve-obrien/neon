<div class="workbench">
	<header class="workbenchHeader">
		<div class="workbenchHeader_left">
			<h1 class="workbenchHeader_title">{$class.label}</h1>
		</div>
		<div class="toolbar">
			<a href="{url route='/phoebe/appforms/index/list-objects' type={$class.class_type} }" class="btn btn-default"><i class="fa fa-list"></i> List</a>
			<a href="{url route='/phoebe/appforms/index/add-object' type={$class.class_type} }" class="btn btn-default "><i class="fa fa-plus"></i> Add New</a>
			{if ($can_develop)}
				<span class="toolbar__divider">|</span>
				<a href="{url route='/phoebe/appforms/index/edit' type={$class.class_type} }" class="btn btn-warning"><i class="fa fa-pencil"></i> Edit Definition</a>
			{/if}
			<span class="toolbar__divider">|</span>
			<a href="{url route='/phoebe/appforms/index/index'}" class="btn btn-default"><i class="fa fa-chevron-left"></i> Back to Forms Index</a>
		</div>
	</header>

	<div class="workbenchBody">

		<div class="workbenchBody_content workbenchBody_content--form" style="background:#fff">
			<div class="workbenchBody__form">
				{$form->run() nofilter}
			</div>
		</div>

		<div class="workbenchBody_entity-sidebar">
			<div class="dds-sidebar">
				<!--<h2 class="sidebar-heading">Versions</h2>
				TODO: add versioning

				<h2 class="sidebar-heading">Users</h2>
				TODO: add user information-->
			</div>
		</div>

	</div>
</div>

