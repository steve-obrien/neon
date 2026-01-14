<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2008 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\dev;

use neon\dev\debug\NeonCaster;
use neon\install\helpers\InstallHelper;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\VarDumper;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use neon\dev\debug\HtmlDumper;
use yii\web\ForbiddenHttpException;
use yii\web\View;

VarDumper::setHandler(function ($var) {
	$cloner = new VarCloner();
	$cloner->addCasters(NeonCaster::getCasters());
	$dumper = 'cli' === PHP_SAPI ? new CliDumper() : new HtmlDumper();
	$dumper->dump($cloner->cloneVar($var));
});

/**
 * The neon cms app class
 *
 * @property \neon\cms\components\Page $page
 */
class App extends \neon\core\BaseApp
{
	/**
	 * @inheritdoc
	 */
	public $defaultRoute = 'index';

	/**
	 * @var array the list of IPs that are allowed to access this module.
	 * Each array element represents a single IP filter which can be either an IP address
	 * or an address with wildcard (e.g. 192.168.0.*) to represent a network segment.
	 * The default value is `['127.0.0.1', '::1']`, which means the module can only be accessed
	 * by localhost. Add a * to allow all IPs
	 */
	public $allowedIPs = ['*'];

	const ENABLE_DEBUG_TOOLBAR = 'enableDebugToolbar';

	/**
	 * @inheritdoc
	 */
	public function getDefaultMenu()
	{
		return [
			'label' => 'Developer',
			'order' => 10000,
			'url' => ['/dev/index/index'],
			'visible' => $this->canAccess(),
			'active' => is_route('/dev*'),
		];
	}

	/**
	 * @inheritdoc
	 */
	public function setup()
	{
		neon()->urlManager->addRules(['/dev/docs/<app>/<doc>' => '/dev/docs/index']);
	}

	/**
	 * Toggle the toolbar on or off
	 */
	public function toggleDebugToolbar()
	{
		// double check permissions
		if ($this->canAccess()) {
			$set = !$this->isDebugToolbarEnabled();
			neon()->session->set(self::ENABLE_DEBUG_TOOLBAR, $set);
			if (!$set) $this->unregisterDebugModule();
		}
	}

	/**
	 * Whether the debug toolbar is enabled or not
	 * @return bool
	 */
	public function isDebugToolbarEnabled()
	{
		$session = neon()->getSession();
		if (! $session->getHasSessionId() && !$session->getIsActive())
			return false;
		if (! $session->get(self::ENABLE_DEBUG_TOOLBAR))
			return false;
		return true;
	}

	/**
	 * bootstrap the debug toolbar (if necessary)
	 */
	public function debugBootstrap()
	{
		// disable the toolbar during install
		if (InstallHelper::isInstallRoute())
			return;
		if ($this->isDebugToolbarEnabled() == false)
			return;
		if (class_exists('\yii\debug\Module')) {
			\yii\debug\Module::setYiiLogo("data:image/svg+xml;utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 11 11'%3E%3Cpath d='M3.2 11V5c0-1 .2-1.6.7-2.1.5-.5 1.1-.7 1.9-.7.7 0 1.2.1 1.5.5.3.4.5 1 .5 1.9V11h2.7V4.4c0-1.5-.3-2.6-1-3.3C9 .3 8 0 6.8 0 6 0 5.3.1 4.7.5c-.6.3-1.1.7-1.5 1.3V.2H.4V11h2.8z' fill='%230066ff'/%3E%3C/svg%3E");
			$panels = [
				'config' => ['class' => 'neon\dev\debug\panels\ConfigPanel'],
				'request' => ['class' => 'yii\debug\panels\RequestPanel'],
				'log' => ['class' => 'yii\debug\panels\LogPanel'],
				'profiling' => ['class' => 'yii\debug\panels\ProfilingPanel'],
				'db' => ['class' => 'yii\debug\panels\DbPanel'],
				'event' => ['class' => 'neon\dev\debug\panels\EventPanel'],
				'assets' => ['class' => 'yii\debug\panels\AssetPanel'],
				'mail' => ['class' => 'yii\debug\panels\MailPanel'],
				'timeline' => ['class' => 'yii\debug\panels\TimelinePanel'],
				'user' => ['class' => 'neon\dev\debug\panels\UserPanel'],
				'router' => ['class' => 'yii\debug\panels\RouterPanel'],
			];
			if (class_exists(\yii\queue\debug\Panel::class)) {
				$panels['queue'] = \yii\queue\debug\Panel::class;
			}
			neon()->setModule('debug', [
				'class' => '\yii\debug\Module',
				'allowedIPs' => ['*'],
				'panels' => $panels
			]);
			/** @var \yii\debug\Module $module */
			$module = neon()->getModule('debug');
			$module->bootstrap(neon());
		}
	}

	/**
	 * Unregisters the Debug module's end body event.
	 */
	public function unregisterDebugModule()
	{
		$debug = $this->getModule('debug', false);
		if ($debug !== null) {
			$this->getView()->off(View::EVENT_END_BODY, [$debug, 'renderToolbar']);
		}
	}

	/**
	 * Whether the module can be accessed by the current user
	 *
	 * @return boolean false means no access
	 */
	public function canAccess()
	{
		if (! neon()->user->hasRole('neon-developer')) {
			\Neon::warning('Access to neon/dev is denied - you must have the the neon developer role', __METHOD__);
			return false;
		}
		if (! $this->ipCanAccess()) {
			\Neon::warning('Access to neon/dev is denied due to IP address restriction. The requested IP is ', __METHOD__);
			return false;
		}
		return true;
	}

	/**
	 * @return bool
	 */
	protected function ipCanAccess()
	{
		// if IP check is on
		$ip = neon()->getRequest()->getUserIP();
		foreach ($this->allowedIPs as $filter) {
			if ($filter === '*' || $filter === $ip || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos))) {
				return true;
			}
		}
		return false;
	}
}
