<div class="form-group {$componentCssClass} field-{$id} {if $isRequired}required{/if} {if $hasError}has-error{/if}">
	{$label nofilter}
	{$hint nofilter}
	<div class="form-group__input-wrap">
		{$field nofilter}
		{*<div class="form-feedback"><div class="form-feedback-error" aria-hidden="true">&cross;</div></div>*}
		{*<div class="form-feedback"><div class="form-feedback-success" aria-hidden="true">&check;</div></div>*}
	</div>
	{$error nofilter}
</div>