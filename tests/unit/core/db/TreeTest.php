<?php
use neon\core\test\Unit;
use \neon\core\db\TreeUnsupportedException;
use neon\tests\unit\core\db\Tree;
use neon\tests\unit\core\db\MultipleTree;

class TreeTest extends Unit
{
	// Runs before each test
	public function _before()
	{
		neon()->db->createCommand('TRUNCATE tree')->execute();
	}

	public static function setUpBeforeClass(): void
	{
		$cmd = <<<TREE
			DROP TABLE IF EXISTS `tree`;
			CREATE TABLE `tree` (
				`id` char(22) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
				`tree_left` int(10) unsigned DEFAULT NULL,
				`tree_right` int(10) unsigned DEFAULT NULL,
				`tree_depth` int(10) unsigned DEFAULT NULL,
				`tree_parent` char(22) DEFAULT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
		TREE;
		neon()->db->createCommand($cmd)->execute();
	}

	public static function tearDownAfterClass(): void
	{
		neon()->db->createCommand('DROP TABLE IF EXISTS `tree`')->execute();
	}

	public function _after()
	{
	}

//	public function testCreatePreventOrphan()
//	{
//		$this->expectException(TreeUnsupportedException::class);
//		// should throw not supported - nodes must either be created as root nodes or be added to an existing tree
//		 $t = new Tree();
//		 $t->name = 'Ruby';
//		 $t->save();
//	}

	/**
	 * Create root nodes
	 */
	public function testCreateRoot()
	{
		$parslow = new Tree(['id' => 'parslow']);
		$this->assertTrue($parslow->makeRoot());
		$roots = Tree::find()->asArray()->all();
		$this->assertEquals([
			[
				'id' => 'parslow',
				'tree_left' => 1,
			    'tree_right' => 2,
			    'tree_depth' => 0,
			    'tree_parent' => null,
			],
		], $roots);
	}

	/**
	 * @expectedException \yii\db\Exception
	 */
	public function testExceptionCantHaveMoreThanOneRootNode()
	{
		$this->expectException(\Exception::class);
		$node = new Tree(['id' => 'One']);
		$node->makeRoot();
		$node = new Tree(['id' => 'Two']);
		$node->makeRoot();
	}

	public function testAddLastChildNodes()
	{
		$parslow = new Tree(['id' => 'parslow']);
		$parslow->makeRoot();
		$parslow->addChild(new Tree(['id'=>'bill']), function($node) {
			$node->addChild(new Tree(['id'=>'lyn']), function($node) {
				$node->addChild(new Tree(['id'=>'bob']), function($node) {
					$node->addChild(new Tree(['id'=>'charlie']));
					$node->addChild(new Tree(['id'=>'ruby']));
				});
				$node->addChild(new Tree(['id'=>'steve']));
				$node->addChild(new Tree(['id'=>'rich']));
			});
		});

		$tree = Tree::find()->asArray()->orderBy('tree_left')->all();
		$this->assertEquals([
			["id" => "parslow","tree_left" => 1,"tree_right" => 16,"tree_depth" => 0,"tree_parent" => null,],
				["id" => "bill","tree_left" => 2,"tree_right" => 15,"tree_depth" => 1,"tree_parent" => "parslow",],
					["id" => "lyn","tree_left" => 3,"tree_right" => 14,"tree_depth" => 2,"tree_parent" => "bill",],
						["id" => "bob","tree_left" => 4,"tree_right" => 9,"tree_depth" => 3,"tree_parent" => "lyn",],
							["id" => "charlie","tree_left" => 5,"tree_right" => 6,"tree_depth" => 4,"tree_parent" => "bob",],
							["id" => "ruby","tree_left" => 7,"tree_right" => 8,"tree_depth" => 4,"tree_parent" => "bob",],
						["id" => "steve","tree_left" => 10,"tree_right" => 11,"tree_depth" => 3,"tree_parent" => "lyn",],
						["id" => "rich","tree_left" => 12,"tree_right" => 13,"tree_depth" => 3,"tree_parent" => "lyn",],
		], $tree);
	}

	public function testAddFirstChildNodes()
	{
		$parslow = new Tree(['id' => 'parslow']);
		$parslow->makeRoot();
		$parslow->addChild(new Tree(['id'=>'bill']), function($node) {
			$node->addChild(new Tree(['id'=>'lyn']), function($node) {
				$node->addAsFirstChild(new Tree(['id'=>'bob']));
				$node->addAsFirstChild(new Tree(['id'=>'steve']));
				$node->addAsFirstChild(new Tree(['id'=>'rich']));
			});
		});

		$tree = Tree::find()->asArray()->orderBy('tree_left')->all();
		$this->assertEquals([
			["id" => "parslow","tree_left" => 1,"tree_right" => 12,"tree_depth" => 0,"tree_parent" => null,],
			["id" => "bill","tree_left" => 2,"tree_right" => 11,"tree_depth" => 1,"tree_parent" => "parslow",],
				["id" => "lyn","tree_left" => 3,"tree_right" => 10,"tree_depth" => 2,"tree_parent" => "bill",],
					["id" => "rich","tree_left" => 4,"tree_right" => 5,"tree_depth" => 3,"tree_parent" => "lyn",],
					["id" => "steve","tree_left" => 6,"tree_right" => 7,"tree_depth" => 3,"tree_parent" => "lyn",],
					["id" => "bob","tree_left" => 8,"tree_right" => 9,"tree_depth" => 3,"tree_parent" => "lyn",],
		], $tree);
	}


//
//	public function testPrependToNew()
//	{
//		$node = new Tree(['name' => 'New node']);
//		$this->assertTrue($node->prependTo(Tree::findOne(9)));
//
//		$node = new MultipleTree(['name' => 'New node']);
//		$this->assertTrue($node->prependTo(MultipleTree::findOne(31)));
//
//		$dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
//		$expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-prepend-to-new.xml');
//		$this->assertDataSetsEqual($expectedDataSet, $dataSet);
//	}
//
//	/**
//	 * @expectedException \yii\db\Exception
//	 */
//	public function testPrependToNewExceptionIsRaisedWhenTargetIsNewRecord()
//	{
//		$node = new Tree(['name' => 'New node']);
//		$node->prependTo(new Tree());
//	}
//
//	public function testAppendToNew()
//	{
//		$node = new Tree(['name' => 'New node']);
//		$this->assertTrue($node->appendTo(Tree::findOne(9)));
//
//		$node = new MultipleTree(['name' => 'New node']);
//		$this->assertTrue($node->appendTo(MultipleTree::findOne(31)));
//
//		$dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
//		$expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-append-to-new.xml');
//		$this->assertDataSetsEqual($expectedDataSet, $dataSet);
//	}
//
//	/**
//	 * @expectedException \yii\db\Exception
//	 */
//	public function testAppendNewToExceptionIsRaisedWhenTargetIsNewRecord()
//	{
//		$node = new Tree(['name' => 'New node']);
//		$node->appendTo(new Tree());
//	}
//
//	public function testInsertBeforeNew()
//	{
//		$node = new Tree(['name' => 'New node']);
//		$this->assertTrue($node->insertBefore(Tree::findOne(9)));
//
//		$node = new MultipleTree(['name' => 'New node']);
//		$this->assertTrue($node->insertBefore(MultipleTree::findOne(31)));
//
//		$dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
//		$expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-insert-before-new.xml');
//		$this->assertDataSetsEqual($expectedDataSet, $dataSet);
//	}
//
//	/**
//	 * @expectedException \yii\db\Exception
//	 */
//	public function testInsertBeforeNewExceptionIsRaisedWhenTargetIsNewRecord()
//	{
//		$node = new Tree(['name' => 'New node']);
//		$node->insertBefore(new Tree());
//	}
//
//	/**
//	 * @expectedException \yii\db\Exception
//	 */
//	public function testInsertBeforeNewExceptionIsRaisedWhenTargetIsRoot()
//	{
//		$node = new Tree(['name' => 'New node']);
//		$node->insertBefore(Tree::findOne(1));
//	}
//
//	public function testInsertAfterNew()
//	{
//		$node = new Tree(['name' => 'New node']);
//		$this->assertTrue($node->insertAfter(Tree::findOne(9)));
//
//		$node = new MultipleTree(['name' => 'New node']);
//		$this->assertTrue($node->insertAfter(MultipleTree::findOne(31)));
//
//		$dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
//		$expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-insert-after-new.xml');
//		$this->assertDataSetsEqual($expectedDataSet, $dataSet);
//	}
//
//	/**
//	 * @expectedException \yii\db\Exception
//	 */
//	public function testInsertAfterNewExceptionIsRaisedWhenTargetIsNewRecord()
//	{
//		$node = new Tree(['name' => 'New node']);
//		$node->insertAfter(new Tree());
//	}
//
//	/**
//	 * @expectedException \yii\db\Exception
//	 */
//	public function testInsertAfterNewExceptionIsRaisedWhenTargetIsRoot()
//	{
//		$node = new Tree(['name' => 'New node']);
//		$node->insertAfter(Tree::findOne(1));
//	}
//
//	public function testMakeRootExists()
//	{
//		$node = MultipleTree::findOne(31);
//		$node->name = 'Updated node 2';
//		$this->assertTrue($node->makeRoot());
//
//		$dataSet = $this->getConnection()->createDataSet(['multiple_tree']);
//		$expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-make-root-exists.xml');
//		$this->assertDataSetsEqual($expectedDataSet, $dataSet);
//	}
//
//	/**
//	 * @expectedException \yii\db\Exception
//	 */
//	public function testMakeRootExistsExceptionIsRaisedWhenTreeAttributeIsFalse()
//	{
//		$node = Tree::findOne(9);
//		$node->makeRoot();
//	}
//
//	/**
//	 * @expectedException \yii\db\Exception
//	 */
//	public function testMakeRootExistsExceptionIsRaisedWhenItsRoot()
//	{
//		$node = MultipleTree::findOne(23);
//		$node->makeRoot();
//	}
//
//	public function testPrependToExistsUp()
//	{
//		$node = Tree::findOne(9);
//		$node->name = 'Updated node 2';
//		$this->assertTrue($node->prependTo(Tree::findOne(2)));
//
//		$node = MultipleTree::findOne(31);
//		$node->name = 'Updated node 2';
//		$this->assertTrue($node->prependTo(MultipleTree::findOne(24)));
//
//		$dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
//		$expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-prepend-to-exists-up.xml');
//		$this->assertDataSetsEqual($expectedDataSet, $dataSet);
//	}
//
//	public function testPrependToExistsDown()
//	{
//		$node = Tree::findOne(9);
//		$node->name = 'Updated node 2';
//		$this->assertTrue($node->prependTo(Tree::findOne(16)));
//
//		$node = MultipleTree::findOne(31);
//		$node->name = 'Updated node 2';
//		$this->assertTrue($node->prependTo(MultipleTree::findOne(38)));
//
//		$dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
//		$expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-prepend-to-exists-down.xml');
//		$this->assertDataSetsEqual($expectedDataSet, $dataSet);
//	}
//
//	public function testPrependToExistsAnotherTree()
//	{
//		$node = MultipleTree::findOne(9);
//		$node->name = 'Updated node 2';
//		$this->assertTrue($node->prependTo(MultipleTree::findOne(53)));
//
//		$dataSet = $this->getConnection()->createDataSet(['multiple_tree']);
//		$expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-prepend-to-exists-another-tree.xml');
//		$this->assertDataSetsEqual($expectedDataSet, $dataSet);
//	}
//
//	/**
//	 * @expectedException \yii\db\Exception
//	 */
//	public function testPrependToExistsExceptionIsRaisedWhenTargetIsNewRecord()
//	{
//		$node = Tree::findOne(9);
//		$node->prependTo(new Tree());
//	}
//
//	/**
//	 * @expectedException \yii\db\Exception
//	 */
//	public function testPrependToExistsExceptionIsRaisedWhenTargetIsSame()
//	{
//		$node = Tree::findOne(9);
//		$node->prependTo(Tree::findOne(9));
//	}
//
//	/**
//	 * @expectedException \yii\db\Exception
//	 */
//	public function testPrependToExistsExceptionIsRaisedWhenTargetIsChild()
//	{
//		$node = Tree::findOne(9);
//		$node->prependTo(Tree::findOne(11));
//	}
//
//	public function testAppendToExistsUp()
//	{
//		$node = Tree::findOne(9);
//		$node->name = 'Updated node 2';
//		$this->assertTrue($node->appendTo(Tree::findOne(2)));
//
//		$node = MultipleTree::findOne(31);
//		$node->name = 'Updated node 2';
//		$this->assertTrue($node->appendTo(MultipleTree::findOne(24)));
//
//		$dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
//		$expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-append-to-exists-up.xml');
//		$this->assertDataSetsEqual($expectedDataSet, $dataSet);
//	}
//
//	public function testAppendToExistsDown()
//	{
//		$node = Tree::findOne(9);
//		$node->name = 'Updated node 2';
//		$this->assertTrue($node->appendTo(Tree::findOne(16)));
//
//		$node = MultipleTree::findOne(31);
//		$node->name = 'Updated node 2';
//		$this->assertTrue($node->appendTo(MultipleTree::findOne(38)));
//
//		$dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
//		$expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-append-to-exists-down.xml');
//		$this->assertDataSetsEqual($expectedDataSet, $dataSet);
//	}
//
//	public function testAppendToExistsAnotherTree()
//	{
//		$node = MultipleTree::findOne(9);
//		$node->name = 'Updated node 2';
//		$this->assertTrue($node->appendTo(MultipleTree::findOne(53)));
//
//		$dataSet = $this->getConnection()->createDataSet(['multiple_tree']);
//		$expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-append-to-exists-another-tree.xml');
//		$this->assertDataSetsEqual($expectedDataSet, $dataSet);
//	}
//
//	/**
//	 * @expectedException \yii\db\Exception
//	 */
//	public function testAppendToExistsExceptionIsRaisedWhenTargetIsNewRecord()
//	{
//		$node = Tree::findOne(9);
//		$node->appendTo(new Tree());
//	}
//
//	/**
//	 * @expectedException \yii\db\Exception
//	 */
//	public function testAppendToExistsExceptionIsRaisedWhenTargetIsSame()
//	{
//		$node = Tree::findOne(9);
//		$node->appendTo(Tree::findOne(9));
//	}
//
//	/**
//	 * @expectedException \yii\db\Exception
//	 */
//	public function testAppendToExistsExceptionIsRaisedWhenTargetIsChild()
//	{
//		$node = Tree::findOne(9);
//		$node->appendTo(Tree::findOne(11));
//	}
//
//	public function testInsertBeforeExistsUp()
//	{
//		$node = Tree::findOne(9);
//		$node->name = 'Updated node 2';
//		$this->assertTrue($node->insertBefore(Tree::findOne(2)));
//
//		$node = MultipleTree::findOne(31);
//		$node->name = 'Updated node 2';
//		$this->assertTrue($node->insertBefore(MultipleTree::findOne(24)));
//
//		$dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
//		$expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-insert-before-exists-up.xml');
//		$this->assertDataSetsEqual($expectedDataSet, $dataSet);
//	}
//
//	public function testInsertBeforeExistsDown()
//	{
//		$node = Tree::findOne(9);
//		$node->name = 'Updated node 2';
//		$this->assertTrue($node->insertBefore(Tree::findOne(16)));
//
//		$node = MultipleTree::findOne(31);
//		$node->name = 'Updated node 2';
//		$this->assertTrue($node->insertBefore(MultipleTree::findOne(38)));
//
//		$dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
//		$expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-insert-before-exists-down.xml');
//		$this->assertDataSetsEqual($expectedDataSet, $dataSet);
//	}
//
//	public function testInsertBeforeExistsAnotherTree()
//	{
//		$node = MultipleTree::findOne(9);
//		$node->name = 'Updated node 2';
//		$this->assertTrue($node->insertBefore(MultipleTree::findOne(53)));
//
//		$dataSet = $this->getConnection()->createDataSet(['multiple_tree']);
//		$expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-insert-before-exists-another-tree.xml');
//		$this->assertDataSetsEqual($expectedDataSet, $dataSet);
//	}
//
//	/**
//	 * @expectedException \yii\db\Exception
//	 */
//	public function testInsertBeforeExistsExceptionIsRaisedWhenTargetIsNewRecord()
//	{
//		$node = Tree::findOne(9);
//		$node->insertBefore(new Tree());
//	}
//
//	/**
//	 * @expectedException \yii\db\Exception
//	 */
//	public function testInsertBeforeExistsExceptionIsRaisedWhenTargetIsSame()
//	{
//		$node = Tree::findOne(9);
//		$node->insertBefore(Tree::findOne(9));
//	}
//
//	/**
//	 * @expectedException \yii\db\Exception
//	 */
//	public function testInsertBeforeExistsExceptionIsRaisedWhenTargetIsChild()
//	{
//		$node = Tree::findOne(9);
//		$node->insertBefore(Tree::findOne(11));
//	}
//
//	/**
//	 * @expectedException \yii\db\Exception
//	 */
//	public function testInsertBeforeExistsExceptionIsRaisedWhenTargetIsRoot()
//	{
//		$node = Tree::findOne(9);
//		$node->insertBefore(Tree::findOne(1));
//	}
//
//	public function testInsertAfterExistsUp()
//	{
//		$node = Tree::findOne(9);
//		$node->name = 'Updated node 2';
//		$this->assertTrue($node->insertAfter(Tree::findOne(2)));
//
//		$node = MultipleTree::findOne(31);
//		$node->name = 'Updated node 2';
//		$this->assertTrue($node->insertAfter(MultipleTree::findOne(24)));
//
//		$dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
//		$expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-insert-after-exists-up.xml');
//		$this->assertDataSetsEqual($expectedDataSet, $dataSet);
//	}
//
//	public function testInsertAfterExistsDown()
//	{
//		$node = Tree::findOne(9);
//		$node->name = 'Updated node 2';
//		$this->assertTrue($node->insertAfter(Tree::findOne(16)));
//
//		$node = MultipleTree::findOne(31);
//		$node->name = 'Updated node 2';
//		$this->assertTrue($node->insertAfter(MultipleTree::findOne(38)));
//
//		$dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
//		$expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-insert-after-exists-down.xml');
//		$this->assertDataSetsEqual($expectedDataSet, $dataSet);
//	}
//
//	public function testInsertAfterExistsAnotherTree()
//	{
//		$node = MultipleTree::findOne(9);
//		$node->name = 'Updated node 2';
//		$this->assertTrue($node->insertAfter(MultipleTree::findOne(53)));
//
//		$dataSet = $this->getConnection()->createDataSet(['multiple_tree']);
//		$expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-insert-after-exists-another-tree.xml');
//		$this->assertDataSetsEqual($expectedDataSet, $dataSet);
//	}
//
//	/**
//	 * @expectedException \yii\db\Exception
//	 */
//	public function testInsertAfterExistsExceptionIsRaisedWhenTargetIsNewRecord()
//	{
//		$node = Tree::findOne(9);
//		$node->insertAfter(new Tree());
//	}
//
//	/**
//	 * @expectedException \yii\db\Exception
//	 */
//	public function testInsertAfterExistsExceptionIsRaisedWhenTargetIsSame()
//	{
//		$node = Tree::findOne(9);
//		$node->insertAfter(Tree::findOne(9));
//	}
//
//	/**
//	 * @expectedException \yii\db\Exception
//	 */
//	public function testInsertAfterExistsExceptionIsRaisedWhenTargetIsChild()
//	{
//		$node = Tree::findOne(9);
//		$node->insertAfter(Tree::findOne(11));
//	}
//
//	/**
//	 * @expectedException \yii\db\Exception
//	 */
//	public function testInsertAfterExistsExceptionIsRaisedWhenTargetIsRoot()
//	{
//		$node = Tree::findOne(9);
//		$node->insertAfter(Tree::findOne(1));
//	}
//
//	public function testDeleteWithChildren()
//	{
//		$this->assertEquals(7, Tree::findOne(9)->deleteWithChildren());
//		$this->assertEquals(7, MultipleTree::findOne(31)->deleteWithChildren());
//
//		$dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
//		$expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-delete-with-children.xml');
//		$this->assertDataSetsEqual($expectedDataSet, $dataSet);
//	}
//
//	/**
//	 * @expectedException \yii\db\Exception
//	 */
//	public function testDeleteWithChildrenExceptionIsRaisedWhenNodeIsNewRecord()
//	{
//		$node = new Tree();
//		$node->deleteWithChildren();
//	}
//
//	public function testDelete()
//	{
//		$this->assertEquals(1, Tree::findOne(9)->delete());
//		$this->assertEquals(1, MultipleTree::findOne(31)->delete());
//
//		$dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
//		$expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-delete.xml');
//		$this->assertDataSetsEqual($expectedDataSet, $dataSet);
//	}
//
//	/**
//	 * @expectedException \yii\db\Exception
//	 */
//	public function testDeleteExceptionIsRaisedWhenNodeIsNewRecord()
//	{
//		$node = new Tree();
//		$node->delete();
//	}
//
//	/**
//	 * @expectedException \yii\base\NotSupportedException
//	 */
//	public function testDeleteExceptionIsRaisedWhenNodeIsRoot()
//	{
//		$node = Tree::findOne(1);
//		$node->delete();
//	}
//
//	/**
//	 * @expectedException \yii\base\NotSupportedException
//	 */
//	public function testExceptionIsRaisedWhenInsertIsCalled()
//	{
//		$node = new Tree([]);
//		$node->insert();
//	}
//
//	public function testUpdate()
//	{
//		$node = Tree::findOne(9);
//		$node->name = 'Updated node 2';
//		$this->assertEquals(1, $node->update());
//	}
//
//	public function testParents()
//	{
//		$this->assertEquals(
//			require(__DIR__ . '/data/test-parents.php'),
//			ArrayHelper::toArray(Tree::findOne(11)->parents()->all())
//		);
//
//		$this->assertEquals(
//			require(__DIR__ . '/data/test-parents-multiple-tree.php'),
//			ArrayHelper::toArray(MultipleTree::findOne(33)->parents()->all())
//		);
//
//		$this->assertEquals(
//			require(__DIR__ . '/data/test-parents-with-depth.php'),
//			ArrayHelper::toArray(Tree::findOne(11)->parents(1)->all())
//		);
//
//		$this->assertEquals(
//			require(__DIR__ . '/data/test-parents-multiple-tree-with-depth.php'),
//			ArrayHelper::toArray(MultipleTree::findOne(33)->parents(1)->all())
//		);
//	}
//
//	public function testChildren()
//	{
//		$this->assertEquals(
//			require(__DIR__ . '/data/test-children.php'),
//			ArrayHelper::toArray(Tree::findOne(9)->children()->all())
//		);
//
//		$this->assertEquals(
//			require(__DIR__ . '/data/test-children-multiple-tree.php'),
//			ArrayHelper::toArray(MultipleTree::findOne(31)->children()->all())
//		);
//
//		$this->assertEquals(
//			require(__DIR__ . '/data/test-children-with-depth.php'),
//			ArrayHelper::toArray(Tree::findOne(9)->children(1)->all())
//		);
//
//		$this->assertEquals(
//			require(__DIR__ . '/data/test-children-multiple-tree-with-depth.php'),
//			ArrayHelper::toArray(MultipleTree::findOne(31)->children(1)->all())
//		);
//	}
//
//	public function testLeaves()
//	{
//		$this->assertEquals(
//			require(__DIR__ . '/data/test-leaves.php'),
//			ArrayHelper::toArray(Tree::findOne(9)->leaves()->all())
//		);
//
//		$this->assertEquals(
//			require(__DIR__ . '/data/test-leaves-multiple-tree.php'),
//			ArrayHelper::toArray(MultipleTree::findOne(31)->leaves()->all())
//		);
//	}
//
//	public function testPrev()
//	{
//		$this->assertEquals(
//			require(__DIR__ . '/data/test-prev.php'),
//			ArrayHelper::toArray(Tree::findOne(9)->prev()->all())
//		);
//
//		$this->assertEquals(
//			require(__DIR__ . '/data/test-prev-multiple-tree.php'),
//			ArrayHelper::toArray(MultipleTree::findOne(31)->prev()->all())
//		);
//	}
//
//	public function testNext()
//	{
//		$this->assertEquals(
//			require(__DIR__ . '/data/test-next.php'),
//			ArrayHelper::toArray(Tree::findOne(9)->next()->all())
//		);
//
//		$this->assertEquals(
//			require(__DIR__ . '/data/test-next-multiple-tree.php'),
//			ArrayHelper::toArray(MultipleTree::findOne(31)->next()->all())
//		);
//	}
//
//	public function testIsRoot()
//	{
//		$this->assertTrue(Tree::findOne(1)->isRoot());
//		$this->assertFalse(Tree::findOne(2)->isRoot());
//	}
//
//	public function testIsChildOf()
//	{
//		$node = MultipleTree::findOne(26);
//		$this->assertTrue($node->isChildOf(MultipleTree::findOne(25)));
//		$this->assertTrue($node->isChildOf(MultipleTree::findOne(23)));
//		$this->assertFalse($node->isChildOf(MultipleTree::findOne(3)));
//		$this->assertFalse($node->isChildOf(MultipleTree::findOne(1)));
//	}
//
//	public function testIsLeaf()
//	{
//		$this->assertTrue(Tree::findOne(4)->isLeaf());
//		$this->assertFalse(Tree::findOne(1)->isLeaf());
//	}


}
