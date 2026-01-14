<h4>Software Update</h4>

<div id="neon-versions"></div>

<?php \neon\core\widgets\Js::begin() ?>

<script>
// get neon versions
new Vue({
	data: function() {
		return {
			error: false,
			loaded: false,
			versions: [],
			current: '',
			newer: false,
			latest: ''
		}
	},
	mounted: function() {
		var me = this;
		// ajax call to get the latest versions
		$.getJSON(neon.url('/core/api/version')).done(function(response) {
			console.log(response);
			me.versions = response.versions;
			me.current = response.current;
			me.newer = response.newer;
			me.loaded = true;
		})
		.fail(function() {
			me.loaded = false;
			me.error = true;
		})
	},
	template: `
		<div>
			<template v-if="loaded">
				<div v-if="newer.length === 0" class="alert alert-success text-center" role="alert">
					<h4 class="alert-heading">Neon {{current}}</h4>
					<p class="mb-0">You are on the latest neon version.</p>
				</div>
				<div v-else class=" alert  alert-warning text-center" role="alert">
					<h4 class="alert-heading">Neon {{current}} upgrade to {{latest}}</h4>
					<p class="mb-0">You are {{newer.length}} version{{newer.length > 1 ? 's' : ''}} behind the latest stable neon version</p>
				</div>
			</template>
			<div v-else class=" alert  text-center" :class="{'alert-danger': error}" role="alert">
				<span v-if="!error"><i class="el-icon-loading"></i> Loading versions...</span>
				<span v-if="error" class="text-danger"><i class="el-icon-warning"></i> Failed to load version information</span>
			</div>
		</div>
	`
}).$mount('#neon-versions');
</script>
<?php \neon\core\widgets\Js::end(); ?>
