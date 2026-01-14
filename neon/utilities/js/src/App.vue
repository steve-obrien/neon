<template>
	<div id="app" >
		<header class="app_header" style="top: 0px;">
			<h2 class="neonToolbar_title" title="System Report">System Report</h2>
		</header>
		<div class="app_sidebar">
			<div class="list-group">
				{{utilities}}

				<a class="list-group-item" href="<?= url(['/utilities/index/index']) ?>">
					<span class="badge ">1</span>
					<span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span>
					<span class="">Updates</span>
				</a>
				<a class="list-group-item" href="<?= url(['/utilities/index/index']) ?>">
					<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
					<span class="">System Report</span>
					<span class="badge ">1</span>
				</a>
				<a class="list-group-item" href="<?= url(['/utilities/index/index']) ?>">
					<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
					<span class="">PHP Info</span>
					<span class="badge ">1</span>
				</a>
				<a class="list-group-item" href="<?= url(['/utilities/index/index']) ?>">
					<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
					<span class="">Database Info</span>
					<span class="badge ">1</span>
				</a>
				<a class="list-group-item" href="<?= url(['/utilities/index/index']) ?>">
					<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
					<span class="">Clear Caches</span>
					<span class="badge ">1</span>
				</a>
				<a class="list-group-item" href="<?= url(['/utilities/index/index']) ?>">
					<span class="fa fa-warning" aria-hidden="true"></span>
					<span class="">Deprecation Warnings</span>
					<span class="badge ">1</span>
				</a>
				<a class="list-group-item" href="<?= url(['/utilities/index/index']) ?>">
					<span class="fa fa-database" aria-hidden="true"></span>
					<span class="">Database Backup</span>
					<span class="badge "></span>
				</a>
				<a class="list-group-item" href="<?= url(['/utilities/index/index']) ?>">
					<span class="fa fa-arrow-up" aria-hidden="true"></span>
					<span class="">Migrations</span>
					<span class="badge "></span>
				</a>
				<a class="list-group-item" href="<?= url(['/utilities/index/index']) ?>">
					<span class="fa fa-bug" aria-hidden="true"></span>
					<span class="">Errors</span>
					<span class="badge ">3</span>
				</a>
			</div>
		</div>
		<div class="app_content"></div>
	</div>
</template>

<script>

	export default {
		name: 'App',
		props: {
			utilities: {type: Array, default: function() {return [];}}
		},
		components: {
		}
	}
</script>

<style>
	#app {
		display: grid;
		width: 100vw;
		height: calc(100vh - 50px);
		grid-template-areas: 'header header' 'sidebar content';
		grid-template-columns: 250px auto;
		grid-template-rows: 57px auto;
	}
	.app_sidebar {
		padding-bottom: 48px;
		background-color:#fbfcfd;
		box-shadow: inset -1px 0 0 #e3e5e8;
		grid-area: sidebar;
	}
	.app_content {
		grid-area: content;
	}
	.app_header {
		box-shadow: 0 1px 0 rgba(0, 0, 20, 0.1);
		grid-area: header;
		padding-left:20px;
		padding-top:12px;
		background: #e7eef3;
	}

	.list-group-item {font-size:14px; background:transparent;}
	.list-group-item:first-child,.list-group-item:last-child  {border-radius: 0;}
</style>
