<?php

namespace neon\tests\unit\phoebe;

use \neon\core\test\Unit;
use \neon\phoebe\services\PhoebeService;
use neon\daedalus\services\ddsAppMigrator\DdsAppMigratorFactory;
use neon\daedalus\services\ddsAppMigrator\DdsAppMigrator;
use neon\tests\unit\daedalus\mocks\DdsAppMigratorMock;

class PhoebeAppFormTest extends Unit {

	private $dds = null;
	private $phoebe = null;

	/**
	 * @inheritdoc
	 */
	public function setUp():void
	{
		self::createTables();
		$this->dds = neon('dds')->IDdsObjectManagement;
		$this->phoebe = neon('phoebe')->getIPhoebeType('applicationForm');
	}

	public function tearDown():void
	{
		self::destroyTables();
	}

	/*public function testCreateAFormFromSimpleDefinition()
	{
		$this->assertTrue(false, 'you need to implement some phoebe tests!!');
	}*/

	public function testInitialiseANewFormWithDdsTables()
	{
		$classType = 'multilinkformtest';

		// create a new couple of dds objects
		$main = $this->dds->addObject('main', ['line_1'=>'I am line 1', 'area_1'=>'I am area 1']);
		$linky = $this->dds->addObject('linky', ['name'=>'My Name', 'description'=>'Dark, tall and handsome']);

		// create a new form with initialised data and see if it can accept it
		$class = $this->phoebe->getClass($classType);
		$formDefinition = $class->getClassFormDefinition();
		$object = $this->phoebe->createStubObject($classType);
		$object->initialiseFromDds([
			'bn6nW2UIjJG38RCj0zgy3M'=>$main,
			'svuP9lI7jYaK-5ctkKYEg0'=>$linky
		]);
		$form = new \neon\core\form\Form($formDefinition);
		$form->loadFromDb($object->data);
		$data = $object->data;
		$rootNode = key($data);

		// create several new objects pointing to the same dds objects
		// and check that we only have the one set of data showing
		for ($i=1; $i<=5; $i++) {
			$newObject = $this->phoebe->addObject($classType);
			$data[$rootNode]['line_2'] = 'Editing for the '.$i.'th time';
			$data[$rootNode]['area_2'] = 'Editing area for the '.$i.'th time';
			$newObject->editObject($data);
		}

		// need to work out some assertions here for the amount of objects and final
		// data sent to the object

	}


	protected function createTables()
	{
		$sql =<<<EOQ
SET foreign_key_checks = 0;
--
-- Dumping data for classes and members
--
INSERT INTO `dds_class` (`class_type`, `label`, `description`, `module`, `deleted`, `count_total`, `count_deleted`)
VALUES
	('linky', 'Linked to ', 'Table linked to by the other table', 'phoebe', 0, 0, 0),
	('main', 'Main', 'Main table to set up', 'phoebe', 0, 0, 0);
INSERT INTO `dds_member` (`class_type`, `member_ref`, `data_type_ref`, `label`, `description`, `choices`, `link_class`, `map_field`, `created`, `updated`, `deleted`)
VALUES
	('linky', 'description', 'text', 'Description', 'Some description of something', NULL, NULL, 0, '2018-12-17 16:29:32', NULL, 0),
	('linky', 'name', 'textshort', 'Name', 'A name for the fun of it', NULL, NULL, 1, '2018-12-17 16:29:32', '2018-12-17 16:29:32', 0),
	('main', 'area_1', 'text', 'Area 1', '', NULL, NULL, 0, '2018-12-17 16:33:03', NULL, 0),
	('main', 'area_2', 'text', 'Area 2', '', NULL, NULL, 0, '2018-12-17 16:33:04', NULL, 0),
	('main', 'area_3', 'text', 'Area 3', '', NULL, NULL, 0, '2018-12-17 16:33:04', NULL, 0),
	('main', 'line_1', 'textshort', 'Line 1', '', NULL, NULL, 0, '2018-12-17 16:33:03', NULL, 0),
	('main', 'line_2', 'textshort', 'Line 2', '', NULL, NULL, 0, '2018-12-17 16:33:03', NULL, 0),
	('main', 'line_3', 'textshort', 'Line 3', '', NULL, NULL, 0, '2018-12-17 16:33:04', NULL, 0),
	('main', 'multilink', 'link_multi', 'Multilink', '', NULL, 'linky', 0, '2018-12-17 16:33:04', NULL, 0);

-- Create syntax for TABLE 'ddt_linky'
CREATE TABLE `ddt_linky` (
  `_uuid` char(22) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL COMMENT 'object _uuid from the dds_object table',
  `name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`_uuid`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create syntax for TABLE 'ddt_main'
CREATE TABLE `ddt_main` (
  `_uuid` char(22) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL COMMENT 'object _uuid from the dds_object table',
  `line_1` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `area_1` text COLLATE utf8mb4_unicode_ci,
  `line_2` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `area_2` text COLLATE utf8mb4_unicode_ci,
  `line_3` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `area_3` text COLLATE utf8mb4_unicode_ci,
  `multilink` char(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`_uuid`),
  KEY `line_1` (`line_1`),
  KEY `line_2` (`line_2`),
  KEY `line_3` (`line_3`),
  KEY `multilink` (`multilink`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SET foreign_key_checks = 1;

--
-- Dump of phoebe class definitions for above and mutliformtest appform
--
INSERT INTO `phoebe_class` (`phoebe_type`, `class_type`, `version`, `module`, `label`, `description`, `version_locked`, `volatile`, `object_history`, `created`, `updated`, `deleted`, `serialised_definition`)
VALUES
	('applicationForm', 'multilinkformtest', 1, 'phoebe', 'Multiform Test', NULL, 0, 1, 0, '2018-12-17 16:34:35', '2018-12-17 16:34:35', 0, '{\"rootNode\":\"tI0nb9iVgLGfYK8vz2gyQM\",\"gridItems\":[],\"dataSources\":[],\"name\":\"Multiform Test\",\"form_ref\":\"multilinkFormTest\",\"renderables\":{\"tI0nb9iVgLGfYK8vz2gyQM\":{\"id\":\"tI0nb9iVgLGfYK8vz2gyQM\",\"name\":\"Multiform Test\",\"gridItems\":[],\"items\":[\"bn6nW2UIjJG38RCj0zgy3M\"],\"dataSources\":[],\"type\":\"FormComponent\",\"form_ref\":\"multilinkFormTest\"},\"bn6nW2UIjJG38RCj0zgy3M\":{\"label\":\"Main\",\"class_type\":\"main\",\"module\":\"phoebe\",\"id\":\"bn6nW2UIjJG38RCj0zgy3M\",\"definition\":{\"label\":\"Main\",\"showLabel\":false},\"members\":[\"main::line_1\",\"main::area_1\",\"main::line_2\",\"main::area_2\",\"main::line_3\",\"main::area_3\",\"main::multilink\"],\"type\":\"ClassComponent\",\"tagName\":\"ClassComponent\",\"name\":\"Class\",\"dataSourceMap\":[],\"items\":[\"BGWxlaBygYWAW88uaV7Gtg\",\"_v3rJCCvh1CqP8tp2ILPow\",\"svuP9lI7jYaK-5ctkKYEg0\",\"Q6i7u1IxgS2gvXR3C9Rxc0\",\"u-vXrZpHia677gk3EssuD0\"]},\"BGWxlaBygYWAW88uaV7Gtg\":{\"type\":\"MemberComponent\",\"tagName\":\"MemberElement\",\"member_ref\":\"line_1\",\"data_type_ref\":\"textshort\",\"definition\":{\"class\":\"neon\\\\\\\\core\\\\\\\\form\\\\\\\\fields\\\\\\\\Text\",\"hint\":\"\",\"label\":\"Line 1\",\"name\":\"line_1\",\"placeholder\":\"\",\"required\":false},\"link_class\":null,\"id\":\"BGWxlaBygYWAW88uaV7Gtg\",\"class_type\":\"main\",\"changes\":[]},\"_v3rJCCvh1CqP8tp2ILPow\":{\"type\":\"MemberComponent\",\"tagName\":\"MemberElement\",\"member_ref\":\"area_1\",\"data_type_ref\":\"text\",\"definition\":{\"class\":\"neon\\\\\\\\core\\\\\\\\form\\\\\\\\fields\\\\\\\\Textarea\",\"hint\":\"\",\"label\":\"Area 1\",\"name\":\"area_1\",\"placeholder\":\"\",\"required\":false},\"link_class\":null,\"id\":\"_v3rJCCvh1CqP8tp2ILPow\",\"class_type\":\"main\",\"changes\":[]},\"svuP9lI7jYaK-5ctkKYEg0\":{\"label\":\"Linked to \",\"class_type\":\"linky\",\"module\":\"phoebe\",\"id\":\"svuP9lI7jYaK-5ctkKYEg0\",\"definition\":{\"label\":\"Linked to \",\"showLabel\":true,\"isRepeater\":true,\"minInstances\":\"1\",\"maxInstances\":\"1\",\"defaultInstances\":\"1\"},\"members\":[\"linky::name\",\"linky::description\"],\"type\":\"ClassComponent\",\"tagName\":\"ClassComponent\",\"name\":\"Class\",\"dataSourceMap\":[],\"items\":[\"2ww7CWm8gkWeaE7pU-QgyM\"],\"parentClassId\":\"main\",\"relationName\":\"multilink\",\"relations\":[{\"classId\":\"bn6nW2UIjJG38RCj0zgy3M\",\"memberRef\":\"multilink\",\"method\":\"append\"}]},\"2ww7CWm8gkWeaE7pU-QgyM\":{\"type\":\"MemberComponent\",\"tagName\":\"MemberElement\",\"member_ref\":\"name\",\"data_type_ref\":\"textshort\",\"definition\":{\"class\":\"neon\\\\\\\\core\\\\\\\\form\\\\\\\\fields\\\\\\\\Text\",\"hint\":\"A name for the fun of it\",\"label\":\"Name\",\"name\":\"name\",\"placeholder\":\"\",\"required\":false},\"link_class\":null,\"id\":\"2ww7CWm8gkWeaE7pU-QgyM\",\"class_type\":\"linky\",\"changes\":[]},\"Q6i7u1IxgS2gvXR3C9Rxc0\":{\"type\":\"MemberComponent\",\"tagName\":\"MemberElement\",\"member_ref\":\"line_2\",\"data_type_ref\":\"textshort\",\"definition\":{\"class\":\"neon\\\\\\\\core\\\\\\\\form\\\\\\\\fields\\\\\\\\Text\",\"hint\":\"\",\"label\":\"Line 2\",\"name\":\"line_2\",\"placeholder\":\"\",\"required\":false},\"link_class\":null,\"id\":\"Q6i7u1IxgS2gvXR3C9Rxc0\",\"class_type\":\"main\",\"changes\":[]},\"u-vXrZpHia677gk3EssuD0\":{\"type\":\"MemberComponent\",\"tagName\":\"MemberElement\",\"member_ref\":\"area_2\",\"data_type_ref\":\"text\",\"definition\":{\"class\":\"neon\\\\\\\\core\\\\\\\\form\\\\\\\\fields\\\\\\\\Textarea\",\"hint\":\"\",\"label\":\"Area 2\",\"name\":\"area_2\",\"placeholder\":\"\",\"required\":false},\"link_class\":null,\"id\":\"u-vXrZpHia677gk3EssuD0\",\"class_type\":\"main\",\"changes\":[]}}}'),
	('daedalus', 'linky', 1, 'phoebe', 'Linked to ', 'Table linked to by the other table', 0, 1, 0, '2018-12-17 16:29:32', '2018-12-17 16:29:32', 0, '{\"class\":\"neon\\\\\\\\core\\\\\\\\form\\\\\\\\Form\",\"id\":\"linky\",\"name\":\"linky\",\"enableAjaxValidation\":true,\"enableAjaxSubmission\":false,\"label\":\"Linked to \",\"hint\":\"Table linked to by the other table\",\"inline\":false,\"visible\":true,\"order\":1,\"action\":\"\",\"readOnly\":false,\"printOnly\":false,\"validationUrl\":null,\"attributes\":[],\"fields\":{\"name\":{\"memberRef\":\"name\",\"name\":\"name\",\"dataType\":\"textshort\",\"label\":\"Name\",\"description\":\"A name for the fun of it\",\"choices\":null,\"linkClass\":null,\"mapField\":true,\"deleted\":0,\"definition\":{\"class\":\"neon\\\\\\\\core\\\\\\\\form\\\\\\\\fields\\\\\\\\Text\",\"classLabel\":\"Single Line Text\",\"id\":null,\"name\":\"name\",\"label\":\"Name\",\"hint\":\"A name for the fun of it\",\"value\":null,\"required\":false,\"dataKey\":\"name\",\"validators\":[],\"placeholder\":\"\",\"deleted\":0,\"order\":0,\"mapField\":true,\"errors\":[],\"visible\":true,\"inline\":false,\"readOnly\":false,\"attributes\":{\"class\":\"\"},\"printOnly\":false,\"showIf\":[]}},\"description\":{\"memberRef\":\"description\",\"name\":\"description\",\"dataType\":\"text\",\"label\":\"Description\",\"description\":\"Some description of something\",\"choices\":null,\"linkClass\":null,\"mapField\":false,\"deleted\":0,\"definition\":{\"class\":\"neon\\\\\\\\core\\\\\\\\form\\\\\\\\fields\\\\\\\\Textarea\",\"classLabel\":\"Multiline Text\",\"id\":null,\"name\":\"description\",\"label\":\"Description\",\"hint\":\"Some description of something\",\"value\":null,\"required\":false,\"dataKey\":\"description\",\"validators\":[],\"placeholder\":\"\",\"deleted\":0,\"order\":1,\"mapField\":false,\"errors\":[],\"visible\":true,\"inline\":false,\"readOnly\":false,\"attributes\":{\"class\":\"\"},\"printOnly\":false,\"showIf\":[]}}}}'),
	('daedalus', 'main', 1, 'phoebe', 'Main', 'Main table to set up', 0, 1, 0, '2018-12-17 16:33:03', '2018-12-17 16:33:04', 0, '{\"class\":\"neon\\\\\\\\core\\\\\\\\form\\\\\\\\Form\",\"id\":\"main\",\"name\":\"main\",\"enableAjaxValidation\":true,\"enableAjaxSubmission\":false,\"label\":\"Main\",\"hint\":\"Main table to set up\",\"inline\":false,\"visible\":true,\"order\":1,\"action\":\"\",\"readOnly\":false,\"printOnly\":false,\"validationUrl\":null,\"attributes\":[],\"fields\":{\"line_1\":{\"memberRef\":\"line_1\",\"name\":\"line_1\",\"dataType\":\"textshort\",\"label\":\"Line 1\",\"description\":\"\",\"choices\":null,\"linkClass\":null,\"mapField\":false,\"deleted\":0,\"definition\":{\"class\":\"neon\\\\\\\\core\\\\\\\\form\\\\\\\\fields\\\\\\\\Text\",\"classLabel\":\"Single Line Text\",\"id\":null,\"name\":\"line_1\",\"label\":\"Line 1\",\"hint\":\"\",\"value\":null,\"required\":false,\"dataKey\":\"line_1\",\"validators\":[],\"placeholder\":\"\",\"deleted\":0,\"order\":0,\"mapField\":false,\"errors\":[],\"visible\":true,\"inline\":false,\"readOnly\":false,\"attributes\":{\"class\":\"\"},\"printOnly\":false,\"showIf\":[]}},\"area_1\":{\"memberRef\":\"area_1\",\"name\":\"area_1\",\"dataType\":\"text\",\"label\":\"Area 1\",\"description\":\"\",\"choices\":null,\"linkClass\":null,\"mapField\":false,\"deleted\":0,\"definition\":{\"class\":\"neon\\\\\\\\core\\\\\\\\form\\\\\\\\fields\\\\\\\\Textarea\",\"classLabel\":\"Multiline Text\",\"id\":null,\"name\":\"area_1\",\"label\":\"Area 1\",\"hint\":\"\",\"value\":null,\"required\":false,\"dataKey\":\"area_1\",\"validators\":[],\"placeholder\":\"\",\"deleted\":0,\"order\":1,\"mapField\":false,\"errors\":[],\"visible\":true,\"inline\":false,\"readOnly\":false,\"attributes\":{\"class\":\"\"},\"printOnly\":false,\"showIf\":[]}},\"line_2\":{\"memberRef\":\"line_2\",\"name\":\"line_2\",\"dataType\":\"textshort\",\"label\":\"Line 2\",\"description\":\"\",\"choices\":null,\"linkClass\":null,\"mapField\":false,\"deleted\":0,\"definition\":{\"class\":\"neon\\\\\\\\core\\\\\\\\form\\\\\\\\fields\\\\\\\\Text\",\"classLabel\":\"Single Line Text\",\"id\":null,\"name\":\"line_2\",\"label\":\"Line 2\",\"hint\":\"\",\"value\":null,\"required\":false,\"dataKey\":\"line_2\",\"validators\":[],\"placeholder\":\"\",\"deleted\":0,\"order\":2,\"mapField\":false,\"errors\":[],\"visible\":true,\"inline\":false,\"readOnly\":false,\"attributes\":{\"class\":\"\"},\"printOnly\":false,\"showIf\":[]}},\"area_2\":{\"memberRef\":\"area_2\",\"name\":\"area_2\",\"dataType\":\"text\",\"label\":\"Area 2\",\"description\":\"\",\"choices\":null,\"linkClass\":null,\"mapField\":false,\"deleted\":0,\"definition\":{\"class\":\"neon\\\\\\\\core\\\\\\\\form\\\\\\\\fields\\\\\\\\Textarea\",\"classLabel\":\"Multiline Text\",\"id\":null,\"name\":\"area_2\",\"label\":\"Area 2\",\"hint\":\"\",\"value\":null,\"required\":false,\"dataKey\":\"area_2\",\"validators\":[],\"placeholder\":\"\",\"deleted\":0,\"order\":3,\"mapField\":false,\"errors\":[],\"visible\":true,\"inline\":false,\"readOnly\":false,\"attributes\":{\"class\":\"\"},\"printOnly\":false,\"showIf\":[]}},\"line_3\":{\"memberRef\":\"line_3\",\"name\":\"line_3\",\"dataType\":\"textshort\",\"label\":\"Line 3\",\"description\":\"\",\"choices\":null,\"linkClass\":null,\"mapField\":false,\"deleted\":0,\"definition\":{\"class\":\"neon\\\\\\\\core\\\\\\\\form\\\\\\\\fields\\\\\\\\Text\",\"classLabel\":\"Single Line Text\",\"id\":null,\"name\":\"line_3\",\"label\":\"Line 3\",\"hint\":\"\",\"value\":null,\"required\":false,\"dataKey\":\"line_3\",\"validators\":[],\"placeholder\":\"\",\"deleted\":0,\"order\":4,\"mapField\":false,\"errors\":[],\"visible\":true,\"inline\":false,\"readOnly\":false,\"attributes\":{\"class\":\"\"},\"printOnly\":false,\"showIf\":[]}},\"area_3\":{\"memberRef\":\"area_3\",\"name\":\"area_3\",\"dataType\":\"text\",\"label\":\"Area 3\",\"description\":\"\",\"choices\":null,\"linkClass\":null,\"mapField\":false,\"deleted\":0,\"definition\":{\"class\":\"neon\\\\\\\\core\\\\\\\\form\\\\\\\\fields\\\\\\\\Textarea\",\"classLabel\":\"Multiline Text\",\"id\":null,\"name\":\"area_3\",\"label\":\"Area 3\",\"hint\":\"\",\"value\":null,\"required\":false,\"dataKey\":\"area_3\",\"validators\":[],\"placeholder\":\"\",\"deleted\":0,\"order\":5,\"mapField\":false,\"errors\":[],\"visible\":true,\"inline\":false,\"readOnly\":false,\"attributes\":{\"class\":\"\"},\"printOnly\":false,\"showIf\":[]}},\"multilink\":{\"memberRef\":\"multilink\",\"name\":\"multilink\",\"dataType\":\"link_multi\",\"label\":\"Multilink\",\"description\":\"\",\"choices\":[],\"linkClass\":\"linky\",\"mapField\":false,\"deleted\":0,\"definition\":{\"class\":\"neon\\\\\\\\core\\\\\\\\form\\\\\\\\fields\\\\\\\\Link\",\"classLabel\":\"Link\",\"id\":null,\"name\":\"multilink\",\"label\":\"Multilink\",\"hint\":\"\",\"value\":[],\"required\":false,\"dataKey\":\"multilink\",\"validators\":[],\"placeholder\":\"- Select Objects -\",\"deleted\":0,\"order\":6,\"mapField\":false,\"errors\":[],\"visible\":true,\"inline\":false,\"readOnly\":false,\"attributes\":{\"class\":\"\"},\"printOnly\":false,\"showIf\":[],\"items\":[],\"allowClear\":true,\"dataMapProvider\":\"dds\",\"dataMapKey\":\"linky\"}}}}');

EOQ;
		neon()->db->createCommand($sql)->execute();
	}


	protected function destroyTables()
	{
		$sql =<<<EOQ
SET foreign_key_checks = 0;
DROP TABLE ddt_linky;
DROP TABLE ddt_main;
DELETE FROM dds_member WHERE `class_type` IN ('linky','main');
DELETE FROM dds_class WHERE `class_type` IN ('linky','main');
DELETE FROM phoebe_class WHERE `class_type` IN ('linky','main','multilinkformtest');
DELETE FROM phoebe_object WHERE `class_type` IN ('linky','main','multilinkformtest');
SET foreign_key_checks = 1;
EOQ;
		neon()->db->createCommand($sql)->execute();
	}
}
