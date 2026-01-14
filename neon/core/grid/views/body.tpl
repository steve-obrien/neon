<div id="{$grid->id}" class="neonGrid {$grid->theme} {(($grid->fillWidth==true)?'neonGrid_fillWidth':'')} neonGrid_nowrap">
	<div class="panel panel-default" >
		<div class="panel-heading" data-header>
			<h3 class="panel-title">
				{$grid->title}
				<i class=" neonSpinner el-icon-loading"></i>
			</h3>
		</div>
		<div class="neonGrid_controls clearfix">
			<div class="neonGrid_right pull-right">
				{$renderer->renderSummary() nofilter}
				<a href data-wrap class="btn btn-default btn-xs" title="wrap text"><i class="fa fa-text-height"></i> Expand</a>
				{if ($grid->canExportCsv)}
					<a data-export class="btn btn-default btn-xs" href="{$grid->exportCsvUrl()}"><i class="fa fa-download"></i> Export</a>
				{/if}
			</div>
			<ul class="neonGrid_scopes">{$renderer->renderScopes() nofilter}</ul>
		</div>
		<div class="panel-body neonGrid_body">
			<neon-core-form-form object-token="{$grid->getFilterForm()->getParentForm()->getObjectToken()}" name="{$grid->id}" id="{$grid->id}Filter">
				<neon-core-form-form name="filter" id="filter" style="padding:0px;">
					<table class="table {($grid->stripedRows) ? 'table-striped' : ''}">
						<thead>
							{$renderer->renderHeaderGroups() nofilter}
							<tr class="neonGrid_headers" data-headers>
								{foreach from=$grid->columns item=column}
									{if $column->visible}
										{$column->renderHeaderCell() nofilter}
									{/if}
								{/foreach}
							</tr>
							{if $grid->filterShow}
								<tr class="neonGrid_filters" data-filters style="height:42px;">
									{foreach from=$grid->columns item=column}
										{if $column->visible}
											{$column->renderFilterCell() nofilter}
										{/if}
									{/foreach}
								</tr>
							{/if}
							{*</neon-core-form-form>*}
						</thead>
						<tbody id="{$grid->id}-rows" class="neonGrid_rows" data-rows>
							{$renderer->renderRows() nofilter}
						</tbody>
					</table>
				</neon-core-form-form>
			</neon-core-form-form>
		</div>
		<div class="panel-footer">
			{$renderer->renderPager() nofilter}
			{$renderer->renderSummary() nofilter}
			{if ($debug)}
				<a class="btn btn-default btn-xs" onclick="$('.datadebug').toggle()"  style="float:right;">Toggle Query</a>
				<div class="datadebug" style="display:none;">
					<textarea readonly class="form-control neonGrid_debug" data-debug>
						{$grid->showQuery() nofilter}
					</textarea>
				</div>
			{/if}
		</div>
	</div>
</div>
