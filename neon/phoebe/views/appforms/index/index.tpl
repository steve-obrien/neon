<div class="workbench">
	<header class="workbenchHeader">
		<div class="workbenchHeader_left">
			<h1 class="workbenchHeader_title">Application Forms</h1>
		</div>
		{if ($can_develop)}
			<div class="workbenchHeader_right">
				<a href="{url route='/phoebe/appforms/index/edit/'}" class="btn btn-primary btn-primary-addition"><i class="fa fa-plus"></i> Create New Form</a>
			</div>
		{/if}
	</header>
	<div class="workbenchBody">
		<div class="workbenchBody_content" >

			<div style="padding:0px 20px;">
				<div class="panel">
					<table class="table table-striped">
						<tr>
							<th style="text-align:center">View<br>List</th>
							<th style="text-align:center">Add<br>New</th>
							<th>Form<br>Name</th>
							<th>Description</th>
							{if ($can_develop)}
								<th style="text-align:center">Edit<br>Definition</th>
								<th>Content<br>Key</th>
							{/if}
						</tr>
						{foreach $classes as $class}
							<tr>
								<td style="text-align:center; width:2%; vertical-align:middle;"><a class="btn btn-small btn-primary" href="{url route='/phoebe/appforms/index/list-objects/' type={$class.class_type} }">View</a></td>
								<td style="text-align:center; width:2%; vertical-align:middle;"><a class="btn btn-small btn-success" href="{url route='/phoebe/appforms/index/add-object/' type={$class.class_type} }">Add</a></td>
								<td style="vertical-align:middle; min-width:15%"><a href="{url route='/phoebe/appforms/index/list-objects/' type={$class.class_type} }">{$class.label|default:{$class.class_type|humanize:true}}</a></td>
								<td style="vertical-align:middle;">{$class.description}</td>
								{if ($can_develop)}
									<td style="text-align:center; width:2%; vertical-align:middle;"><a class="btn btn-small btn-danger" href="{url route='/phoebe/appforms/index/edit/' type={$class.class_type} }">Edit</td>
									<td style="vertical-align:middle;" width="2%"><code>{$class.class_type}</code></td>
								{/if}
							</tr>
						{/foreach}
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
