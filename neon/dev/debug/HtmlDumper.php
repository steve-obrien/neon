<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 18/09/2018
 * @package neon
 */

namespace neon\dev\debug;


class HtmlDumper extends \Symfony\Component\VarDumper\Dumper\HtmlDumper
{
	protected $indentPad = '    ';
	protected $styles = [
		'default' => 'background-color:transparent; color:#000; line-height:2em; font-size:12px; font-family:Menlo, Monaco, Consolas, monospace; word-wrap: break-word; white-space: pre-wrap; position:relative; z-index:99999; word-break: break-all',
		'num' => 'color:#a626a4',
		'const' => 'color:#1299DA',
		'str' => 'color:#50a14f',
		'note' => 'color:#1299DA',
		'ref' => 'color:#A0A0A0',
		'public' => 'color:#a626a4',
		'protected' => 'color:#a626a4',
		'private' => 'color:#a626a4',
		'meta' => 'color:#1299DA',
		'key' => 'color:#50a14f',
		'index' => 'color:#1299DA',
		'ellipsis' => 'color:#FF8400',
	];

	public function __construct($output = null, $charset = null, $flags = 0)
	{
		parent::__construct($output = null, $charset = null, $flags = 0);
		$this->setDisplayOptions(['maxDepth' => 2]);
	}
}