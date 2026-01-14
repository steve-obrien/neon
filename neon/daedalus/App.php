<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2008 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\daedalus;

use neon\core\helpers\Str;
use neon\core\interfaces\IDataMapProvider;

/**
 * The neon dds app class
 * @property \neon\daedalus\interfaces\IDdsDataTypeManagement $IDdsDataTypeManagement
 * @property \neon\daedalus\interfaces\IDdsClassManagement $IDdsClassManagement
 * @property \neon\daedalus\interfaces\IDdsObjectManagement $IDdsObjectManagement
 * @property \neon\daedalus\interfaces\IDdsObjectMapManagement $IDdsObjectMapManagement
 * @property \neon\daedalus\interfaces\IDdsObjectManagement $objectManager
 * @property \neon\daedalus\interfaces\IDdsChangeLogManagement $IDdsChangeLogManagement
 */
class App extends \neon\core\BaseApp
implements IDataMapProvider
{
	/**
	 * Prevent migrations from old names from running again
	 * @var array
	 */
	public $_migrationAlias = 'dds';

	/**
	 * Override in config with an array to change menu options
	 * for e.g:
	 * ```php
	 * 'menu' => [
	 *     [
	 *         'label' => 'App Forms',
	 *         'order' => 1020,
	 *         'url' => ['/phoebe/appforms/index/index'],
	 *         'visible' => function() { // rules },
	 *     ],
	 * ]
	 * ```
	 * @see getMenu
	 * @see setMenu
	 * @var null
	 */
	protected $_menu = null;

	/**
	 * Enable default phoebe menu to be overridden
	 * @param $menu
	 */
	public function setMenu($menu)
	{
		$this->_menu = $menu;
	}

	/**
	 * @inheritdoc
	 */
	public function getDefaultMenu()
	{
		return [
			[
				'label' => 'Database',
				'order' => 1300,
				'url' => ['/daedalus/index/index'],
				'visible' => neon()->user->hasRole('neon-administrator'),
				'active' => (is_route('/phoebe/database/*') || is_route('/daedalus/*')),
			]
		];
	}

	/**
	 * @inheritdoc
	 */
	public function configure() {

		$this->set('IDdsDataTypeManagement', [
			'class' => '\neon\daedalus\services\ddsManager\DdsDataDefinitionManager'
		]);
		$this->set('IDdsClassManagement', [
			'class' => '\neon\daedalus\services\ddsManager\DdsDataDefinitionManager'
		]);
		$this->set('IDdsChangeLogManagement', [
			'class' => '\neon\daedalus\services\ddsChangeLog\DdsChangeLogManager'
		]);
		$this->set('IDdsObjectManagement', [
			'class' => '\neon\daedalus\services\ddsManager\DdsObjectManager'
		]);
		$this->set('IDdsObjectMapManagement', [
			'class' => '\neon\daedalus\services\ddsManager\DdsObjectMapManager'
		]);
		$this->set('IDdsQueryHelpers', [
			'class' => '\neon\daedalus\services\ddsHelpers\DdsQueryHelpers'
		]);
		$this->set('DdsAppMigratorFactory', [
			'class' => '\neon\daedalus\services\ddsAppMigrator\DdsAppMigratorFactory'
		]);
	}

	/**
	 * request an IDdsDataTypeManagement interface
	 * @return \neon\daedalus\interfaces\IDdsDataTypeManagement
	 */
	public function getIDdsDataTypeManagement() {
		return $this->get('IDdsDataTypeManagement');
	}

	/**
	 * request an IDdsClassManagement interface
	 * @return \neon\daedalus\interfaces\IDdsClassManagement
	 */
	public function getIDdsClassManagement() {
		return $this->get('IDdsClassManagement');
	}

	/**
	 * request an IDdsClassManagement interface
	 * @return \neon\daedalus\interfaces\IDdsChangeLogManagement
	 */
	public function getIDdsChangeLogManagement() {
		return $this->get('IDdsChangeLogManagement');
	}

	/**
	 * request an IDdsObjectManagement interface
	 * @return \neon\daedalus\interfaces\IDdsObjectManagement
	 */
	public function getIDdsObjectManagement() {
		return $this->get('IDdsObjectManagement');
	}

	/**
	 * request an IDdsObjectManagement interface
	 * @return \neon\daedalus\interfaces\IDdsObjectManagement
	 */
	public function getObjectManager()
	{
		return $this->get('IDdsObjectManagement');
	}

	/**
	 * request an IDdsObjectMapManagement interface
	 * @return \neon\daedalus\interfaces\IDdsObjectMapManagement
	 */
	public function getIDdsObjectMapManagement() {
		return $this->get('IDdsObjectMapManagement');
	}

	/**
	 * request an IDdsQueryHelpers interface
	 * @return \neon\daedalus\interfaces\IDdsQueryHelpers
	 */
	public function getIDdsQueryHelpers() {
		return $this->get('IDdsQueryHelpers');
	}

	/**
	 * request an IDdsAppMigrator interface
	 * @param string $module  which module this is for - e.g. dds or phoebe
	 * @return \neon\daedalus\interfaces\IDdsAppMigrator
	 */
	public function getIDdsAppMigrator($module='dds') {
		$factory = $this->get('DdsAppMigratorFactory');
		return $factory::createMigrator("@system/$module/migrations");
	}

	/**
	 * @inheritdoc
	 */
	public function setup()
	{
		// set up rules
		neon()->urlManager->addRules([
			'/daedalus/api/classes' => '/daedalus/api/class/classes',
			'/daedalus/api/classes/<class>' => '/daedalus/api/class/list',
			'/daedalus/api/classes/<class>/definition' => '/daedalus/api/class/definition',
			'/daedalus/api/classes/<class>/<uuid:[0-9a-zA-Z-_]{22}>' => '/daedalus/api/class/object',
			'/daedalus/api/object/<uuid:[0-9a-zA-Z-_]{22}>' => '/daedalus/api/class/object',
			'PUT,POST /daedalus/api/object/<class>' => '/daedalus/api/class/object-add',
		]);
		// set up rules
		// make url's like https://newicon.net/daedalus/index/list?type=contact
		// become https://newicon.net/admin/database/contact
		neon()->urlManager->addRules([
			'/database/' => '/daedalus/index/index',
			'/database/<type>' => '/daedalus/index/list',
			'/database/<type>/<id:[0-9a-zA-Z-_]{22}>' => '/daedalus/index/view-object',
			'/database/<type>/<id:[0-9a-zA-Z-_]{22}>/edit' => '/daedalus/index/edit-object',
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function getName() {
		return 'Daedalus';
	}

	/**
	 * @inheritdoc
	 */
	public function getSettings() {}


	/** IDataMapProvider Methods **/
	/**==========================**/

	/**
	 * @inheritdoc
	 */
	public function getDataMapTypes()
	{
		return $this->IDdsClassManagement->getMappableClasses();
	}

	/**
	 * @inheritdoc
	 */
	public function getDataMap($key, $query='', $filters = [], $fields = [], $start = 0, $length = 100)
	{
		if (is_string($query)) {
			$filters['_map_field_'] = $query;
		}
		// convert generic form builder filters to dds ones
		return $this->IDdsObjectMapManagement->getObjectMap($key, $filters, $fields, $start, $length);
	}

	/**
	 * @inheritdoc
	 */
	public function makeMapLookupRequest($key, $ids, $fields=[])
	{
		return $this->IDdsObjectMapManagement->makeMapLookupRequest($ids, $fields, $key);
	}

	/**
	 * @inheritdoc
	 */
	public function getMapResults($requestId)
	{
		return $this->IDdsObjectMapManagement->getMapLookupResults($requestId);
	}

	/**
	 * Gets a key that represents DDS change state.
	 * When any object is edited, added or destroyed via daedalus
	 * this will return a new key
	 * @param mixed $name
	 * @return string
	 */
	public function cacheKey($key='')
	{
		$key = neon()->cache->buildKey($key);
		return neon()->cache->getOrSet('ddsCacheKey', function() {
			return date('Y-m-d H:i:s') . ' dds_' . Str::random(8);
		}) .' ' . $key;
	}

	public function clearCacheKey()
	{
		return neon()->cache->delete('ddsCacheKey');
	}
}
