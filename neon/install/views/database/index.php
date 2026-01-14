<div id="installer">

	<h4>Core</h4>
	<div v-if="coreInstalled" class="alert alert-success">
		Neon core has been successfully installed
		<!-- @test: database successfully installed -->
	</div>
	<div class="progress" style="margin:0"><div class="progress-bar progress-bar-success" :style="{width:installProgress+'%'}" ></div></div>
	<table class="table">
		<tr v-for="app in install.core" :class="{'success': app.status == 'success', 'danger': app.status == 'error'}">
			<td>
				<div @click="app.expand = !app.expand">{{app.name}}</div>
				<div style="width:625px;" v-show="app.expand">
					<pre >{{app}}</pre>
				</div>
			</td>
			<td style="width:24px">
				<i class="fa fa-check-circle-o" v-show="app.status == 'success'"></i>
				<i class="fa fa-circle-o " v-show="app.status == 'todo'"></i>
			</td>
		</tr>
	</table>
	<h4>Apps</h4>
	<table class="table">
		<tr v-for="app in install.apps" :class="{'success': app.status == 'success', 'danger': app.status == 'error'}">
			<td >
				<div @click="app.expand = !app.expand">{{app.name}}</div>
				<div style="width:625px;" v-show="app.expand">
					<pre >{{app}}</pre>
				</div>
			</td>
			<td style="width:24px">
				<i class="fa fa-check-circle-o text-success" v-show="app.status == 'success'"></i>
				<i class="fa fa-circle-o" v-show="app.status == 'todo'"></i>
				<i class="fa fa-exclamation-circle text-danger" v-show="app.status == 'error'"></i>
			</td>
		</tr>
	</table>
	<div class="clearfix" style="margin-top:20px;">
		<a :disabled="!coreInstalled" @click="moveOn" href="<?= \neon\core\helpers\Url::toRoute(['/install/account/index']) ?>" class="btn btn-primary btn-disabled" data-cmd="create_admin_account">
			Create an Admin User Account
			<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
		</a>
	</div>
</div>

<?php \neon\core\helpers\Page::jsBegin() ?>
<script>
window.neonInstaller = new Vue({
	el: '#installer',
	data: function() {
		var migrations =  <?php echo json_encode($migrations) ?>;
		var install = {core: {}, apps:{}};
		// Creates a sub object for each app that monitors the state of the install
		var createInstallItem = function(appName) {
			return {
				// the app name
				name: appName,
				// whether installation needs to be done
				todo: true,
				// whether the app installed successfully
				status: 'todo',
				// whether to show expanded information on the apps install
				expand: false,
				// an error object - if the install errored
				error: {},
				// the migrations that were ran
				migrations: []
			};
		};
		_.each(migrations.core, function(item, appName) {
			// create a small object to manage the state of each apps install
			install.core[appName] = createInstallItem(appName);
		});
		_.each(migrations.apps, function(item, appName) {
			// create a small object to manage the state of each apps install
			install.apps[appName] = createInstallItem(appName);
		});
		return {
			migrations: migrations,
			install: install
		}
	},
	methods: {
		moveOn: function(e) {
			// prevent moving on unless the core is installed
			if (!this.coreInstalled) {
				e.preventDefault();
			}
		},
		_installApps: function (apps, deferred) {
			var vm = this;
			var app = _.find(apps, function(item) {return item.todo === true});
			if (_.isUndefined(app)) {
				deferred.resolve();
				return;
			}
			$.post({url:neon.url('/install/database/migrations', {app: app.name}), dataType: 'json'})
				.done(function(response){vm.onAppInstallSuccess(app, response);})
				.fail(function(response){vm.onAppInstallError(app, response);})
				.always(function() {app.todo = false; vm._installApps(apps, deferred);});
		},
		installApps: function (apps) {
			var def = $.Deferred();
			this._installApps(apps, def);
			return def.promise();
		},
		onAppInstallError: function(app, response) {
			app.status = 'error';
			app.error = response.responseJSON;
		},
		onAppInstallSuccess: function(app, response) {
			if (response.success) {
				app.status = 'success';
				app.migrations = response.migrations;
			}
		},
		installCore: function() {
			vm = this;
			this.installApps(vm.install.core).then(function(status) {
				vm.installApps(vm.install.apps)
			})
		}
	},
	computed: {
		coreInstalled: function () {
			var items = _.filter(this.install.core, function(item){return item.todo === true}).length;
			return items === 0;
		},
		installProgress: function () {
			var total = _.keys(this.install.core).length;
			var done = _.filter(this.install.core, function(item){return item.todo === false}).length;
			console.log(done/total * 100);
			return done/total * 100;
		}
	},
	mounted: function() {
		this.installCore();
	}
})
</script>
<?php \neon\core\helpers\Page::jsEnd()  ?>
