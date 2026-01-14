<h3>{$class.label}</h3>
<p>Class Type: <code>{$class.class_type}</code>. Table name: <code>ddt_{$class.class_type}</code>.</p>
<p>Description: {$class.description}</p>

<table class="table table-striped">
	<thead>
	<tr>
		<th style='width:10%'>Field</th>
		<th style='width:10%'>Label</th>
		<th style='width:10%'>Type</th>
		<th style='width:30%'>Description</th>
		<th style='width:30%'>Choices</th>
		<th style='width:10%'>Links to</th>
	</tr>
	</thead>
	<tbody>
    {foreach from=$class.members item=member key=ref}
		<tr>
			<td style='width:10%'><code>{$member.member_ref}</code></td>
			<td style='width:10%'>{$member.label}</td>
			<td style='width:10%'>{$member.data_type.label} <code>{$member.data_type.data_type_ref}</code></td>
			<td style='width:30%'>{$member.description}</td>
			<td style='width:30%'>
                {if  ( $member.choices and count($member.choices))}
					<ul>
                        {foreach $member.choices as $key => $choice}
							<li><code>{$key}</code> - {$choice}</li>
                        {/foreach}
					</ul>
                {/if}
			</td>
			<td style='width:10%'>{$member.links_to_table}</td>
		</tr>
    {/foreach}
	</tbody>
</table>
