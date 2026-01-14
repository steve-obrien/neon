<?php

use neon\core\db\Migration;

class m20200601_123225_cms_og_fields extends Migration
{
	public function safeUp()
	{
		$this->addColumn('cms_page', 'og_title', $this->string(100)->comment('The open graph title meta tag text'));
		$this->addColumn('cms_page', 'og_title_use_seo', $this->boolean()->defaultValue(true)->comment('When true uses the seo title tag for the og_title'));
		$this->addColumn('cms_page', 'og_description', $this->string(300)->comment('The open graph description meta tag text'));
		$this->addColumn('cms_page', 'og_description_use_seo', $this->boolean()->defaultValue(true)->comment('When true uses the seo title tag for the og_title'));
		$this->addColumn('cms_page', 'og_image', $this->string(100)); // url or firefly id or {{field_name}}
	}

	public function safeDown()
	{
		// create a down migration
		$this->dropColumn('cms_page', 'og_title');
		$this->dropColumn('cms_page', 'og_title_use_seo');
		$this->dropColumn('cms_page', 'og_description');
		$this->dropColumn('cms_page', 'og_description_use_seo');
		$this->dropColumn('cms_page', 'og_image');
	}
}
