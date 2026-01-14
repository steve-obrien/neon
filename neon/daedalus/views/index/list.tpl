{css position='head'}.table img { max-width:50px; max-height:50px } .neonGrid .table td, .neonGrid .table tr { max-width:16em; }{/css}
<div class="workbench">
<header class="workbenchHeader">
	<a href="{url route='/daedalus/index/index'}" class="btn  btn-link">
		<i class="fa fa-chevron-left"></i>
	</a>
	<div class="workbenchHeader_left" style="margin-left:10px;">
		<h1 class="workbenchHeader_title">{$class.label}</h1>
	</div>
	<div class="toolbar">
		<a href="{url route='/daedalus/index/add-object' type=$class.class_type}" class="btn btn-primary">
			<i class="fa fa-plus"></i> Add New
		</a>
	</div>
	<div class="workbenchHeader_right">
		<a onclick="showDataDictionary()" class="btn btn-default " title="View information about this type">
			<i class="fa fa-info"></i> Info
		</a>
		{if ($class.change_log)}
			<a href="{url route='/daedalus/change-log/list' type=$class.class_type}" class="btn btn-primary ">
				<i class="fa fa-history"></i> Change Log
			</a>
		{else}
			<a onclick="daedalusSetChangeLog(1);" class="btn btn-primary ">
				<i class="fa fa-history"></i> Start Change Log
			</a>
		{/if}
		{if ($can_develop)}
			<a href="{url route='/phoebe/database/index/edit' type=$class.class_type}" class="btn btn-danger ">
				<i class="fa fa-pencil"></i> Edit Definition
			</a>
		{/if}
	</div>
</header>
<div class="workbenchBody_content">
	<div style="padding:0px 20px;">
		{$grid->run()}
	</div>
</div>
{include '../partials/scripts.tpl' type=$class.class_type}

{js}
<script>
	window.showDataDictionary = function(){
		neon.modal.show(
			// A Vue component definition
			{
				props: { classType: '', required: true },
				data: function() { return { dictionary: 'Loading' } },
				delimiters: ['[[', ']]'],
				template:'<div style="padding:16px;"><div v-html="dictionary">Loading...</div></div>',
				mounted: function() {
					var vm = this, url = neon.url('/daedalus/index/data-dictionary-for?classType='+this.classType);
					$.get(url).done(function(r) { vm.dictionary = r; }).fail(function(r){ vm.dictionary = r.responseText; });
				}
			},
			// Data to pass as props to the component defined above
			{ classType: '{$class.class_type}' },
			// Properties for the modal
			{
				draggable: true, resizable:true, width:'90%', height:'90%',
				buttons: [ { title: 'Close' } ]
			},
		)
	};
</script>
{/js}

</div>
