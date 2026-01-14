<?php

namespace neon\tests\unit\core\view;

use neon\core\test\Unit;
use \neon\core\view\SmartySharedPlugins;

class SmartySharedPluginsTest extends Unit
{
	/** @var SmartySharedPlugins */
	protected $smartySharedPlugins;

	public function setUp(): void
	{
		$reflector = new \ReflectionClass(SmartySharedPlugins::class);
		$this->smartySharedPlugins = $reflector->newInstanceWithoutConstructor();
	}

	public function tearDown():void
	{
		$this->smartySharedPlugins = null;
	}

	/** tests for image_srcset */
	public function test_image_srcset_must_specify_src_param()
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->smartySharedPlugins->image_srcset([]);
	}

}
