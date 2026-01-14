<?php

use neon\core\db\Migration;

class m20200620_200011_cms_cms_page_collections_and_code extends Migration
{
	public function safeUp()
	{
		$this->addColumn('cms_page', 'collection', $this->boolean()->defaultValue(false)->comment('Whether this page represents a collection of items - if so a collection_class should be defined that represents the objects in the collection'));
		$this->addColumn('cms_page', 'collection_class', $this->string()->defaultValue(null)->comment('The dds class for the collection.  This means the page can represent an item from this collection - defining the class enables meta tags and page content to access the class member fields'));
		$this->addColumn('cms_page', 'code_head', $this->text()->comment('Code inserted in the <head> tag of the page'));
		$this->addColumn('cms_page', 'code_body', $this->text()->comment('Code inserted before the </body> tag of the page'));
	}

	public function safeDown()
	{
		$this->dropColumn('cms_page', 'collection');
		$this->dropColumn('cms_page', 'collection_class');
		$this->dropColumn('cms_page', 'code_head');
		$this->dropColumn('cms_page', 'code_body');
	}
}
