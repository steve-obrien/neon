

<pre>{$classes|print_r}</pre>

<table class="table">
	<tr>
		<th>Content Type</th>
	</tr>
{foreach $classes as $class}
	<tr>
		<td><a href="{url route='/cms/data/edit/' type={$class.class_type} }">{$class.label}</td>
	</tr>
{/foreach}
</table>