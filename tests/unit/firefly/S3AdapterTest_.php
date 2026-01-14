<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 23/11/2016 02:46
 * @package neon
 */

namespace newicon\tests\unit\firefly;

use \neon\tests\unit\firefly\LocalAdapterTest;

class S3AdapterTest extends LocalAdapterTest
{
	public function setUp():void
	{
		if (env('AMAZON_S3_KEY') == false) {
			$this->markTestSkipped('Amazon connection details have not been provided');
		}
		neon()->firefly->driveManager->default = 's3';
		neon()->firefly->driveManager->drives['s3'] = [
			'driver' => 's3',
			'key' => env('AMAZON_S3_KEY'),
			'secret' => env('AMAZON_S3_SECRET'),
			'region' => 'eu-west-1',
			'bucket' => 'newicon',
			'prefix' => '/tests/'
		];
		$this->drive = neon()->firefly->drive('s3');
	}

	public function testGetName()
	{
		$this->assertTrue($this->drive->getName() == 's3');
	}

	public function testListContents()
	{
		$this->assertTrue(empty($this->drive->listContents('/tests/', true)), 'Make sure the list is empty');

		$this->assertTrue($this->drive->put('/tests/path/to/somefile.txt', 'list contents test'));
		$this->assertTrue($this->drive->put('/tests/diff/a-different-file.txt', 'contents'));
		$this->assertTrue($this->drive->put('/tests/other.txt', 'contents'));

		$list = $this->drive->listContents('', true);
		$this->assertTrue(count($list) >= 3, 'Test the list has 3 or more files');
		foreach($list as $fileInfo) {
			$this->assertTrue(
				   ($fileInfo['path'] == '/tests/path' && $fileInfo['type'] == 'dir')
				|| ($fileInfo['path'] == '/tests/path/to' && $fileInfo['type'] == 'dir')
				|| ($fileInfo['path'] == '/tests/path/to/somefile.txt' && $fileInfo['type'] == 'file')
				|| ($fileInfo['path'] == '/tests/diff' && $fileInfo['type'] == 'dir')
				|| ($fileInfo['path'] == '/tests/diff/a-different-file.txt' && $fileInfo['type'] == 'file')
				|| ($fileInfo['path'] == '/tests/other.txt' && $fileInfo['type'] == 'file')
			);
		}
		// tidy up
		foreach($list as $fileInfo) {
			if ($fileInfo['type'] == 'dir') {
				$this->assertTrue($this->drive->deleteDirectory($fileInfo['path']));
			} else {
				$this->assertTrue($this->drive->delete($fileInfo['path']));
			}
		}

		$this->assertTrue(empty($this->drive->listContents('', true)), 'List contents should be empty');
	}

}