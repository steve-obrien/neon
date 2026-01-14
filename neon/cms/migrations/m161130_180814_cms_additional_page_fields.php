<?php

use yii\db\Migration;

class m161130_180814_cms_additional_page_fields extends Migration
{

    public function safeUp()
    {
		$this->addColumn('cms_page', 'keywords', "VARCHAR(1000) NULL COMMENT 'Keywords for this page'");
		$this->addColumn('cms_page', 'description', "VARCHAR(1000) NULL COMMENT 'Description of this page'");
		$this->addColumn('cms_page', 'languages', "VARCHAR(1000) NULL COMMENT 'Supported languages on this page'");
		$this->addColumn('cms_page', 'page_params', "VARCHAR(10000) NULL COMMENT 'Additional parameters for this page which are interpreted directly within the templates'");
    }

    public function safeDown()
    {
		$this->dropColumn('cms_page', 'keywords');
		$this->dropColumn('cms_page', 'description');
		$this->dropColumn('cms_page', 'languages');
		$this->dropColumn('cms_page', 'page_params');
    }

}
