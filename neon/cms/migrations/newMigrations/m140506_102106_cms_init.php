<?php

use yii\db\Schema;
use yii\db\Migration;

class m140506_102106_cms_init extends Migration
{
	public function up()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			// http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
		}
		$connection = \Yii::$app->getDb();

		// create the various tables for dds
		$sql=<<<EOQ
-- --------------------------------------------------------

--
-- Table structure for table `cms_page`
--

DROP TABLE IF EXISTS `cms_page`;
CREATE TABLE `cms_page` (
  `id` char(22) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL COMMENT 'A uuid using base 64',
  `nice_id` varchar(100) DEFAULT NULL COMMENT 'A unique readable id name for the page, that may be displayed to the user, it also helps give context to know what the page is',
  `name` varchar(255) DEFAULT NULL COMMENT 'A human readable name for the page',
  `title` varchar(255) DEFAULT NULL COMMENT 'Page title',
  `status` varchar(255) NOT NULL DEFAULT 'DRAFT',
  `layout` varchar(255) DEFAULT NULL COMMENT 'the name of the layout template to load for this page',
  `template` varchar(255) DEFAULT NULL COMMENT 'the name of the template file',
  `keywords` varchar(1000) DEFAULT NULL COMMENT 'Keywords for this page',
  `description` varchar(1000) DEFAULT NULL COMMENT 'Description of this page',
  `languages` varchar(1000) DEFAULT NULL COMMENT 'Supported languages on this page',
  `page_params` varchar(10000) DEFAULT NULL COMMENT 'Additional parameters for this page which are interpreted directly within the templates',
  `created_at` datetime DEFAULT NULL COMMENT 'When the record was created',
  `updated_at` datetime DEFAULT NULL COMMENT 'When the record was last updated',
  `created_by` int(11) DEFAULT '0' COMMENT 'The id of the user that created this row',
  `updated_by` int(11) DEFAULT '0' COMMENT 'The id of the user that last updated this row',
  `deleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Whether this row is deleted or not',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nice_id` (`nice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `cms_static_content`
--

DROP TABLE IF EXISTS `cms_static_content`;
CREATE TABLE `cms_static_content` (
  `key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'a key to access the content',
  `page_id` char(22) CHARACTER SET latin1 COLLATE latin1_general_cs DEFAULT NULL COMMENT 'A uuid using base 64',
  `content` text COLLATE utf8mb4_unicode_ci COMMENT 'the static content',
  UNIQUE KEY `key` (`key`,`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Table structure for table `cms_url`
--

DROP TABLE IF EXISTS `cms_url`;
CREATE TABLE `cms_url` (
  `url` varchar(255) NOT NULL COMMENT 'Unique url primary key',
  `nice_id` varchar(100) NOT NULL COMMENT 'cms_page foreign key using the nice_id to maintain useful readability',
  PRIMARY KEY (`url`(150)),
  KEY `page_foreign_key` (`nice_id`),
  CONSTRAINT `page_foreign_key` FOREIGN KEY (`nice_id`) REFERENCES `cms_page` (`nice_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOQ;
		$command = $connection->createCommand($sql);
		$command->execute();
	}

	public function down()
	{
		# deletion order is important because of foreign key constraints
		$this->dropTable('cms_url');
		$this->dropTable('cms_static_content');
		$this->dropTable('cms_page');
	}
}
