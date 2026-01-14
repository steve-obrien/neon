<?php

namespace neon\tests\unit\firefly;

use Carbon\Carbon;
use \DateTime;
use \neon\core\helpers\Arr;
use \neon\core\test\Unit;
use \League\Flysystem\FileNotFoundException;

/**
 * Class LocalAdapterTest
 * Test the local adapter interface:
 *
 * All functions:
 *
 * ✔ read
 * ✔ readStream
 * ✔ listContents
 * ✔ listFiles
 * ✔ listDirectories
 * ✔ getName
 * ✔ getSize
 * ✔ getMimeType
 * ✔ getLastModified
 * ✔ getVisibility
 * ✔ getMeta
 * ✔ exists
 * ✔ put
 * ✔ setVisibility
 * ✔ move
 * ✔ copy
 * ✔ prepend
 * ✔ append
 * ✔ createDirectory
 * ✔ delete
 * ✔ deleteDirectory
 *
 * x getDriver
 */
class LocalAdapterTest extends Unit
{

	/**
	 * @var \neon\firefly\services\driveManager\interfaces\IFileSystem
	 */
	public $drive;

	public function setUp():void
	{
		neon()->firefly->driveManager->default = 'local';
		neon()->firefly->driveManager->drives['local'] = [
			'driver' => 'local',
			'root' => '@runtime/tests'
		];
		$this->drive = neon()->firefly->driveManager->drive();
	}

	public function testRead()
	{
		$file = 'path/to/somefile.txt';
		$content = 'My content';
		$this->assertTrue($this->drive->put($file, $content), 'Test successful put');
		$this->assertTrue($this->drive->read($file) == $content, 'Test reading the file contents is correct');
		$this->assertTrue($this->drive->exists($file), 'Test file exists');
		$this->assertTrue($this->drive->delete($file), 'Test the delete was successful (returned true)');
		$this->assertFalse($this->drive->exists($file), 'The file should no longer exist');
		// not all drivers support full directory support
		// lets test if the directory exists
		if ($this->drive->exists('path/to')) {
			$this->assertTrue($this->drive->deleteDirectory('path/to'), 'Test deleting the directory "path/to" returns true');
		}
		if ($this->drive->exists('path')) {
			$this->assertTrue($this->drive->deleteDirectory('path'), 'Test deleting the directory "path" returns true');
		}
	}

	public function testMetaNotFound()
	{
		$this->expectException(FileNotFoundException::class);
		$result = $this->drive->getMeta('doesnotexist!');
	}

	public function testMeta()
	{
		$this->drive->put('meta.test.file', '123');
		$meta = $this->drive->getMeta('meta.test.file');
		$this->assertArrayHasKey('type', $meta, 'check type key exists');
		$this->assertArrayHasKey('path', $meta, 'check path key exists');
		$this->assertArrayHasKey('timestamp', $meta, 'check timestamp key exists');
		$this->assertArrayHasKey('size', $meta, 'check size key exists');
		$this->assertTrue($meta['type'] == 'file', 'check file == ' . $meta['type']);
		$this->assertTrue($meta['path'] == 'meta.test.file', 'check path == ' . $meta['path']);
		$this->assertTrue($meta['size'] == 3, 'check size == 3');
		$this->drive->delete('meta.test.file');
	}

	public function testExists()
	{
		$file = 'Exists.txt';
		$this->assertTrue($this->drive->put($file, 'Exists test'));
		$this->assertTrue($this->drive->exists($file));
		$this->assertTrue($this->drive->delete($file));
		$this->assertFalse($this->drive->exists($file));
		$this->assertFalse($this->drive->exists('path/to/somefile_DOES_NOT_EXIST.txt'));
	}

	public function testReadFile()
	{
		$file = 'Read test.txt';
		$content = 'My file content!';
		$this->assertTrue($this->drive->put($file, $content));
		$this->assertTrue($this->drive->read($file) == $content);
		$this->assertTrue($this->drive->delete($file));
		$this->assertFalse($this->drive->exists($file));
	}

	public function testCopy()
	{
		if ($this->drive->exists('new.txt'))
			$this->drive->delete('new.txt');
		if ($this->drive->exists('new.copy.txt'))
			$this->drive->delete('new.copy.txt');

		$this->drive->put('new.txt', 'New');
		$this->drive->copy('new.txt', 'new.copy.txt');
		$this->assertEquals('New', $this->drive->read('new.copy.txt'));

		$this->drive->delete('new.txt');
		$this->drive->delete('new.copy.txt');
	}

	public function testGetName()
	{
		$this->assertTrue($this->drive->getName() == 'local');
	}

	/**
	 * Test an exception is thrown when requesting a non existent drive
	 */
	public function testFailWhenNoDrive()
	{
		$exceptionThrown = false;
		try {
			neon()->firefly->driveManager->drive('made_up_drive')->put('myfile.txt', 'bla bla');
		} catch (\Exception $e) {
			$exceptionThrown = true;
		}
		$this->assertTrue($exceptionThrown);
	}

	public function testMimeType()
	{
		$file = '/tests/Read test.txt';
		$content = 'more content';
		$this->assertTrue($this->drive->put($file, $content));
		$this->assertTrue($this->drive->getMimeType($file) == 'text/plain');
		$this->assertTrue($this->drive->delete($file));
		$this->assertFalse($this->drive->exists($file));
		// not all drivers support a full directory interface - directories are second class
		if ($this->drive->exists('/tests')) {
			$this->assertTrue($this->drive->deleteDirectory('/tests'));
		}
	}

	public function testFileNotFoundException()
	{
		$this->expectException(FileNotFoundException::class);
		$this->drive->getMimeType('path/to/somefile_DOES_NOT_EXIST.txt');
	}

	public function testFileNotFoundExceptionVisibility()
	{
		$this->expectException(FileNotFoundException::class);
		$this->drive->setVisibility('path/to/somefile_DOES_NOT_EXIST.txt', 'public');
	}

	public function testListContents()
	{
		// Lets delete everything first as other tests leave crap lieing around
		foreach($this->drive->listContents('', true) as $item) {
			if ($item['type'] == 'file') {
				$this->drive->delete($item['path']);
			} else {
				$this->drive->deleteDirectory($item['path']);
			}
		}
		$this->assertTrue(empty($this->drive->listContents('', true)), 'Make sure the list is empty');
		$this->assertTrue($this->drive->put('path/to/somefile.txt', 'list contents test'));
		$list = $this->drive->listContents('', true);
		$this->assertEquals(['type' => 'dir', 'path' => 'path'], Arr::only($list[0], ['type', 'path']));
		$this->assertEquals(['type' => 'dir', 'path' => 'path/to'], Arr::only($list[1], ['type', 'path']));
		$this->assertEquals(['type' => 'file', 'path' => 'path/to/somefile.txt'], Arr::only($list[2], ['type', 'path']));
		// test listing path with beginning slash and without beginning slash
		$list =$this->drive->listContents('/path/to');
		$this->assertEquals(['type' => 'file', 'path' => 'path/to/somefile.txt'], Arr::only($list[0], ['type', 'path']));
		// trailing slash
		$list = $this->drive->listContents('path/to/');
		$this->assertEquals(['type' => 'file', 'path' => 'path/to/somefile.txt'], Arr::only($list[0], ['type', 'path']));
		$this->assertTrue($this->drive->delete('path/to/somefile.txt'));
		$this->assertFalse($this->drive->exists('path/to/somefile.txt'));
	}

	public function testSingleWritePublic()
	{
		$file = 'some/place/myfile.txt';
		$this->assertTrue($this->drive->put($file, 'contents', 'public'), 'Put a public file');
		$this->assertTrue($this->drive->exists($file));
		$this->assertTrue($this->drive->getVisibility($file) == 'public', 'check the visibility is set to public');
		$this->assertTrue($this->drive->delete($file));
		$this->assertFalse($this->drive->exists($file));
		if ($this->drive->exists('some/')) {
			$this->assertTrue($this->drive->deleteDirectory('some/'));
		}
		$this->assertFalse($this->drive->exists('some/place'));
	}

	public function testChangeVisibility()
	{
		$file = 'some/place/visibility.txt';
		$this->assertTrue($this->drive->write($file, 'contents'));
		$this->assertTrue($this->drive->setVisibility($file, 'private'));
		$this->assertTrue($this->drive->getVisibility($file) == 'private');
		$this->assertTrue($this->drive->delete($file));
		$this->assertFalse($this->drive->exists($file));
		if ($this->drive->exists('some/place')) {
			$this->assertTrue($this->drive->deleteDirectory('some/place'));
			$this->assertTrue($this->drive->deleteDirectory('some'));
		}
	}

	public function testSingleWritePrivate()
	{
		$this->assertTrue($this->drive->put('SOME PRIVATE.txt', 'contents', 'private'));
		$this->assertTrue($this->drive->getVisibility('SOME PRIVATE.txt') == 'private');
		$this->assertTrue($this->drive->delete('SOME PRIVATE.txt'));
		$this->assertFalse($this->drive->exists('SOME PRIVATE.txt'));
	}

	public function testFiles()
	{
		// lets add a new file in the mix for poops and giggles
		$this->assertTrue($this->drive->put('path/xxx.txt', 'Ooooh xxx content'));
		$this->assertTrue($this->drive->put('path/to/xxx.txt', 'Ooooh xxx content'));
		$this->assertTrue($this->drive->put('path/to/another.txt', 'Ooooh more xxx content'));
		// recursive search and only return files
		$list = $this->drive->listFiles('', true);
		$this->assertEquals($list, ['path/to/another.txt', 'path/to/xxx.txt', 'path/xxx.txt']);
		$list = $this->drive->listFiles('path/to');
		$this->assertEquals($list, ['path/to/another.txt', 'path/to/xxx.txt']);
		$this->assertTrue($this->drive->delete('path/xxx.txt'));
		$this->assertTrue($this->drive->delete('path/to/xxx.txt'));
		$this->assertTrue($this->drive->delete('path/to/another.txt'));
		$this->assertFalse($this->drive->exists('path/xxx.txt'));
		$this->assertFalse($this->drive->exists('path/to/xxx.txt'));
		$this->assertFalse($this->drive->exists('path/to/another.txt'));
	}

	public function testSize()
	{
		$this->assertTrue($this->drive->put('path/xxx.txt', 'Ooooh xxx content'));
		$this->assertEquals($this->drive->getSize('path/xxx.txt'), 17);
		$this->assertTrue($this->drive->delete('path/xxx.txt'));
		$this->assertFalse($this->drive->exists('path/xxx.txt'));
		if ($this->drive->exists('path')) {
			$this->assertTrue($this->drive->deleteDirectory('path'));
		}
	}

	public function testLastModified()
	{
		if ($this->drive->exists('timestamp-file.txt')) {
			$this->drive->delete('timestamp-file.txt');
		}
		$this->assertFalse($this->drive->exists('timestamp-file.txt'), 'The timestamp-file.txt should not exist!');

		$before = (new \DateTime('now'))->getTimestamp();
		// Amend the file in order to update the last modified value
		$this->drive->put('timestamp-file.txt', 'bla bla bla');
        $lastModified = $this->drive->getLastModified('timestamp-file.txt');
		$after = (new \DateTime('now'))->getTimestamp();
		// the interface commands only give us a timestamp and so we have a tolerance of a second.
		// last modified should be between the before and after dates
		$this->assertTrue($lastModified >= $before, 'Last modified should be a date after before');
        $this->assertTrue($lastModified <= $after, 'Last modified should be before after');
		// lets clean up the mess
		$this->assertTrue($this->drive->delete('timestamp-file.txt'), 'Test the delete returned boolean true "thinks it successfully deleted"');
		$this->assertFalse($this->drive->exists('timestamp-file.txt'), 'The file should not exist');

	}

	public function testUrl()
	{
//		$this->drive->put('new-public-file.txt', 'bla bla bla', 'public');
//		neon()->firefly->drive('public')->put('new-public-file.txt', 'bla bla bla', 'public');
//		neon()->firefly->drive('public')->delete('new-public-file.txt');
//		$this->drive->delete('new-public-file.txt');
//		dd(neon()->firefly->drive('public')->url('new-public-file.txt'));
	}

	public function testMove()
	{
		$this->assertTrue($this->drive->put('path/to/a-different-file.txt', 'content'), 'Put new file "path/to/a-different-file.txt"');
		$this->assertTrue($this->drive->exists('path/to/a-different-file.txt'), 'make sure "path/to/a-different-file.txt" exists');
		$this->assertTrue($this->drive->move('path/to/a-different-file.txt', 'moved/into/here.txt'), 'Whether the move command was successful');
		$this->assertTrue($this->drive->read('moved/into/here.txt') == 'content', 'The content of the moved file is the same');
		// check the original one has changed
		$this->assertFalse($this->drive->exists('path/to/a-different-file.txt'), 'The previously moved file should not exist');
		// clean up this one test
		$this->assertTrue($this->drive->exists('moved/into/here.txt'));
		$this->assertTrue($this->drive->delete('moved/into/here.txt'));
		$this->assertFalse($this->drive->exists('moved/into/here.txt'));
		if ($this->drive->exists('moved', 'Check moved folder exists')) {
			$this->assertTrue($this->drive->deleteDirectory('moved/into'));
			$this->assertTrue($this->drive->deleteDirectory('moved'));
		}
	}

	/**
	 * The delete function accepts multiple arguments as file paths to delete and array of files to delete
	 */
	public function testDeleteFiles()
	{
		// Test deleting files by passing multiple arguments to delete function

		$this->drive->put('a-file-to-delete-1.txt', 'content');
		$this->drive->put('a-file-to-delete-2.txt', 'content');
		$this->drive->put('a-file-to-delete-3.txt', 'content');

		$this->assertTrue($this->drive->exists('a-file-to-delete-1.txt'));
		$this->assertTrue($this->drive->exists('a-file-to-delete-2.txt'));
		$this->assertTrue($this->drive->exists('a-file-to-delete-3.txt'));

		$this->drive->delete('a-file-to-delete-1.txt', 'a-file-to-delete-2.txt', 'a-file-to-delete-3.txt');

		$this->assertTrue(!$this->drive->exists('a-file-to-delete-1.txt'));
		$this->assertTrue(!$this->drive->exists('a-file-to-delete-2.txt'));
		$this->assertTrue(!$this->drive->exists('a-file-to-delete-3.txt'));

		// Test delete function passing in an array

		$this->drive->put('a-file-to-delete-1.txt', 'content');
		$this->drive->put('a-file-to-delete-2.txt', 'content');
		$this->drive->put('a-file-to-delete-3.txt', 'content');

		$this->assertTrue($this->drive->exists('a-file-to-delete-1.txt'));
		$this->assertTrue($this->drive->exists('a-file-to-delete-2.txt'));
		$this->assertTrue($this->drive->exists('a-file-to-delete-3.txt'));

		$this->drive->delete(['a-file-to-delete-1.txt', 'a-file-to-delete-2.txt', 'a-file-to-delete-3.txt']);

		$this->assertTrue(!$this->drive->exists('a-file-to-delete-1.txt'));
		$this->assertTrue(!$this->drive->exists('a-file-to-delete-2.txt'));
		$this->assertTrue(!$this->drive->exists('a-file-to-delete-3.txt'));

	}

	public function testDeleteDirectory()
	{
		// lets create a few files:
		$this->drive->put('path/to/a-different-file.txt', 'content');
		$this->drive->put('path/to/other/crazy/a-different-file.txt', 'content');
		$this->drive->put('path/to/another/a-different-file.txt', 'content');

		$this->assertTrue($this->drive->exists('path/to/a-different-file.txt'));
		$this->assertTrue($this->drive->exists('path/to/other/crazy/a-different-file.txt'));
		$this->assertTrue($this->drive->exists('path/to/another/a-different-file.txt'));

		$this->assertTrue($this->drive->deleteDirectory('/path/to'), 'deleted successfully?');

		$this->assertTrue(!$this->drive->exists('path/to/a-different-file.txt'));
		$this->assertTrue(!$this->drive->exists('path/to/other/crazy/a-different-file.txt'));
		$this->assertTrue(!$this->drive->exists('path/to/another/a-different-file.txt'));
	}

	public function testReadStream()
	{
		$this->drive->put('/streamy', 'my content');
		$this->assertTrue(stream_get_contents($this->drive->readStream('/streamy')) == 'my content');
	}

	public function testAppend()
	{
		$this->assertTrue($this->drive->put('/to/be/append.txt', 'First line'));
		$this->assertTrue($this->drive->append('/to/be/append.txt', 'Second line'));
		$content = $this->drive->read('/to/be/append.txt');
		$lines = explode(PHP_EOL, $content);
		$this->assertTrue($lines[0] == 'First line');
		$this->assertTrue($lines[1] == 'Second line');

		$this->assertTrue($this->drive->delete('/to/be/append.txt'));
		$this->assertTrue($this->drive->put('/to/be/append.txt', 'First bit'));
		$this->assertTrue($this->drive->append('/to/be/append.txt', 'Second bit', ' - '));
		$this->assertTrue($this->drive->read('/to/be/append.txt') == 'First bit - Second bit');
	}

	public function testPrepend()
	{
		$this->assertTrue($this->drive->put('/to/be/prepend.txt', 'Second line'));
		$this->assertTrue($this->drive->prepend('/to/be/prepend.txt', 'First line'));
		$content = $this->drive->read('/to/be/prepend.txt');
		$lines = explode(PHP_EOL, $content);
		$this->assertTrue($lines[0] == 'First line');
		$this->assertTrue($lines[1] == 'Second line');

		$this->assertTrue($this->drive->delete('/to/be/prepend.txt'));
		$this->assertTrue($this->drive->put('/to/be/prepend.txt', 'Second bit'));
		$this->assertTrue($this->drive->prepend('/to/be/prepend.txt', 'First bit', ' - '));
		$this->assertTrue($this->drive->read('/to/be/prepend.txt') == 'First bit - Second bit');
	}

	public function testCreateDirectory()
	{
		$this->assertTrue($this->drive->createDirectory('my/dir/to/test', 'directory should be created successfully and return true'));
		$this->assertTrue($this->drive->exists('my/dir/to/test'), 'The directory should exist');
		$meta = $this->drive->getMeta('my/dir/to/test');
		$this->assertTrue($meta['type'] == 'dir');
	}

	public function testObjectFilePut()
	{
		$contents = 'Test put file contents';
		$file = neon()->getAlias('@runtime/test-put.txt');
		file_put_contents($file, $contents);
		$this->assertEquals($contents, file_get_contents($file), 'check we can use default php functions to get the contents');
		$fileObject = new \SplFileObject($file);
		neon()->firefly->drive()->put('/my/object/test.txt', $fileObject);
		$actual = neon()->firefly->drive()->read('/my/object/test.txt');
		$this->assertEquals($contents, $actual);
	}

	public function testMakeAndListDirectory()
	{
		$this->testDeleteEverything();
		$this->assertTrue($this->drive->createDirectory('my/dir/to/test'));
		// won't error if added the same dir twice
		$this->assertTrue($this->drive->createDirectory('my/dir/to/test'));
		$this->assertTrue($this->drive->createDirectory('my/dir/to/testy'));
		$directories = $this->drive->listDirectories('my/dir');
		$this->assertEquals([
			'my/dir/to',
		], $directories);
		$directories = $this->drive->listDirectories('', true);
		// don't care about the order just that the directories exist in the list
		$this->assertTrue(
			in_array('my', $directories) &&
			in_array('my/dir', $directories) &&
			in_array('my/dir/to', $directories) &&
			in_array('my/dir/to/test', $directories) &&
			in_array('my/dir/to/testy', $directories)
		);
	}

	public function testDeleteEverything()
	{
		foreach ($this->drive->listContents('', true) as $fileInfo) {
			if ($fileInfo['type'] == 'dir') {
				$this->drive->deleteDirectory($fileInfo['path']);
			} else {
				$this->drive->delete($fileInfo['path']);
			}
		}
		$this->assertTrue(empty($this->drive->listContents('', true)), 'There should be no files left');
	}
}