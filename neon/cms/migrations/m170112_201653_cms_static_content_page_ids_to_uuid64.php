<?php

use \neon\core\db\Migration;

use \neon\cms\services\cmsManager\models\CmsStaticContent;
use \neon\cms\models\CmsPage;

class m170112_201653_cms_static_content_page_ids_to_uuid64 extends Migration
{
    public function safeUp()
    {
    	$cms_static_content = CmsStaticContent::tableName();
		$cms_page = CmsPage::tableName();

		// use a nice id
    	$this->alterColumn($cms_static_content, 'page_id', $this->char(22)->null()->comment('A uuid using base 64'));
		// update with nice_id's
		$this->execute("UPDATE $cms_static_content JOIN $cms_page ON $cms_page.old_id = $cms_static_content.page_id SET $cms_static_content.page_id = $cms_page.id");
    }

    public function safeDown()
    {
		$cms_static_content = CmsStaticContent::tableName();
		$cms_page = CmsPage::tableName();

		$this->execute("UPDATE $cms_static_content JOIN $cms_page ON $cms_page.id = $cms_static_content.page_id SET $cms_static_content.page_id = $cms_page.old_id");
		$this->alterColumn($cms_static_content, 'page_id', $this->integer(11)->null());
    }
}