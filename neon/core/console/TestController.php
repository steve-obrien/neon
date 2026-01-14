<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace neon\core\console;

use neon\core\helpers\Console;
use Symfony\Component\Process\Process;
use yii\console\Request;

/**
 * Run the test suite
 */
class TestController extends \yii\console\controller
{
	/**
	 * @var bool if true stop execution after first failed test
	 */
	public $failFast=false;
	/**
	 * @var string Comma separated list of tests to run for e.g. unit,acceptance,api
	 */
	public $run;

	/**
	 * {@inheritdoc}
	 */
	public function options($actionID)
	{
		return array_merge(
			parent::options($actionID),
			['failFast', 'run']
		);
	}

	/**
	 * run a codecept test
	 * @param string $tests
	 */
	public function actionIndex($tests='unit,api,acceptance')
	{
		$options = $this->failFast ? ' --fail-fast' : '';

		if ($this->run)
			$tests = $this->run;

		$options .= " $tests";

		$command = './codecept run ' . $options;
		$process = new Process($command, null, null, null, null);

		$this->stdout("-> Running command: $command\n", Console::FG_CYAN);
		// enable real time output
		$process->run(function ($type, $buffer) {
			echo $buffer;
		});
	}
}
