<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2008 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */
namespace neon\cms\services\cmsManager\models;

use neon\core\db\ActiveRecord;

/**
 * CmsStaticContent model
 *
 * This maintains the 'static' content of a CosMoS site. The static content
 * can be edited by admin and consists of a simple key / content relationship.
 * However it is static in contrast to the dynamic data service where the
 * type of data is not fixed and context dependent.
 */
class CmsStaticContent extends ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%cms_static_content}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['key'], 'string', 'max'=>100],
			[['page_id'], 'string', 'length'=>22],
			[['content'], 'safe']
		];
	}

	public static function primaryKey()
	{
		return ['key', 'page_id'];
	}
}