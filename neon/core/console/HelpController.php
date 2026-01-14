<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace neon\core\console;

use \Yii;
use \yii\helpers\Console;
use \yii\helpers\Inflector;

/**
 * Display help information about the available commands for neo
 * Before you gasp in horror this is taking Yii and working with it to fit it in to how we want it to work
 * A simple convention of a controller class in a console folder... Not much to ask
 * Hooking this in with their current plan is kinda fugly because all the logic is in this help controller
 *
 * @todo This needs sanitizing!!
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * Hacked by Steve
 * @since 2.0
 */
class HelpController extends \yii\console\controllers\HelpController
{
	/**
	 * Displays all available commands.
	 */
	protected function getDefaultHelp()
	{
		$commands = $this->getCommandDescriptions();
		if (isset($commands['core/help'])) {
			unset($commands['core/help']);
		}
		$this->stdout("\nThis is Neon version " . \Neon::getVersion() . ".\n");
		if (!empty($commands)) {
			$this->stdout("\nThe following commands are available:\n\n", Console::BOLD);
			$len = 0;
			foreach ($commands as $command => $description) {
				$result = Yii::$app->createController($command);
				if ($result !== false) {
					/** @var $controller Controller */
					list($controller, $actionID) = $result;
					$actions = $this->getActions($controller);
					if (!empty($actions)) {
						$prefix = $controller->getUniqueId();
						foreach ($actions as $action) {
							$string = $prefix . '/' . $action;
							if ($action === $controller->defaultAction) {
								$string .= ' (default)';
							}
							if (($l = strlen($string)) > $len) {
								$len = $l;
							}
						}
					}
				} elseif (($l = strlen($command)) > $len) {
					$len = $l;
				}
				$this->stdout('- ' . $this->ansiFormat($command, Console::FG_YELLOW));
				$this->stdout(str_repeat(' ', $len + 4 - strlen($command)));
				$this->stdout(Console::wrapText($description, $len + 4 + 2), Console::BOLD);
				$this->stdout("\n");

				$result = Yii::$app->createController($command);
				if ($result !== false) {
					list($controller, $actionID) = $result;
					$actions = $this->getActions($controller);
					if (!empty($actions)) {
						$prefix = $controller->getUniqueId();
						foreach ($actions as $action) {
							$string = '  ' . $prefix . '/' . $action;
							$this->stdout('  ' . $this->ansiFormat($string, Console::FG_GREEN));
							if ($action === $controller->defaultAction) {
								$string .= ' (default)';
								$this->stdout(' (default)', Console::FG_YELLOW);
							}
							$summary = $controller->getActionHelpSummary($controller->createAction($action));
							if ($summary !== '') {
								$this->stdout(str_repeat(' ', $len + 4 - strlen($string)));
								$this->stdout(Console::wrapText($summary, $len + 4 + 2));
							}
							$this->stdout("\n");
						}
					}
					$this->stdout("\n");
				}
			}
			$scriptName = $this->getScriptName();
			$this->stdout("\nTo see the help of each command, enter:\n", Console::BOLD);
			$this->stdout("\n  $scriptName " . $this->ansiFormat('help', Console::FG_YELLOW) . ' '
							. $this->ansiFormat('<command-name>', Console::FG_CYAN) . "\n\n");
		} else {
			$this->stdout("\nNo commands are found.\n\n", Console::BOLD);
		}
	}

	/**
	 * Return a default help header.
	 * @return string default help header.
	 * @since 2.0.11
	 */
	protected function getDefaultHelpHeader()
	{
		return "\nThis is Yii version " . \Yii::getVersion() . ".\n"
			. "\nThis is Neon version " . \Neon::getVersion() . ".\n";

	}

	/**
	 * @inheritdoc
	 */
	protected function getModuleCommands($module)
	{
		$prefix = $module instanceof Application ? '' : $module->getUniqueId() . '/';

		$commands = [];

		if ($module->id != 'core') {
			foreach (array_keys($module->controllerMap) as $id) {
				$commands[] = $prefix . $id;
			}
		}

		// only include commands from apps.
		$controllerPath = $module->getControllerPath();
		if (is_dir($controllerPath)) {
			$files = scandir($controllerPath);
			foreach ($files as $file) {
				if (!empty($file) && substr_compare($file, 'Controller.php', -14, 14) === 0) {
					$controllerClass = $module->controllerNamespace . '\\' . substr(basename($file), 0, -4);
					if ($this->validateControllerClass($controllerClass)) {
//						if ($prefix == 'core' || $prefix == 'core/')
//							continue;
						$commands[] = $prefix . Inflector::camel2id(substr(basename($file), 0, -14));
					}
				}
			}
		}

		foreach ($module->getModules() as $id => $child) {
			if (($child = $module->getModule($id)) === null) {
				continue;
			}
			foreach ($this->getModuleCommands($child) as $command) {
				$commands[] = $command;
			}
		}

		return $commands;
	}
}
