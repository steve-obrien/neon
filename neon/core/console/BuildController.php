<?php
/**
 * @copyright Copyright (c) 2020 Newicon Ltd
 */

namespace neon\core\console;

use Symfony\Component\Process\Process;
use neon\core\helpers\Console;
use yii\console\ExitCode;

/**
 * Run or check build processes for Neon
 *
 */
class BuildController extends \yii\console\controller
{
	/**
	 * Run the default
	 * This just makes sure you choose correctly
	 */
	public function actionIndex()
	{
		$this->stdout("\nMake Selection");
		$this->stdout("\n--------------");
		$this->stdout("\nChoose between core/build/app and core/build/neon\n\n");
	}

	/**
	 * Run the default build processes for the application
	 *
	 * Currently this consists of running all of the theme javascript through Babel
	 * for backwards compatibility with older browsers. In the future this
	 * will consist of every step required to ensure that the released code
	 * is compatible with all areas neon is required to work, as well as
	 * provided optimisation steps such as precompiling javascript framework
	 * code e.g. for vuejs.
	 */
	public function actionApp()
	{
		// 1. Run babel over the javascript codebase
		$this->stdout("\n");
		$this->stdout("-> Running babel over the themes/default javascript\n");
		$this->stdout("---------------------------------------------------\n");
		$this->runBabel('themes'.DIRECTORY_SEPARATOR.'default');
		$this->stdout("\n\n-> Running babel over the apps javascript\n");
		$this->stdout("-----------------------------------------\n");
		$this->runBabel('apps');

		// 2. er well the rest of whatever we are going to do
		$this->stdout("\n\n-> Done!\n\n");
	}

	/**
	 * Run the default build processes for neon
	 *
	 * Currently this consists of running all of the javascript through Babel
	 * for backwards compatibility with older browsers. In the future this
	 * will consist of every step required to ensure that the released code
	 * is compatible with all areas neon is required to work, as well as
	 * provided optimisation steps such as precompiling javascript framework
	 * code e.g. for vuejs.
	 */
	public function actionNeon()
	{
		// 1. Run babel over the javascript codebase
		$this->stdout("\n");
		$this->stdout("-> Running babel over neon javascript\n");
		$this->stdout("-------------------------------------\n");
		$neon = neon()->getalias('@neon');
		$this->runBabel($neon);

		// 2. er well the rest of whatever we are going to do
		$this->stdout("\n\n-> Done!\n\n");
	}

	/**
	 * This will run Babel over all of the neon javascript to help ensure
	 * backwards compatibility with older browsers.
	 */
	protected function runBabel($path)
	{
		$root = neon()->getAlias("@root").DIRECTORY_SEPARATOR;

		// 1. Check that Babel is installed and if not install it
		if (($result = $this->checkBabel())!=0)
			return $result;

		// 2. Create the babel config file
		if (($result = $this->checkBabelConfig($root)) != 0)
			return $result;

		// 3. Run babel over the project
		$sourceDirectory = $root.$path;;
		$outDirectory = $root.'babelOut';
		$command = [
			'babel',
			'--root-mode=upward',
			$sourceDirectory,
			'--out-dir=' . $outDirectory,
			'--out-file-extension=.build.js',
			'--ignore=' . $sourceDirectory . '/**/*.build.js,' . $sourceDirectory . '/**/vendor/*,' . $sourceDirectory . '/**/*.min.js'
		];
		$this->stdout("\n-> Running the babel command over $path\n");
		$this->stdout("->   " . implode(' ', $command) . "\n");
		$babel = new Process($command);
		$babel->setTimeout(3600);
		$babel->start();
		$babel->wait();

		// 4. Move any files to the old location with .build.js as their ending
		$this->stdout("\n-> Moving built files back into $path\n");
		$moveCommand = ['cd', "$outDirectory;", 'find', '.', '-iname', '*.build.js', '-exec', 'cp', '-v', '{}', "$sourceDirectory/{} \;"];
		
		$move = new Process($moveCommand);
		$this->stdout("->   " . $move->getCommandLine() . "\n");
		
		$move->start();
		$move->wait();

		// 5. Remove the babel directory
		$this->stdout("\n-> Removing the temporary directory $outDirectory\n");
		$deleteCommand=['rm', '-rf', $outDirectory];

		$this->stdout("->   " . implode(' ', $deleteCommand) . "\n");
		$delete = new Process($deleteCommand);
		$delete->start();
		$delete->wait();
	}

	private function checkBabel()
	{
		$process = new Process(['which', 'babel']);
		$process->start();
		$process->wait();
		if ($process->getOutput() == '') {
			$this->stderr("-> You need to install babel and make it available on the command line\n->\n", Console::FG_RED);
			$this->stderr("-> You can install babel globally using:\n->\n", Console::FG_RED);
			$this->stderr("-> npm install -g @babel/core @babel/cli @babel/preset-env @babel/polyfill\n->\n", Console::FG_RED);
			$this->stderr("-> ----- or ----- \n->\n", Console::FG_RED);
			$this->stderr("-> You can also install it in your home directory:\n->\n", Console::FG_RED);
			$this->stderr("-> cd ~\n", Console::FG_RED);
			$this->stderr("-> npm install --save-dev @babel/core @babel/cli @babel/preset-env @babel/polyfill\n->\n", Console::FG_RED);
			$this->stderr("-> Then add [path to the]/node_modules/.bin to your PATH variable e.g.\n", Console::FG_RED);
			$this->stderr("-> PATH=/Users/[youruseraccount]/node_modules/.bin:\$PATH\n", Console::FG_RED);
			return ExitCode::UNAVAILABLE;
		}
		return 0;
	}

	private function checkBabelConfig($root)
	{
		$babelConfig = $root.'babel.config.json';
		if (!file_exists($babelConfig)) {
			$this->stdout("-> Creating a default babel config file in project root at \n-> $babelConfig\n");
			file_put_contents($babelConfig, <<<EOF
{
	"presets": [
		[
			"@babel/env",
			{
				"targets": {
					"ie": "11",
					"edge": "17",
					"firefox": "60",
					"chrome": "67",
					"safari": "11.1"
				}
			}
		]
	]
}
EOF
			);
			if (!file_exists($babelConfig)) {
				$this->stderr("-> Creation of $babelConfig failed", Console::FG_RED);
				return ExitCode::UNSPECIFIED_ERROR;
			}
		}
		$this->stdout("\n-> Using the current babel config file in project root at \n->   $babelConfig\n-> Delete this and restart if incorrect\n");
		return 0;
	}
}
