<?php

namespace neon\dev\console;

use neon\dev\debug\NeonCaster;
use Psy\Shell;
use Psy\Configuration;

/**
 * Create a PHP neon interactive shell
 */
class ShellController extends \yii\console\controller
{
	public $defaultAction = 'run';

	/**
	 * Play with your app in a PHP shell - try neon()->version
	 */
	public function actionRun($include=[])
	{
		$config = new Configuration;
		$config->getPresenter()->addCasters(NeonCaster::getCasters());

		$shell = new Shell($config);
		$shell->setIncludes($include);

		$shell->run();
	}
}