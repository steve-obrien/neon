{css position='head'}.table img { max-width:50px; max-height:50px } .neonGrid .table td, .neonGrid .table tr { max-width:16em; }{/css}
<header class="workbenchHeader">
	<a href="{$goBackTo}" class="btn  btn-link">
		<i class="fa fa-chevron-left"></i>
	</a>
	<div class="workbenchHeader_left" style="margin-left:10px;">
		<h1 class="workbenchHeader_title">Database Change Log</h1>
	</div>
</header>
<div class="workbenchBody_content">
	<div style="padding:0px 20px;">
		{if ($hasChangeLogs)}
			<p>Change logs are available for the following tables: {$availableChangeLogs}</p>
			{$grid->run()}
		{else}
			<h3>No Change Log Set</h3>
			<p>There are no tables with a change log set. To set the change log for a particular table, go to the table listing and click on 'Start Change Log'.</p>
		{/if}
	</div>
</div>
