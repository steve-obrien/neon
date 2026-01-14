
<div class="workbench">
	<header class="workbenchHeader">
		<h1 class="workbenchHeader_title">Cobe CMS</h1>
		<div class="toolbar">
		</div>
	</header>
	<div class="workbenchBody">
		<div class="workbenchBody_sidebar"></div>
		<div class="workbenchBody_content">

			<div class="neonCard">
                {$cmpForm->run()}
			</div>

		</div>
	</div>
</div>

{css}
	<style>
		.workbench { background-color: #F4F6F9; }
		.workbenchBody { display:flex; }
		.workbenchBody_sidebar { flex: 0 0 260px; background-color: #E3E7ED; }
		.workbenchBody_content { background: #EFF2F6 }
		.neonCard { padding:50px; margin:20px 50px; width: 900px; background-color: #FFFFFF; box-shadow: 0 13px 60px 0 rgba(97,99,128,0.14); border-radius: 8px; }
	</style>
{/css}