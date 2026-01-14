<?php

namespace neon\admin\services\menu;

interface IManager
{
	/**
	 * @param string $menu
	 * @return array
	 */
	public function get($menu);

	public function has($menu);

	public function add($menu, $item);

}