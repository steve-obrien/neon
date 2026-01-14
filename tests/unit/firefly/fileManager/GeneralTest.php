<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 23/11/2016 02:46
 * @package neon
 */

namespace newicon\tests\unit\firefly\fileManager;

use \neon\core\test\Unit;
use \neon\core\web\UploadedFile;
use neon\firefly\services\fileManager\FileExceedsMemoryLimitException;
use neon\firefly\services\fileManager\FileNotFoundException;

/**
 * ✔ save()
 * ✔ saveFile()
 * ✔ exists()
 * ✔ read()
 * ✔ getMeta()
 * getUrl()
 * ✔ getVisibility()
 * ✔ setVisibility()
 * copy()
 * delete()
 * ✔ destroy()
 * restore()
 * prepend()
 * append()
 * ✔ getFileName()
 * ✔ setFileName()
 * ✔ getFileHash()
 * ✔ getSize()
 */
class General extends Unit
{
	/**
	 * @var \neon\firefly\services\fileSystem\interfaces\IFileSystem
	 */
	public $drive;

	public $root = '@runtime/tests';

	public function setUp():void
	{
		$path = neon()->getAlias($this->root);
		@mkdir($path);
		neon()->firefly->driveManager->default = 'media';
		neon()->firefly->driveManager->drives['media'] = [
			'driver' => 'local',
			'root' => $this->root
		];
		$this->drive = neon()->firefly->drive('media');
	}

	public function testSaveContent()
	{
		$uuid = neon()->firefly->save('Hello there im some test content', 'hello-test.txt');
		$this->assertTrue(is_string($uuid) && strlen($uuid) == 22, 'We got a uuid back');
		$read = neon()->firefly->read($uuid);
		$this->assertTrue($read == 'Hello there im some test content');
		$this->checkMeta($uuid, [
			'name' => 'hello-test.txt',
			'mime_type' => 'text/plain',
			'size' => 32
		]);
	}

	public function testSaveStream()
	{
		$this->drive->put('/large-test-file', 'some large file - TODO: actually make this a large file');
		$stream = $this->drive->readStream('/large-test-file');
		$uuid = neon()->firefly->save($stream, 'my-stream-file.txt');
		$this->assertTrue(is_string($uuid) && strlen($uuid) == 22, 'We got a uuid back');

		$this->checkMeta($uuid, [
			'name' => 'my-stream-file.txt',
			'mime_type' => 'text/plain'
		]);
	}

	public function testSaveUploadedFile()
	{
		$uploadedFilePath = $this->getUploadedFilePath().'/tmp_name';

		file_put_contents($uploadedFilePath, 'SOME DATA');

		$uploadedFile = new UploadedFile([
			'name' => 'my-uploaded-text-file.txt',
			'tempName' => $uploadedFilePath,
			'type' => '',
			'size' => 123
		]);

		$this->assertTrue(file_exists($uploadedFilePath), 'The uploaded fake file exists');
		$uuid = neon()->firefly->saveFile($uploadedFile);
		$this->assertTrue(is_string($uuid) && strlen($uuid) == 22, 'We got a uuid back');

		$this->checkMeta($uuid, [
			'name' => 'my-uploaded-text-file.txt',
			'mime_type' => 'text/plain'
		]);
	}

	public function testFileHash()
	{
		$uuid = neon()->firefly->save('Hello there im some test content', 'hello-test.txt');
		$meta = neon()->firefly->getMeta($uuid);
		// file hash should not exist
		$this->assertTrue($meta['file_hash'] == null);
		$hash = neon()->firefly->getFileHash($uuid);
		$this->assertTrue(strlen($hash) == 32);
		$meta = neon()->firefly->getMeta($uuid);
		$this->assertTrue($meta['file_hash'] == $hash);
	}

	public function testSetFileName()
	{
		$uuid = neon()->firefly->save('Hello there im some test content', 'hello-test.txt');
		$meta = neon()->firefly->getMeta($uuid);
		// file hash should not exist
		$this->assertTrue($meta['name'] == 'hello-test.txt');
		neon()->firefly->setFileName($uuid, 'MY new name');
		$this->checkMeta($uuid, [
			'name' => 'MY new name'
		]);
		$name = neon()->firefly->getFileName($uuid);
		$this->assertTrue($name == 'MY new name');

		// test FileNoteFound
		$shouldBeFalse = neon()->firefly->setFileName('notfounduuid', 'MY new name');
		$this->assertFalse($shouldBeFalse);
	}

	public function testFileNotFound()
	{
		$doesNotExist = neon()->firefly->read('qqqqqqqqqqqqqqqqqqqqqq');
		$this->assertFalse($doesNotExist);
	}
	
	public function testCopy()
	{
		
	}

	public function testLargeFile()
	{
		$path = neon()->getAlias('@runtime/tests').'/twice-the-size-of-php-memory-limit.txt';
		@mkdir(neon()->getAlias('@runtime/tests'));
		file_put_contents($path, 'placeholder');

		// lets cheat
		// set this to make the tests run a bit faster - remove to use php memory limit as defined at runtime.
		ini_set('memory_limit', '100M');

		// create size as twice php's memory limit - OMG!
		$size = $this->toBytes(ini_get('memory_limit')) * 2;

		$fp = fopen($path, 'w'); // open in write mode.
		fseek($fp, $size - 1, SEEK_CUR); // seek to SIZE-1
		fwrite($fp, '$size'); // write a dummy char at SIZE position
		fclose($fp); // close the file.

		// Add the large file to the file manager
		$file = new \SplFileInfo($path);
		$uuid = neon()->firefly->saveFile($file);
		$this->assertTrue(strlen($uuid) == 22, 'Storage of large file worked');
		// right we have added to firefly lets try to has this huge file
		$hash = neon()->firefly->getFileHash($uuid);
		$this->assertTrue(strlen($hash) == 32, 'Hash of large file worked');

		// check the size within a tollerance
		$tolerancePlusMinusBytes = 50;
		$difference = abs($size - neon()->firefly->getSize($uuid));
		$this->assertTrue($difference <= $tolerancePlusMinusBytes, 'The file is indeed a large un!');

		// should throw an Exception if trying to read this file
		$fileExceedsLimitThrown = false;
		try {
			neon()->firefly->read($uuid);
		} catch (FileExceedsMemoryLimitException $e) {
			$fileExceedsLimitThrown = true;
		}
		$this->assertTrue($fileExceedsLimitThrown, 'Make sure the file exceeds limit IS thrown. You are being dumb and loading in a huge file!');
	}

	public function testSetAndGetVisibility()
	{
		$uuid = neon()->firefly->save('Hello there im some test content', 'hello-test.txt');
		$this->assertTrue(neon()->firefly->setVisibility($uuid, 'public'));
		$this->assertTrue(neon()->firefly->getVisibility($uuid) == 'public');
		$this->assertTrue(neon()->firefly->setVisibility($uuid, 'private'));
		$this->assertTrue(neon()->firefly->getVisibility($uuid) == 'private');
	}

	public function testRead()
	{
		$uuid = neon()->firefly->save('Hello there im some test content', 'hello-test.txt');
		$this->assertTrue(neon()->firefly->read($uuid) == 'Hello there im some test content');
	}

	public function testReadStream()
	{
		// oh lets try on our large file??
		$uuid = neon()->firefly->save('Hello there im some test content', 'hello-test.txt');
		$this->assertTrue(stream_get_contents(neon()->firefly->readStream($uuid)) == 'Hello there im some test content');
	}

	public function testPrepend()
	{
		$prepend = 'my prepended content:';
		$content = 'hello';
		$uuid = neon()->firefly->save($content);
		neon()->firefly->prepend($uuid, $prepend);
		$this->assertEquals($prepend . PHP_EOL . $content, neon()->firefly->read($uuid));
		$anotherPrepend = 'FIRST ';
		neon()->firefly->prepend($uuid, $anotherPrepend, '');
		$this->assertEquals($anotherPrepend . $prepend . PHP_EOL . $content, neon()->firefly->read($uuid));
	}

	public function testAppend()
	{
		$uuid = neon()->firefly->save('First line');
		$this->assertTrue(neon()->firefly->append($uuid, 'Second line'));
		$content = neon()->firefly->read($uuid);
		$lines = explode(PHP_EOL, $content);
		$this->assertTrue($lines[0] == 'First line');
		$this->assertTrue($lines[1] == 'Second line');

		$this->assertEquals(1, neon()->firefly->destroy($uuid));

		$uuid = neon()->firefly->save('First bit');

		$this->assertTrue(neon()->firefly->append($uuid, 'Second bit', ' - '));
		$this->assertTrue(neon()->firefly->read($uuid) == 'First bit - Second bit');
	}

	public function testCreateAndDestroyAFile()
	{
		$uuid = neon()->firefly->save('Hello there im some test content', 'hello-test.txt');
		$meta =  neon()->firefly->getMeta($uuid);
		$this->assertTrue($this->drive->exists($meta['path']), 'check file exists in driver');
		$this->assertTrue(neon()->firefly->exists($uuid), 'File should exist in File Manager');
		$this->assertEquals(1, neon()->firefly->destroy($uuid), 'Destroy should return true');
		$this->assertFalse(neon()->firefly->exists($uuid), 'File should not exist');
		// check file has been removed
		$this->assertFalse($this->drive->exists($meta['path']), 'Driver real file should not exist');
	}

	/**
	 * @param  string  $uuid
	 * @param  array  $metaShouldBe
	 */
	public function checkMeta($uuid, $metaShouldBe)
	{
		$actual = neon()->firefly->getMeta($uuid);
		$metaShouldBe['uuid'] = $uuid;
		foreach($metaShouldBe as $key => $value) {
			$this->assertTrue($actual[$key] == $metaShouldBe[$key]);
		}
	}

	/**
	 * Return the full uploaded file path directory
	 * @return bool|string
	 */
	public function getUploadedFilePath()
	{
		return neon()->getAlias($this->root);
	}

	/**
	 * Test the file custom meta set and get functions
	 */
	public function testMetaSetter()
	{
		$meta = ['test_1' => 1, 'test_2' => 2];
		$uuid = neon()->firefly->save('First line', 'my-file', $meta);
		$fileMeta = neon()->firefly->getMeta($uuid);
		$this->assertEquals($meta, $fileMeta['meta']);
		$expected = ['another'=>'thing', 'crop'=>['width' => 100]];
		neon()->firefly->setMeta($uuid, $expected);
		$actual = neon()->firefly->getMeta($uuid);
		$this->assertEquals($expected, $actual['meta']);

		$shouldBeFalse = neon()->firefly->setMeta('uuid-does-not-exists', []);
		$this->assertFalse($shouldBeFalse);
	}

	public function testFindFile()
	{
		$uuid = neon()->firefly->save('First line', 'my-test-find-file-file');
		$meta = neon()->firefly->getMeta($uuid);
		$foundMeta = neon()->firefly->findFile($meta['drive'], $meta['path']);
		$this->assertEquals($meta, $foundMeta);
		// test the null response
		$shouldBeNull = neon()->firefly->findFile('no drive', '/does/not/exist');
		$this->assertTrue($shouldBeNull === null);

	}

	public function testMetaNotFound()
	{
		$meta = neon()->firefly->getMeta('11324123412341234');
		$this->assertTrue(is_array($meta), 'Should be an empty array');
		$this->assertTrue(empty($meta), 'Should be an empty array');
	}

	public function testReadStreamNotFound()
	{
		$return = neon()->firefly->readStream('11324123412341234');
		$this->assertFalse($return);
	}

}