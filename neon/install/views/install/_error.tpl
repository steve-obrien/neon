<div class="alert alert-warning">
	{$message nofilter}
	<br /><br />
	{foreach $buttons as $button}
		<a href="{$button.url}" class="btn {if isset($button.type)} btn-{$button.type} {else} btn-default {/if}"><i class="{if isset($button.iconClass)} {$button.iconClass} {/if}"></i> {$button.label nofilter}</a>
	{/foreach}
</div>


