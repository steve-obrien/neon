<div class="workbench" style="padding-bottom:50px"><!-- padding to raise above footer -->

	{capture "buttons"}
		<a href="/daedalus/index/edit-object?type={$class.class_type}&id={$object._uuid}" class="btn btn-default pull-right">Edit</a>
	{/capture}
	{include '../partials/header.tpl' back="/daedalus/index/list?type={$class.class_type}" buttons=$smarty.capture.buttons}
	<div class="workbenchBody">
		<div class="workbenchBody_content workbenchBody_content--form" style="background:#fff">
			<div class="workbenchBody__form">
				{$form->run() nofilter}
			</div>
		</div>
		{include '../partials/objectSidebar.tpl' object=$object}
	</div>
</div>
