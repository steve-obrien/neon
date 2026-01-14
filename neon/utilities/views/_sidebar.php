<!--<a class="list-group-item --><?//= is_route('/utilities/index*') ? 'active' : '' ?><!--" href="--><?//= url(['/utilities/index/index']) ?><!--">-->
<!--	<span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span>-->
<!--	<span class="utility">Updates</span>-->
<!--	<span class="badge ">1</span>-->
<!--</a>-->
<a class="list-group-item <?= is_route('/utilities/system/report') ? 'active' : '' ?>" href="<?= url(['/utilities/system/report']) ?>">
	<svg style="width:16px;" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M5 13l4 4L19 7"></path></svg>
	<span class="utility">System Report</span>
</a>
<a class="list-group-item <?= is_route('/utilities/system/phpfpm') ? 'active' : '' ?>" href="<?= url(['/utilities/system/phpfpm']) ?>">
	<svg style="width:16px;" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M5 13l4 4L19 7"></path></svg>
	<span class="utility">PHP FPM</span>
</a>
<!--<a class="list-group-item" href="--><?//= url(['/utilities/index/index']) ?><!--">-->
<!--	<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>-->
<!--	<span class="utility">PHP Info</span>-->
<!--</a>-->
<!--<a class="list-group-item" href="--><?//= url(['/utilities/index/index']) ?><!--">-->
<!--	<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>-->
<!--	<span class="utility">Database Info</span>-->
<!--</a>-->
<a class="list-group-item <?= is_route('/utilities/clear-cache*') ? 'active' : '' ?>" href="<?= url(['/utilities/clear-cache']) ?>">
	<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
	<span class="utility">Clear Caches</span>
	<span class="badge "></span>

</a>
<!--<a class="list-group-item" href="--><?//= url(['/utilities/index/index']) ?><!--">-->
<!--	<span class="fa fa-warning" aria-hidden="true"></span>-->
<!--	<span class="utility">Deprecation Warnings</span>-->
<!--</a>-->
<a class="list-group-item <?= is_route('/utilities/db-backup*') ? 'active' : '' ?>" href="<?= url(['/utilities/db-backup/index']) ?>">
	<span class="fa fa-database" aria-hidden="true"></span>
	<span class="utility">Database Backup</span>
	<span class="badge "></span>
</a>
<!--<a class="list-group-item" href="--><?//= url(['/utilities/index/index']) ?><!--">-->
<!--	<span class="fa fa-arrow-up" aria-hidden="true"></span>-->
<!--	<span class="utility">Migrations</span>-->
<!--	<span class="badge "></span>-->
<!--</a>-->
<a class="list-group-item <?= is_route('/utilities/error*') ? 'active' : '' ?>" href="<?= url(['/utilities/error/index']) ?>">
	<span class="fa fa-bug" aria-hidden="true"></span>
	<span class="utility">Errors</span>
	<span class="badge "></span>
</a>
