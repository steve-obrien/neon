<div class="workbench">
	<header class="workbenchHeader">
		<div class="workbenchHeader_left">
			<h1 class="workbenchHeader_title">Database Index</h1>
		</div>
		<div class="workbenchHeader_right">
			<a href="{url route='/daedalus/index/data-dictionary/'}" class="btn btn-primary btn-primary-addition mls" data-cmd="dictionary"><i class="fa fa-database"></i> Data Dictionary</a>
			<a href="{url route='/daedalus/change-log/list-all'}" class="btn btn-primary btn-primary-addition mls" data-cmd="change_log"><i class="fa fa-history"></i> Change Log</a>
			{if ($can_develop)}
				<a href="{url route='/phoebe/database/index/edit/'}" class="btn btn-danger btn-primary-addition mls" data-cmd="create_table"><i class="fa fa-plus-circle"></i> Create Table</a>
			{/if}
		</div>
	</header>
	<div class="workbenchBody">
		<div class="workbenchBody_content" >
			<div class="panel panelHasTable">
				<table class="table">
					<thead>
						<tr>
							<th style="text-align:left;width:2%">No.</th>
							<th style="width:200px">Database Table</th>
							{if ($can_develop)}
								<th style="width:200px">Table Name</th>
							{/if}
							<th>Description</th>
							<th style="text-align:left">Actions</th>
					</tr>
					</thead>
					<tbody>
					{foreach $classes as $class}
						<tr>
							<td style="text-align:left"><code>{$class.count_current}</code></td>
							<td style="width:200px">
								<a href="{url route='/daedalus/index/list/' type={$class.class_type} }">{$class.label|default:{$class.class_type|humanize:true}}</a>
							</td>
							{if ($can_develop)}
								<td style="width:200px"><code>{$class.class_type}</code></td>
							{/if}
							<td>{$class.description}</td>
							<td style="text-align:left; width:10em;">
								<a class="fa fa-eye" title='View Table' href="{url route='/daedalus/index/list/' type={$class.class_type} }"></a>&nbsp;
								<a class="fa fa-plus" title='Add Object' href="{url route='/daedalus/index/add-object/' type={$class.class_type} }"></a>&nbsp;
								{if ($class.change_log)}
									<a class="fa fa-history" title='Table Change Log' href="{url route='/daedalus/change-log/list/' type={$class.class_type} }"></a>
								{/if}
							</td>
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
