<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace neon\core\console;

use Symfony\Component\Process\Process;
use neon\core\helpers\Console;
use yii\console\ExitCode;

/**
 * Publish a new version of neon
 * This method uses the version number specified in the newicon/version file
 */
class PublishController extends \yii\console\controller
{
	/**
	 * @var bool Whether to force publish - this will override the tag if it already exists. Use with extreme caution.
	 */
	public $force;

	/**
	 * Use the version number to publish a neon version release
	 * This uses the composer version tag to make sure the git tag release and composer version stay in sync.
	 *
	 * To publish a release:
	 * 1: Update the version number in the version file to the desired new version.
	 * 2: Run the build process (./neo core/build)
	 * 3: Run and pass all of the tests (./codecept run)
	 * 4: Check all your changes in to git.
	 * 5: Then run this command (./neo publish).
	 *
	 * The command does the following:
	 * - Checks composer version number conforms to semver standard (https://semver.org/)
	 * - Checks git has no unstaged changes
	 * - Check we are on the releases branch
	 * - Asks if the build process has been run and encourages this to be done if not
	 * - Asks if tests have passed and encourages these to be run if not
	 * - Ensures there are no git tags with the same version
	 * - Creates git version tag
	 * - Pushes changes
	 *
	 * Useful git command:
	 * Under some circumstances you may discover an error or a minor change after publishing.
	 * In order to up issue the current version without changing the number you have to delete the tag:
		 * `git push --delete origin v2.0.2 && git tag -d v2.0.2`
	 * Note that in most circumstances its easier and preferable just to increment the patch number.
	 *
	 * @return boolean successful publish
	 */
	public function actionIndex()
	{
		// get version specified in composer.json
		$version = neon()->getVersion();
		// output version to be published
		$this->stdout("-> Attempting to publish neon version: $version\n", Console::FG_CYAN);

		// Check the version number conforms to semver format - 2.0.1 or 3.0.0[-beta][+build]
		if (!preg_match('/^[0-9]+\.[0-9]+\.[0-9]+([-+].*)?$/', $version, $match)) {
			$this->stderr("!! The version number is not a valid version it must be in the format '#.#.#' or '#.#.#[-description][+build]'.\n",  Console::FG_RED);
			return ExitCode::UNSPECIFIED_ERROR;
		}

		// Check all changes have been committed
		$process = new Process(['git', 'status', '--porcelain']);
		$process->start();
		$process->wait();
		if ($process->getOutput() != '') {
			$this->stderr("-> You have unstaged changes make sure everything is commited and checked in to git\n", Console::FG_RED);
			return ExitCode::UNSPECIFIED_ERROR;
		}

		// Check to see if the javascript has been built since changing
		if (!$this->confirm("=> Have you run the neon build process?\n")) {
			$this->stdout("-> Please run the build process before continuing! =>./neo core/build/neon\n",  Console::FG_CYAN);
			return ExitCode::UNSPECIFIED_ERROR;
		}

		// Check tests are passing - (well ask)
		// need to make sure that the version to update to is specified and checked in, in the composer.json file
		// this ensures the tag will have the correct version listed
		if (!$this->confirm("=> Are your tests passing?\n")) {
			$this->stdout("-> Please run all of the tests! =>./codecept run\n",  Console::FG_CYAN);
			return ExitCode::UNSPECIFIED_ERROR;
		}

		// Check we are on releases branch
		if (exec('git rev-parse --abbrev-ref HEAD') !== 'releases') {
			$this->stderr("!! You must be on the releases branch in order to publish a release\n", Console::FG_RED);
			return ExitCode::UNSPECIFIED_ERROR;
		}

		// Check there are no git tags with desired version number
		$this->stdout("-> Checking Git tags...\n", Console::FG_CYAN);
		$process = new Process(['git', 'ls-remote', '--tags']);
		$process->start();
		$process->wait();
		$gitVersions = explode("\n", $process->getOutput());
		$versions = [];
		foreach($gitVersions as $v) {
			$v = substr($v, strrpos($v, "/")+1);
			if ($v == '') continue;
			$versions[] = $v ;
		}
		if (in_array($version, $versions)) {
			if (!$this->force) {
				$this->stderr("!! This version ($version) already exists!\n!! use --force to update the tag - this is a terrible idea if the tag is used in production - in this scenario create another version\n", Console::FG_RED);
				$this->stderr("-> Update the desired version in the version file\n", Console::FG_CYAN);
				return ExitCode::UNSPECIFIED_ERROR;
			} else {
				$this->stdout("-> Running command: git push --delete origin $version && git tag -d $version\n", Console::FG_CYAN);
				$process = new Process(['git', 'push', '--delete', 'origin', $version, '&&', 'git', 'tag', '-d', $version]);
				$process->run();
			}
		}

		// Finally create tag version number and push!
		$this->stdout("-> Running command: git tag -a $version -m \"neon release $version\" && git push origin releases && git push origin refs/tags/$version\n", Console::FG_CYAN);
		$fullCommand = sprintf(
			'git tag -a %s -m "neon release %s" && git push origin releases && git push origin refs/tags/%s',
			escapeshellarg($version),
			escapeshellarg($version),
			escapeshellarg($version)
		);
		$process = Process::fromShellCommandLine($fullCommand);
		$process->run();
		$process->wait();
		if ($process->isSuccessful()) {
			$this->stdout("✔  Published at version: $version\n", Console::FG_GREEN);
			return ExitCode::UNSPECIFIED_ERROR;
		} else {
			$buffer = $process->getErrorOutput();
			$this->stderr("!! $buffer\n", Console::FG_RED);
			return ExitCode::UNSPECIFIED_ERROR;
		}
	}

	/**
	 * Return the current version of neon
	 * @return string
	 */
	public function actionVersion()
	{
		$this->stdout("Version: ".neon()->getVersion()."\n", Console::FG_GREEN);
	}

	/**
	 * @inheritdoc
	 */
	public function options($actionID)
	{
		return ['force', 'help'];
	}
}
