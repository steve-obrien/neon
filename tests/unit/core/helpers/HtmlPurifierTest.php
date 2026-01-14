<?php

namespace neon\tests\unit\core\helpers;

use HTMLPurifier_Config;
use neon\core\helpers\HtmlPurifier;
use neon\core\test\Unit;

class HtmlPurifierTest extends Unit
{
	public function testProcessAllowsTargetBlank()
	{
		$configInstance = HTMLPurifier_Config::create(null);

		$content = '<a href="https://example.com" target="_blank">link</a>';

		$purifiedContent = HtmlPurifier::process($content, $configInstance);

		$this->assertStringContainsString('target="_blank"', $purifiedContent);
	}

	public function testProcessAddsRelsForTargetBlank()
	{
		$configInstance = HTMLPurifier_Config::create(null);

		$content = '<a href="https://example.com" target="_blank">link</a>';

		$purifiedContent = HtmlPurifier::process($content, $configInstance);

		$this->assertStringContainsString('noopener', $purifiedContent);
		$this->assertStringContainsString('noreferrer', $purifiedContent);
	}
}