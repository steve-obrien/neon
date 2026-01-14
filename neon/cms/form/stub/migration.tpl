<?php

use neon\core\db\Migration;

class {$className} extends Migration
{
	public function safeUp()
	{
		$this->insert('cms_page', [
			'id' => '{$id}',
			'nice_id' => '{$nice_id}',
			'name' => '{$name}',
			'title' => '{$title}',
			'status' => '{$status}',
			'layout' => '{$layout}',
			'template' => '{$template}',
			'keywords' => '{$keywords}',
			'description' => '{$description}',
			'collection' => '{$collection}',
			'collection_class' => '{$collection_class}',
 		]);
		$this->insert('cms_url', ['url' => '{$url}', 'nice_id' => '{$nice_id}']);
	}

	public function safeDown()
	{
		$this->delete('cms_url', ['nice_id'=>'{$nice_id}']);
		$this->delete('cms_page', ['nice_id'=>'{$nice_id}']);
	}
}
