<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 23/11/2016 02:46
 * @package neon
 */

namespace newicon\tests\unit\firefly;

use neon\core\test\Unit;
use neon\firefly\services\mediaManager\models\Media;
use neon\firefly\services\mediaManager\DirectoryExistsException;
use neon\firefly\services\mediaManager\InvalidDirectoryException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class MediaTest extends Unit
{
	public function setUp():void
	{
		$this->path = ['/'.$this->randomString(5), $this->randomString(10)];
		$this->pathFull = implode('/',$this->path);
	}

	public function tearDown():void
	{
		// remove any added items
		$list = neon()->firefly->mediaManager->listContents('/');
		foreach($list as $item) {
			neon()->firefly->mediaManager->destroy($item['id']);
		}
	}

	public function testCreateDirectory()
	{
		// get the id of the root node.
		$root = Media::getRootNode();
		$priorList = neon()->firefly->mediaManager->listContents('/');

		$created = neon()->firefly->mediaManager->createDirectory($this->pathFull);
		$my = $created[0];
		$folder = $created[1];
		$expected = [
			[
				"parent_id" => $root['id'],
				"path" => $this->path[0],
				"type" => "dir",
				"id" => $my['id']
			],
			[
				"parent_id" => $my['id'],
				"path" => $this->pathFull,
				"type" => "dir",
				"id" => $folder['id']
			]
		];
		$this->assertEquals($expected, $created);

		$this->assertEquals(true, neon()->firefly->mediaManager->delete($my['id']), 'soft delete a media item');
		$this->assertThat(neon()->firefly->mediaManager->delete($folder['id']), $this->isTrue(), 'soft delete folder');
		$this->assertThat(neon()->firefly->mediaManager->exists($this->path[0]), $this->isFalse(), 'exists should return false as it has been soft deleted');
		$this->assertThat(neon()->firefly->mediaManager->exists($this->pathFull), $this->isFalse());
		$list = neon()->firefly->mediaManager->listContents('/');
		$this->assertEquals($priorList, $list);

		// destroy folders
		$this->assertThat(neon()->firefly->mediaManager->destroy($my['id']), $this->equalTo(1));
		$this->assertThat(neon()->firefly->mediaManager->destroy($folder['id']), $this->equalTo(1));
	}

	public function testDirectoryExistsException()
	{
		$this->expectException(DirectoryExistsException::class);
		neon()->firefly->mediaManager->createDirectory($this->path[0]);
		neon()->firefly->mediaManager->createDirectory($this->path[0]);
	}

	public function testNotFoundExceptionWhenDestoryingNonExistingItem()
	{
		$this->expectException(NotFoundHttpException::class);
		neon()->firefly->mediaManager->destroy('this-id-does-not-exist');
		neon()->firefly->mediaManager->destroy([]);
	}

	public function testList()
	{
		neon()->firefly->mediaManager->createDirectory($this->pathFull);
		$list = neon()->firefly->mediaManager->listContents('/');
		$found = false;
		$k = null;
		foreach ($list as $k=>$l) {
			if ($l['path'] == $this->path[0]) {
				$found = true;
				break;
			}
		}
		$this->assertTrue($found);
		$this->assertTrue($list[$k]['type'] == 'dir');
	}

	public function testExists()
	{
		neon()->firefly->mediaManager->createDirectory($this->pathFull);
		$exists = neon()->firefly->mediaManager->exists($this->pathFull.'/this/does/not/exist');
		$this->assertEquals(false, $exists);
		$exists = neon()->firefly->mediaManager->exists($this->path[0]);
		$this->assertEquals(true, $exists);
	}

	public function testAddFile()
	{
		$root = Media::getRootNode();

		// lets add a file to the file manager
		$id = neon()->firefly->save('my new file managed file contents', 'test', ['somedata' => 'ooogg']);
		// add file to the root
		$meta = neon()->firefly->mediaManager->addFile($id, '/');
		$list = neon()->firefly->mediaManager->listContents('/');
		$this->assertArraySubset([
			0 => ['type' => 'file', 'path' => '/', 'parent_id' => $root->id, 'file' => ['name' => $meta['file']['name']]],
		], $list);

		$id = neon()->firefly->save('my new file managed file contents');
		// add a new file to a directory that does not yet exist
		neon()->firefly->mediaManager->addFile($id, $this->pathFull.'/this/does/not/exist');
		$list = neon()->firefly->mediaManager->listContents('/');

		$this->assertArraySubset([
			['type' => 'dir', 'path' => $this->path[0], 'parent_id' => $root->id],
			['type' => 'file', 'path' => '/', 'parent_id' => $root->id, 'file' => ['name' => $meta['file']['name']]],
		], $list);
		$thisFolderId = $list[0]['id'];
		$list = neon()->firefly->mediaManager->listContents($this->path[0]);
		$this->assertArraySubset([
			['type' => 'dir', 'path' => $this->pathFull, 'parent_id' => $thisFolderId],
		], $list);

	}

	public function testExceptionRootOnly()
	{
		$this->expectException(DirectoryExistsException::class);
		neon()->firefly->mediaManager->createDirectory('/');
	}

	public function testExceptionDoubleSlash()
	{
		$this->expectException(InvalidDirectoryException::class);
		neon()->firefly->mediaManager->createDirectory('/my//folder');
	}

	public function testExceptionNoRoot()
	{
		$this->expectException(InvalidDirectoryException::class);
		neon()->firefly->mediaManager->createDirectory('my/folder');
	}

	public function testExceptionDoubleWhammy()
	{
		$this->expectException(InvalidDirectoryException::class);
		neon()->firefly->mediaManager->createDirectory('my//folder');
	}

	public function testExceptionDirectoryExists()
	{
		$this->expectException(DirectoryExistsException::class);
		$created = neon()->firefly->mediaManager->createDirectory($this->pathFull);
		neon()->firefly->mediaManager->createDirectory($this->pathFull);
		neon()->firefly->mediaManager->destroy($created['id']);
	}

	public function testGetRoot()
	{
		$root = neon()->firefly->mediaManager->getRoot();
		$this->assertThat($root, $this->arrayHasKey('id'));
		$this->assertThat($root['parent_id'], $this->equalTo(null));
		$this->assertThat($root['path'], $this->equalTo('/'));
		$this->assertThat($root['type'], $this->equalTo('root'));
	}

	public function testRenameExceptionCannotHaveSlash()
	{
		$dir = neon()->firefly->mediaManager->createDirectory('/test');
		$this->expectException(BadRequestHttpException::class);
		neon()->firefly->mediaManager->rename($dir[0]['id'], 'erg/noslash');
	}

	public function testRenameExceptionItemNotFound()
	{
		$this->expectException(NotFoundHttpException::class);
		neon()->firefly->mediaManager->rename('notfoundid', 'name');
	}

	public function testRename()
	{
		// create a directory
		neon()->firefly->mediaManager->createDirectory($this->pathFull.'/to_rename/things/inside');
		neon()->firefly->mediaManager->createDirectory($this->pathFull.'/to_rename/more/things');
		neon()->firefly->mediaManager->createDirectory($this->pathFull.'/to_rename/more/thing');
		$renameMeta = neon()->firefly->mediaManager->getDirectoryMeta($this->pathFull.'/to_rename');
		$renameThing = neon()->firefly->mediaManager->getDirectoryMeta($this->pathFull.'/to_rename/more/thing');
		// test meta returns null if directory does not exist
		$this->assertEquals([], neon()->firefly->mediaManager->getDirectoryMeta($this->pathFull.'/does/not/exist'));
		neon()->firefly->mediaManager->rename($renameMeta['id'], 'renamed');
		neon()->firefly->mediaManager->rename($renameThing['id'], 'used_to_be_thing');

		// check the folders are renamed
		$metaA = neon()->firefly->mediaManager->getDirectoryMeta($this->pathFull.'/renamed/things');
		$metaB = neon()->firefly->mediaManager->getDirectoryMeta($this->pathFull.'/renamed/things/inside');
		$meta1 = neon()->firefly->mediaManager->getDirectoryMeta($this->pathFull.'/renamed/more');
		$meta2 = neon()->firefly->mediaManager->getDirectoryMeta($this->pathFull.'/renamed/more/things');

		$this->assertArraySubset(['type' => 'dir', 'path' => $this->pathFull.'/renamed/things', 'parent_id' => $renameMeta['id']], $metaA);
		$this->assertArraySubset(['type' => 'dir', 'path' => $this->pathFull.'/renamed/things/inside', 'parent_id' => $metaA['id']], $metaB);

		$this->assertArraySubset(['type' => 'dir', 'path' => $this->pathFull.'/renamed/more', 'parent_id' => $renameMeta['id']], $meta1);
		$this->assertArraySubset(['type' => 'dir', 'path' => $this->pathFull.'/renamed/more/things', 'parent_id' => $meta1['id']], $meta2);

		$metaThing = neon()->firefly->mediaManager->getDirectoryMeta($this->pathFull.'/renamed/more/used_to_be_thing');
		$this->assertArraySubset(['type' => 'dir', 'path' => $this->pathFull.'/renamed/more/used_to_be_thing', 'parent_id' => $meta1['id']], $metaThing);
	}

}
