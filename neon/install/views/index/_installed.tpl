<div class="alert alert-info">
	<p>Neon appears to already be installed!</p>
	<p>You will have to delete the database tables in order to re run this part of the installation process.</p>
</div>

<hr>

{if $progress == 4}
	<a class="btn btn-default" href="{url route='/user/account/login'}">Log In</a>
{/if}

<a class="btn btn-primary" href="{url route='/install/config/index'}">Next
	<span class="glyphicon glyphicon-chevron-right"></span>
</a>