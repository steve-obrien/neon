<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use neon\core\helpers\File;

class Api extends \Codeception\Module
{
	public function getWebRoot()
	{
		return DIR_TEST . '/_root/public';
	}

	public function _beforeSuite($settings = [])
	{
		// clear the cache
		File::removeDirectory(DIR_TEST . '/_root/var');
		// clear system dir
		File::removeDirectory(DIR_TEST . '/_root/system');
		// Install the database
		neon_test_database_create();
		// launch test server
		neon_test_server_start('api_test_server', $this->getWebRoot());
	}

	public function _afterSuite()
	{
		neon_test_server_stop('api_test_server');
	}
}
