<?php

use neon\core\db\Migration;

/**
 * Create names based on the nice ids for blank name sites
 */
class m20200111_170648_cms_create_cms_page_names extends Migration
{
	public function safeUp()
	{
		// Create a name for all pages with no name based on the nice id
		$pages = neon()->db->createCommand("SELECT * FROM `cms_page` WHERE `name` IS NULL")->queryAll();
		foreach ($pages as $page) {
			$name = $this->calculateNameFromNice($page['nice_id']);
			$this->execute("UPDATE `cms_page` SET `name`='$name' WHERE `nice_id`='$page[nice_id]' LIMIT 1");
		}
	}

	public function safeDown()
	{
		// There is no valid down migration here. This is reasonable but not fool proof
		// Make null any names that were equivalent to their nice id
		$pages = neon()->db->createCommand("SELECT * FROM `cms_page` WHERE `name` IS NOT NULL")->queryAll();
		foreach ($pages as $page) {
			$name = $this->calculateNameFromNice($page['nice_id']);
			if ($name == $page['name']) {
				$this->execute("UPDATE `cms_page` SET `name`=NULL WHERE `nice_id`='$page[nice_id]' LIMIT 1");
			}
		}
	}

	private function calculateNameFromNice($niceId)
	{
		return ucwords(preg_replace(['/_+/', '/ +/'], ' ', $niceId));
	}
}
