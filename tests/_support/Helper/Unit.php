<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use neon\core\helpers\File;

class Unit extends \Codeception\Module
{
	public function _beforeSuite($settings = [])
	{
		// clear the cache
		File::removeDirectory(DIR_TEST . '/_root/var');
		// clear system dir
		File::removeDirectory(DIR_TEST . '/_root/system');
		// Install the database
		neon_test_database_create();
	}

	public function _afterSuite()
	{
	}
}
