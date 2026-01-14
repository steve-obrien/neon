<?php
namespace neon\admin\services\menu;

use \neon\admin\services\menu\IManager;
use \neon\core\helpers\Arr;

class Manager implements IManager
{
	/**
	 * an array of menus by key name
	 * 'admin' => [// admin menu items]
	 * @var array
	 */
	protected $_menus = [];

	/**
	 * Return boolean if the menu manager has the specified menu
	 * @param string $menu menu name
	 * @return boolean
	 */
	public function has($menu)
	{
		return isset($this->_menus[$menu]);
	}

	/**
	 * This is a special menu - one that is defined by each applications `getMenu` function
	 */
	public function getMainAdminMenu()
	{
		foreach (neon()->getActiveApps() as $app) {
			$menu = $app->getMenu();
			if (is_array($menu)) {
				if (is_int(key($menu))) {
					foreach ($menu as $menuItem)
						$this->add('admin', $menuItem);
				} else {
					$this->add('admin', $app->getMenu());
				}
			}
		}
		return $this->get('admin');
	}

	/**
	 * Get a menu array specified by its key name e.g.
	 * ~~~
	 * Neon::app('admin')->menuManager->get('admin')
	 * ~~~
	 * @param string $menu
	 * @return array - empty array if a menu does not exist
	 */
	public function get($menu)
	{
		// does the menu exist in cache?

		// look up menu
		// maybe go off to db?

		// cache menu

		if (!isset($this->_menus[$menu])) {
			return [];
		}
		Arr::multisort($this->_menus[$menu]['items'], 'order');
		return $this->_menus[$menu];
	}

	/**
	 * Add an item to a menu
	 * @param string $menu
	 * @param array $item
	 * a menu item array definition, minimum definition:
	 * ['label' => 'The Label']
	 * @return void
	 */
	public function add($menu, $item)
	{
		if (!isset($this->_menus[$menu])) {
			$this->_menus[$menu]['items'] = [];
		}
		$this->_menus[$menu]['items'][] = $item;
	}
}