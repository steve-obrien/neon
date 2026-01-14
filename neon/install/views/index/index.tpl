{*
|-----------------------------------------------------------------------
| View variables
| @var  bool     $installed    - whether the application has already been installed
| @var  integer  $progress     - Integer representing the installation progress step
| @var  array    $requirements - Array of application requirements and messages
| -----------------------------------------------------------------------
*}

<!-- @test: installer welcome message -->
<p>Welcome to the neon installer.</p>

<!-- @test: check requirements -->
{if $requirements.summary.errors == 0}
	<!-- @test: installer requirements success -->
	<div class="alert alert-success">
		<span style="color:green;">&#10004;</span> Your system looks like it should be able to run Neon no problem!
		<a href class="btn btn-default btn-xs pull-right btn-success" onclick="$('#requirements-detail').toggle();return false;">Show me more</a>
	</div>
{else}
	<!-- @test: installer requirements failed -->
	<div class="alert alert-danger">
		<span style="color:red;">&#10008;</span> Ooops! It seems your system does not meet the minimum requirements to run Neon.
	</div>
{/if}

<div id="requirements-detail" style="{($requirements.summary.errors == 0) ? 'display:none' : '' }">
	<table class="table table-bordered">
		<tr>
			<th>System Requirements</th>
			<th></th>
		</tr>
		{foreach $requirements.requirements as $req}
		<tr style="background-color:{if $req.condition == false}{($req.mandatory) ? '#F2DEDE' : '#FFF1D0'}{/if}">
			<td>
				{$req.name}
				{* only show the description if the requirement failed *}
				{if !$req.condition} <br> {$req.memo} {/if}
			</td>
			<td>
				{if $req.condition}
					<span style="color:green;">&#10004;</span>
				{elseif $req.mandatory}
					<span style="color:red;">&#10008;</span>
				{else}
					-
				{/if}
			</td>
		</tr>
		{/foreach}
	</table>
</div>

{if $installed}
    {include file='_installed.tpl' progress=$progress}
{else}
	<p>Before getting started, we need some information on the database. You will need to know the following items before proceeding.</p>
	<ol>
		<li>Database name</li>
		<li>Database username</li>
		<li>Database password</li>
		<li>Database host</li>
		<li>Table prefix (if you want to run more than one neon instance in a single database)</li>
	</ol>
	<p>We’re going to use this information to create a config file called <code>config/env.ini</code> file.
		If for any reason this automatic file creation doesn’t work, don’t worry.
		All this does is fill in the database information to a configuration file. You may also simply open
		<code>config/env.sample.ini</code> in a text editor, fill in your information, and save it as <code>env.ini</code>.
		Need a hand? We're here to help.</p>
	<p>If you’re all ready…</p>
	<hr>
	<a class="btn btn-primary" href="{url route='/install/config'}" data-cmd="start_installation">
		Start Installation
		<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
	</a>
{/if}
