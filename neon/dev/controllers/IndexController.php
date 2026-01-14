<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 17/09/2018
 * @package neon
 */

namespace neon\dev\controllers;

use neon\core\helpers\Arr;
use neon\core\helpers\Url;
use neon\core\web\AdminController;
use neon\dev\widgets\NeonFiddle;
use neon\dev\widgets\NeonJsFiddle;

class IndexController extends AdminController
{
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[
				'allow' => true,
				'actions' => [
					'terminal', 'terminal-php-get', 'terminal-run', 'terminal-js', 'terminal-js-run', 'terminal-js-result',
					'toggle-debug-toolbar'
				],
				'matchCallback' => function() { return neon()->dev->canAccess(); }   // only a super user
			],
			[
				'allow' => true,
				'actions' => ['index'],
				'roles' => ['neon-administrator']
			],
			[
				'allow' => false
			]
		];
	}

	public function actionIndex()
	{
		return $this->render('index', [
			'debugToolbarEnabled' => neon()->dev->isDebugToolbarEnabled()
		]);
	}

	/**
	 * Toogle the debug toolbar on/off for the user session
	 */
	public function actionToggleDebugToolbar()
	{
		if (neon()->dev->canAccess()) {
			neon()->dev->toggleDebugToolbar();
		}
		return $this->redirect(Url::toRoute('/dev/index/index'));
	}

	/**
	 * Displays the PHP terminal in the browser
	 * @return string
	 * @throws \neon\core\form\exceptions\InvalidNameFormat
	 * @throws \neon\core\form\exceptions\UnknownFieldClass
	 */
	public function actionTerminal()
	{
		$f = new \neon\core\form\Form('terminal');
		$f->add(new \neon\core\form\fields\Ide('ide'));
		$f->registerScripts();
		return $this->render('terminal.tpl', ['form' => $f]);
	}

	/**
	 * Loads a previously ran php fiddle into the php editor
	 * @return bool|string
	 */
	public function actionTerminalPhpGet()
	{
		return NeonFiddle::getFiddleContentFromFile(neon()->request->post('file'));
	}

	/**
	 * Displays the PHP terminal in the browser
	 * @return string
	 * @throws \neon\core\form\exceptions\InvalidNameFormat
	 * @throws \neon\core\form\exceptions\UnknownFieldClass
	 */
	public function actionTerminalJs()
	{
		$f = new \neon\core\form\Form('terminal');
		$f->add(new \neon\core\form\fields\Ide('ide'));
		$f->registerScripts();
		return $this->render('terminal-js.tpl', ['form' => $f]);
	}

	/**
	 * Run the terminal php code and echo the result.
	 */
	public function actionTerminalRun()
	{
		$save = (bool) neon()->request->post('save', false);
		// We want the raw value as this will be interpreted so access directly
		// this value not only is unsafe but also likely contains php!
		$php = Arr::getRequired($_POST, 'input');
		// double check we are a super user
		if (neon()->user->isSuper()) {

			echo NeonFiddle::process($php, $save);
			exit;
		}
	}

	public function actionTerminalJsRun()
	{
		// double check we are a super user
		if (neon()->user->isSuper()) {
			return ['fileId' => NeonJsFiddle::process(
				Arr::getRequired($_POST, 'script'),
				Arr::getRequired($_POST,'style'),
				Arr::getRequired($_POST,'html'),
				Arr::get($_POST, 'bundles', []),
				Arr::get($_POST, 'urls', [])
			)];
		}
	}

	public function actionTerminalJsResult($fileId)
	{
		return (new NeonJsFiddle())->renderResult($fileId);
	}
}