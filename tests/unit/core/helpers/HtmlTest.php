<?php
/**
 * Created by PhpStorm.
 * User: newicon
 * Date: 17/10/2018
 * Time: 11:10
 */

namespace neon\tests\unit\core\helpers;

use neon\core\test\Unit;
use neon\core\helpers\Html;

class HtmlTest extends Unit
{

	/**
	 * @dataProvider sanitiseProvider
	 */
	public function testHtmlSanitise($hack, $safe)
	{
		$result = Html::sanitise($hack);
		$this->assertEquals($safe, $result);
	}

	/**
	 * @dataProvider highlightProvider
	 */
	public function testHtmlHighlight($search, $html, $expected)
	{
		$this->assertEquals($expected, Html::highlight($search, $html));
	}

	public function sanitiseProvider()
	{
		// hack | safe
		return [
			[
				'<sc<script>ript>alert("hello");</sc<script>ript>',
				'alert("hello");'
			]
		];
	}

	public function highlightProvider()
	{
		// search | html | expected
		return [

			[
				'steve', // search
				'<a href="link/to/steve">steve</a>', // html
				'<a href="link/to/steve"><span class="neonSearchHighlight">steve</span></a>' // expected
			],
			[
				'over', // search
				'something over there', // html
				'<p>something <span class="neonSearchHighlight">over</span> there</p>' // expected
			],
			[
				'eve', // search
				'<a href="link/to/steve">steve <strong title="steve">steve</strong></a>', // html
				'<a href="link/to/steve">st<span class="neonSearchHighlight">eve</span> <strong title="steve">st<span class="neonSearchHighlight">eve</span></strong></a>' // expected
			],
			[
				'eve &',
				'<a href="link/to/steve">steve &amp; linda</a>',
				'<a href="link/to/steve">st<span class="neonSearchHighlight">eve &amp;</span> linda</a>'
			],
			[
				'evé', // search
				'<a href="link/to/stevé">stevé waz ere</a>', // html
				'<a href="link/to/stev%C3%A9">st<span class="neonSearchHighlight">ev&eacute;</span> waz ere</a>' // expected
			],
			[
				'eve &',
				'<a href="link/to/steve">steve & linda</a>',
				'<a href="link/to/steve">st<span class="neonSearchHighlight">eve &amp;</span> linda</a>'
			],
		];
	}
}