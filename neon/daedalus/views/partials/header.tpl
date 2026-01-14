{css}.table img { max-width:50px; max-height:50px } .neonGrid .table td, .neonGrid .table tr { max-width:16em; }{/css}
<header class="workbenchHeader">
	<a href="{url route={$back|default:'/daedalus/index/index'}}" class="btn btn-link"><i class="fa fa-chevron-left"></i></a>
	<div class="workbenchHeader_left">
		<h1 class="workbenchHeader_title">{if (isset($title))} {$title} {else} {$class.label} {/if}</h1>
	</div>
	<div class="toolbar">
		{$buttons|default:''}
	</div>
</header>