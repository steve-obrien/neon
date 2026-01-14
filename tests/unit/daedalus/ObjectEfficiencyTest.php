<?php

namespace neon\tests\unit\daedalus;

use \neon\tests\unit\daedalus\DdsBaseTestUnit;
use \neon\daedalus\services\ddsManager\DdsObjectManager;
use \neon\daedalus\services\ddsManager\DdsDataDefinitionManager;
use \Codeception\Util\Debug;
use Yii;

class ObjectEfficiencyTest extends DdsBaseTestUnit
{

	public static function setUpBeforeClass(): void {
		$table = 'ddt_efficiency_test';
		$sql =<<<EOQ
CREATE TABLE IF NOT EXISTS `$table` (
  `_uuid` CHAR(22) NOT NULL,
  `text0` TEXT(16000) NULL,
  `text1` TEXT(16000) NULL,
  `text2` TEXT(16000) NULL,
  `text3` TEXT(16000) NULL,
  `text4` TEXT(16000) NULL,
  `text5` TEXT(16000) NULL,
  `text6` TEXT(16000) NULL,
  `text7` TEXT(16000) NULL,
  `text8` TEXT(16000) NULL,
  `int0` INTEGER NULL,
  `int1` INTEGER NULL,
  `int2` INTEGER NULL,
  `int3` INTEGER NULL,
  `int4` INTEGER NULL,
  `int5` INTEGER NULL,
  `int6` INTEGER NULL,
  `int7` INTEGER NULL,
  `int8` INTEGER NULL,
  PRIMARY KEY (`_uuid`),
  INDEX `text0` (`text0`(100)),
  INDEX `text1` (`text1`(100)),
  INDEX `text2` (`text2`(100)),
  INDEX `text3` (`text3`(100)),
  INDEX `text4` (`text4`(100)),
  INDEX `text5` (`text5`(100)),
  INDEX `text6` (`text6`(100)),
  INDEX `text7` (`text7`(100)),
  INDEX `text8` (`text8`(100)),
  INDEX `int0` (`int0`),
  INDEX `int1` (`int1`),
  INDEX `int2` (`int2`),
  INDEX `int3` (`int3`),
  INDEX `int4` (`int4`),
  INDEX `int5` (`int5`),
  INDEX `int6` (`int6`),
  INDEX `int7` (`int7`),
  INDEX `int8` (`int8`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Holds the basic information about the dynamic data class';
EOQ;
		neon()->db->createCommand($sql)->execute();
	}

	public function tearDown():void
	{
		$table = 'ddt_efficiency_test';
		$query = "DROP TABLE IF EXISTS `$table`";
		neon()->db->createCommand($query)->execute();
	}

	public function testFillDatabase() {
		$this->dds = new DdsDataDefinitionManager();
		$this->ddo = new DdsObjectManager();
		$dataTypeRefText = $this->randomString(20);
		$dataTypeRefInt = $this->randomString(20);
		$this->dds->addDataType($dataTypeRefText, "Short Text", "Short Text", '', "textshort");
		$this->dds->addDataType($dataTypeRefInt, "Integer", "Integer", '', "integershort");

		$classType = 'efficiency_test'.$this->randomString(10);
		$this->dds->addClass($classType, 'test');

		$members = [];
		$memberIds = [];
		for ($i = 0; $i<9; $i++) {
			$memberRef = 'text'.$i;
			$members[] = $memberRef;
			$memberIds[$memberRef] = $this->dds->addMember($classType, $memberRef, $dataTypeRefText, $memberRef, '');
		}
		for ($i = 0; $i<9; $i++) {
			$memberRef = 'int'.$i;
			$members[] = $memberRef;
			$memberIds[$memberRef] = $this->dds->addMember($classType, $memberRef, $dataTypeRefInt, $memberRef, '');
		}
		// now insert some 1200 rows to check efficiency
		for ($i=0; $i<30; $i++) {
			$inserts = [];
			for ($j=0; $j<40; $j++) {
				$txtDataInserts = [];
				$intDataInserts = [];
				$objectId = $this->uuid64();
				for ($k=0; $k<9; $k++) {
					$txtDataInserts[] = "'".$this->randomString(10)."'";
					$intDataInserts[] = rand(1,10000);
				}
				$inserts[] = "('$objectId', ".implode(',',$txtDataInserts).', '.implode(',',$intDataInserts).")";
			}
			$query = "INSERT INTO `ddt_efficiency_test` (`_uuid`, `text0`,`text1`,`text2`,`text3`,`text4`,`text5`,`text6`,`text7`,`text8`,`int0`,`int1`,`int2`,`int3`,`int4`,`int5`,`int6`,`int7`,`int8`) VALUES ".implode(', ', $inserts);
			neon()->db->createCommand($query)->execute();
		}
	}
}