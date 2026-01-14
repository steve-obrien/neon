<div class="workbench">
	<header class="workbench-header">
		<div class="workbench-header__left">
			<h1 class="workbench-header__title">{$project} Data Dictionary</h1>
		</div>
		<div class="">
			<a href="{url route='/daedalus/index/index'}"><i class="fa fa-angle-left"></i> Back to database</a>
		</div>
	</header>
	<style>
		.databaseDictionary p  { width: 50%; font-size:10pt; }
		.databaseDictionary_tableList { font-size:10pt; }
	</style>
	<div class="workbench-body">
		<div class="workbench-body__content" >
			<div style="padding:0px 20px;">
				<div class="panel">
					<div class='databaseDictionary' style="padding:5em 10em;">

						{* Database specific tables *}
						<h2>{$project} Specific Database</h2>
						<p>
							Below are all of the tables specifically created for the {$project} database listed in alphabetical order.
							Each section lists the fields on the table, their data types<sup>*</sup> and if they link to other tables in the database (foreign key references).
						</p>
						<p>
							Additional tables in the database are part of the neon framework and are described later.
							<br/><sup>*</sup>Following these is a description of the different data types used in neon.
						</p>
						{foreach from=$definitions item=class key=classType}
							<hr/>
							{include file="./tableDictionary.tpl" class=$class classType=$classType }
						{/foreach}

						{* Description of neon data types *}
						<br/>
						<hr/>
						<h2>Overview of Neon Tables</h2>
						<p>
							The neon framework consists of several database tables sorted by module.
							{foreach from=$neonDatabase item=$module}
								<h3>{$module.name}</h3>
								<p>
									{$module.description}
								</p>
								<ul class='databaseDictionary_tableList'>
								{foreach from=$module.tables item=$table}
									<li>{$table.name} - {$table.description}</li>
								{/foreach}
								</ul>
								</hr>
							{/foreach}
						</p>

						{* Description of neon data types *}
						<br/>
						<hr/>
						<h2>Neon Data Types</h2>
						<p>
							The following data types are used within neon.
						</p>
						<ul>
							{foreach from=$dataTypes item=$dataType}
								<li>{$dataType.label} - {$dataType.description}
							{/foreach}
						</ul>

					</div>
				</div>
			</div>
		</div>
	</div>
</div>

