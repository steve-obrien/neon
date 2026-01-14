<?php

use \neon\core\db\Migration;

use \neon\cms\services\cmsManager\models\CmsStaticContent;
use \neon\cms\models\CmsPage;

class m170112_211700_change_dds_page_ids_to_uuid64 extends Migration
{
    public function safeUp()
    {
    	$cms_page = CmsPage::tableName();
    	// Step 1
		// ------
    	// loop through all members in the DDS and find all members with a definition using
		// {class: neon\\cms\\form\\fields\\PageSelector}
		// or where the definition uses a `dataMapKey` of `pages` and a `dataMapProvider` of `cms`
		$members = neon()->db->query()->select('*')->from('dds_member')->where('definition LIKE \'{"class":"neon__cms__form__fields__PageSelector%\'')->all();

		// Step 2
		// ------
		// With each found member row:
		// - change the column `data_type_ref` to a `uuid64`
		// Update all data types to uuid64
		$this->execute("UPDATE dds_member SET data_type_ref = 'uuid64' WHERE definition LIKE '{\"class\":\"neon__cms__form__fields__PageSelector%'");
		// - note the `class_type` and create the $ddt_table_name from `'ddt_' . class_type`
		// - note the `member_ref` as the $column in the ddt table  - this column will hold the previous integer based page id
		//   which will match the cms_page.old_id column
		foreach ($members as $member) {
			$class_type =  $member['class_type'];
			$ddt_table_name =  'ddt_' . $class_type;
			$column = $member['member_ref'];

			// Step 3
			// ------
			// Alter the $column in $ddt_table_name to be a uuid64 (char(22))
			$exists = $this->db->createCommand("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE `table_name` = '$ddt_table_name' AND `table_schema` = '". env('DB_NAME')."' AND column_name = '$column'")->queryScalar();
			if ($exists) {
				$this->alterColumn($ddt_table_name, $column, $this->char(22)->comment("A uuid using base 64"));
				// Update the $column value in every row in the $ddt_table_name by matching it to the cms_page.old_id
				// and replacing with the cms_page.id
				$this->execute("UPDATE $ddt_table_name JOIN $cms_page ON $cms_page.old_id = $ddt_table_name.$column SET $ddt_table_name.$column = $cms_page.id");
			} else {
				// should be a rare edge case.
				// The member exists but not the column in the table - we should delete this member!
				$this->execute("DELETE FROM dds_member WHERE class_type = '$class_type' AND member_ref = '$column'");
			}
		}
		$this->dropColumn($cms_page, 'old_id');
    }

    public function safeDown()
    {
    	echo "There is no coming back from this!\n";
    }
}