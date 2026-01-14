{css position='head'}.table img { max-width:50px; max-height:50px } .neonGrid .table td, .neonGrid .table tr { max-width:16em; }{/css}
<header class="workbenchHeader">
	<a href="{$goBackTo}" class="btn  btn-link">
		<i class="fa fa-chevron-left"></i>
	</a>
	<div class="workbenchHeader_left" style="margin-left:10px;">
		<h1 class="workbenchHeader_title">{$class.label} Change Log</h1>
	</div>
	<div class="workbenchHeader_right">
		{if ($hasChangeLog)}
			<a onclick="daedalusSetChangeLog(0, '{$goBackTo}');" class="btn btn-primary ">
				<i class="fa fa-history"></i> Stop Change Log
			</a>
		{/if}
	</div>
</header>
<div class="workbenchBody_content">
	<div style="padding:0px 20px;">
		{if ($hasChangeLog)}
			{$grid->run()}
		{else}
			<h3>{$class.label} does not have a change log</h3>
			<p>To turn the change log on for {$class.label} select 'Start Change Log' from the {$class.label} table screen.</p>
		{/if}
	</div>
</div>
{include '../partials/scripts.tpl' type=$class.class_type}
