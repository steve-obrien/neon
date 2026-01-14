<?php

namespace neon\install;

/**
 * The noen core admin app class
 */
class App extends \neon\core\BaseApp
{
	/**
	 * @inheritdoc
	 */
	public function setup()
	{
	}
	
	/**
	 * @inheritdoc
	 */
	public function install()
	{}

	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return 'Install';
	}

}
