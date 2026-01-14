{ddsForm
	action="/cms/data/daedalus-editor?key=$key&classType=$classType"
	classType=$classType
	uuid=$key
	enableAjaxValidation=true
	enableAjaxSubmission=true
}
{if ($submitted)}
	{getDdsObject uuid=$key asJson=true}
	{$ddsObject}
{else}
	{$form->run()}
{/if}