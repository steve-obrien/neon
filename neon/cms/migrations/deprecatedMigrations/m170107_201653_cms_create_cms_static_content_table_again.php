<?php

use yii\db\Migration;

class m170107_201653_cms_create_cms_static_content_table_again extends Migration
{
    public function safeUp()
    {
		$cms_static = 'cms_static_content';

		// recreate the table with the new structure
		$this->execute("DROP TABLE IF EXISTS $cms_static");
		$sql =<<<EOQ
CREATE TABLE IF NOT EXISTS `cms_static_content` (
  `key` varchar(100) NOT NULL COMMENT 'a key to access the content' COLLATE utf8mb4_unicode_ci,
  `page_id` INT UNSIGNED NULL COMMENT 'if provided, restrict content to the associated page',
  `content` text COMMENT 'the static content' COLLATE utf8mb4_unicode_ci,
  UNIQUE KEY (`key`, `page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOQ;
		$this->execute($sql);

		// copy over the data if it exists!
//		$sql =<<<EOQ
//INSERT INTO `cms_static_content` (`key`, `content`)
//SELECT `content_key`, `content` FROM `ddt_cms_static_content`
//EOQ;
//		$this->execute($sql);


		// and now extract page ids
//		$dds = neon('dds')->getIDdsObjectManagement();
//		$count = 0;
//		$total = 0;
//		do {
//			$rows = $dds->listObjects($cms_static, $total, true, true, $count);
//			$count += count($rows);
//			foreach ($rows as $row) {
//				$page_id = null;
//				$key = $row['content_key'];
//				if (preg_match('/(_page-[0-9]+)$/', $key, $matches)) {
//					$end = $matches[1];
//					$newquay = substr($key, 0, strlen($key)-strlen($end));
//					$page_id = substr($end,strlen('_page-'));
//					$this->execute("UPDATE $cms_static SET `key`='$newquay', `page_id`=$page_id WHERE `key`='$key'");
//				}
//			}
//		} while ($count < $total);
    }

    public function safeDown()
    {
		$this->dropTable('cms_static_content');
    }
}
