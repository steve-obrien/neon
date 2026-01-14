<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 03/01/2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

function maxExecutionTimeRequirement()
{
	$maxExecutionTime = (int)trim(ini_get('max_execution_time'));

	$humanTime = $maxExecutionTime . ($maxExecutionTime === 0 ? ' (no limit)' : '');
	$memo = "Neon requires a minimum PHP max execution time of 120 seconds. The max_execution_time directive in php.ini is currently set to {$humanTime}.";

	return array(
		'name' => 'Max Execution Time',
		'mandatory' => false,
		'condition' => $maxExecutionTime === 0 || $maxExecutionTime >= 120,
		'memo' => $memo,
	);
}

function memoryLimitRequirement()
{
	$memoryLimit = ini_get('memory_limit');
	$bytes = getByteSize($memoryLimit);

	$humanLimit = $memoryLimit . ($memoryLimit === -1 ? ' (no limit)' : '');
	$memo = "Neon requires a minimum PHP memory limit of 256M. The memory_limit directive in php.ini is currently set to {$humanLimit}.";

	return array(
		'name' => 'Memory Limit',
		'mandatory' => false,
		'condition' => $bytes === -1 || $bytes >= 268435456,
		'memo' => $memo,
	);
}

function checkPhpExtensionVersion($extensionName, $version, $compare = '>=')
{
	if (!extension_loaded($extensionName)) {
		return false;
	}

	$extensionVersion = phpversion($extensionName);

	if (empty($extensionVersion)) {
		return false;
	}

	if (strncasecmp($extensionVersion, 'PECL-', 5) === 0) {
		$extensionVersion = substr($extensionVersion, 5);
	}

	return version_compare($extensionVersion, $version, $compare);
}


/**
 * Gets the size in bytes from verbose size representation. For example: '5K' => 5 * 1024
 *
 * @param string $value The verbose size representation.
 *
 * @return int|float The actual size in bytes
 */
function getByteSize($value)
{
	// Copied from craft\helpers\App::phpConfigValueInBytes()
	if (!preg_match('/(\d+)(K|M|G)/i', $value, $matches)) {
		return (int)$value;
	}

	$value = (int)$matches[1];

	// Multiply!
	switch (strtolower($matches[2])) {
		case 'g':
			$value *= 1024;
		// no break
		case 'm':
			$value *= 1024;
		// no break
		case 'k':
			$value *= 1024;
		// no break
	}

	return $value;
}

return [
	[
		'name' => 'PHP version 7.0+',
		'mandatory' => true,
		'condition' => version_compare(PHP_VERSION, '7.0', '>='),
		'by' => '<a href="http://neon.newicon.net/">Neon Framework</a>',
		'memo' => 'PHP 7.0 or higher is required.',
	],
	[
		'name' => 'Reflection extension',
		'mandatory' => true,
		'condition' => class_exists('Reflection', false),
		'memo' => 'The <a rel="noopener" target="_blank" href="http://php.net/manual/en/class.reflectionextension.php">Reflection</a> extension is required.',
	],
	[
		'name' => 'PCRE extension (with UTF-8 support)',
		'mandatory' => true,
		'condition' => extension_loaded('pcre') && preg_match('/./u', 'Ü') === 1,
		'memo' => 'The <a rel="noopener" target="_blank" href="http://php.net/manual/en/book.pcre.php">PCRE</a> extension is required and it must be compiled to support UTF-8.',
	],
	[
		'name' => 'SPL extension',
		'mandatory' => true,
		'condition' => extension_loaded('SPL'),
		'memo' => 'The <a rel="noopener" target="_blank" href="http://php.net/manual/en/book.spl.php">SPL</a> extension is required.'
	],
	[
		'name' => 'PDO extension',
		'mandatory' => true,
		'condition' => extension_loaded('pdo'),
		'memo' => 'The <a rel="noopener" target="_blank" href="http://php.net/manual/en/book.pdo.php">PDO</a> extension is required.'
	],
	[
		'name' => 'Multibyte String extension (with Function Overloading disabled)',
		'mandatory' => true,
		'condition' => extension_loaded('mbstring') && ini_get('mbstring.func_overload') == 0,
		'memo' => 'Craft CMS requires the <a rel="noopener" target="_blank" href="http://www.php.net/manual/en/book.mbstring.php">Multibyte String</a> extension with <a rel="noopener" target="_blank" href="http://php.net/manual/en/mbstring.overload.php">Function Overloading</a> disabled in order to run.'
	],
	[
		'name' => 'GD extension or ImageMagick extension',
		'mandatory' => true,
		'condition' => extension_loaded('gd') || (extension_loaded('imagick') && !empty(\Imagick::queryFormats())),
		'memo' => 'The <a rel="noopener" target="_blank" href="http://php.net/manual/en/book.image.php">GD</a> or <a rel="noopener" target="_blank" href="http://php.net/manual/en/book.imagick.php">ImageMagick</a> extension is required, however ImageMagick is recommended as it adds animated GIF support, and preserves 8-bit and 24-bit PNGs during image transforms.'
	],
	array(
		'name' => 'OpenSSL extension',
		'mandatory' => true,
		'condition' => extension_loaded('openssl'),
		'memo' => 'The <a rel="noopener" target="_blank" href="http://php.net/manual/en/book.openssl.php">OpenSSL</a> extension is required.'
	),
	array(
		'name' => 'cURL extension',
		'mandatory' => true,
		'condition' => extension_loaded('curl'),
		'memo' => 'The <a rel="noopener" target="_blank" href="http://php.net/manual/en/book.curl.php">cURL</a> extension is required.',
	),
	[
		'name' => 'Ctype extension',
		'mandatory' => true,
		'condition' => extension_loaded('ctype'),
		'memo' => 'The <a rel="noopener" target="_blank" href="https://secure.php.net/manual/en/book.ctype.php">ctype</a> extension is required.',
	],
	[
		'name' => 'Intl extension',
		'mandatory' => false,
		'condition' => checkPhpExtensionVersion('intl', '1.0.2', '>='),
		'by' => '<a href="http://www.php.net/manual/en/book.intl.php">Internationalization</a> support',
		'memo' => 'PHP Intl extension 1.0.2 or higher is required when you want to use advanced parameters formatting
        in <code>Yii::t()</code>, non-latin languages with <code>Inflector::slug()</code>,
        <abbr title="Internationalized domain names">IDN</abbr>-feature of
        <code>EmailValidator</code> or <code>UrlValidator</code> or the <code>yii\i18n\Formatter</code> class.'
	],
	[
		'name' => 'Fileinfo extension',
		'mandatory' => false,
		'condition' => extension_loaded('fileinfo'),
		'by' => '<a href="http://www.php.net/manual/en/book.fileinfo.php">File Information</a>',
		'memo' => 'Required for files upload to detect correct file mime-types.'
	],
	[
		'name' => 'DOM extension',
		'mandatory' => false,
		'condition' => extension_loaded('dom'),
		'by' => '<a href="http://php.net/manual/en/book.dom.php">Document Object Model</a>',
		'memo' => 'Required for REST API to send XML responses via <code>yii\web\XmlResponseFormatter</code>.'
	],
	array(
		'name' => 'iconv extension',
		'mandatory' => true,
		'condition' => function_exists('iconv'),
		'memo' => '<a rel="noopener" target="_blank" href="http://php.net/manual/en/book.iconv.php">iconv</a> is required for more robust character set conversion support.',
	),
	array(
		'name' => 'password_hash()',
		'mandatory' => true,
		'condition' => function_exists('password_hash'),
		'memo' => 'The <a rel="noopener" target="_blank" href="https://secure.php.net/manual/en/function.password-hash.php">password_hash()</a> function is required so Craft can create secure passwords.',
	),

	maxExecutionTimeRequirement(),
	memoryLimitRequirement(),

	array(
		'name' => 'Zip extension',
		'mandatory' => true,
		'condition' => extension_loaded('zip'),
		'memo' => 'The <a rel="noopener" target="_blank" href="https://secure.php.net/manual/en/book.zip.php">zip</a> extension is required for zip and unzip operations.',
	),
	array(
		'name' => 'JSON extension',
		'mandatory' => true,
		'condition' => extension_loaded('json'),
		'memo' => 'The <a rel="noopener" target="_blank" href="https://secure.php.net/manual/en/book.json.php">JSON</a> extension is required for JSON encoding and decoding.',
	),

	[
		'name' => 'MBString extension',
		'mandatory' => true,
		'condition' => extension_loaded('mbstring'),
		'by' => '<a href="http://www.php.net/manual/en/book.mbstring.php">Multibyte string</a> processing',
		'memo' => 'Required for multibyte encoding string processing.'
	],
	[
		'name' => 'OpenSSL extension',
		'mandatory' => false,
		'condition' => extension_loaded('openssl'),
		'by' => '<a href="http://www.yiiframework.com/doc-2.0/yii-base-security.html">Security Component</a>',
		'memo' => 'Required by encrypt and decrypt methods.'
	],

	array(
		'name' => 'proc_open()',
		'mandatory' => false,
		'condition' => function_exists('proc_open'),
		'memo' => 'The <a rel="noopener" target="_blank" href="https://secure.php.net/manual/en/function.proc-open.php">proc_open()</a> function is required for running tasks and tests.',
	),
	array(
		'name' => 'proc_get_status()',
		'mandatory' => false,
		'condition' => function_exists('proc_get_status'),
		'memo' => 'The <a rel="noopener" target="_blank" href="https://secure.php.net/manual/en/function.proc-get-status.php">proc_get_status()</a> function is required for running tasks and tests.',
	),
	array(
		'name' => 'proc_close()',
		'mandatory' => false,
		'condition' => function_exists('proc_close'),
		'memo' => 'The <a rel="noopener" target="_blank" href="https://secure.php.net/manual/en/function.proc-close.php">proc_close()</a> function is required for running tasks and tests.',
	),
	array(
		'name' => 'proc_terminate()',
		'mandatory' => false,
		'condition' => function_exists('proc_terminate'),
		'memo' => 'The <a rel="noopener" target="_blank" href="https://secure.php.net/manual/en/function.proc-terminate.php">proc_terminate()</a> function is required for running tasks and tests.',
	),
	array(
		'name' => 'allow_url_fopen',
		'mandatory' => false,
		'condition' => ini_get('allow_url_fopen'),
		'memo' => '<a rel="noopener" target="_blank" href="https://secure.php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen">allow_url_fopen</a> must be enabled in your PHP configuration for updates and ingesting image urls into firefly.',
	),

	[
		'name' => 'ICU version',
		'mandatory' => false,
		'condition' => defined('INTL_ICU_VERSION') && version_compare(INTL_ICU_VERSION, '49', '>='),
		'by' => '<a href="http://www.php.net/manual/en/book.intl.php">Internationalization</a> support',
		'memo' => 'ICU 49.0 or higher is required when you want to use <code>#</code> placeholder in plural rules
        (for example, plural in
        <a href=\"http://www.yiiframework.com/doc-2.0/yii-i18n-formatter.html#asRelativeTime%28%29-detail\">
        Formatter::asRelativeTime()</a>) in the <code>yii\i18n\Formatter</code> class. Your current ICU version is ' .
			(defined('INTL_ICU_VERSION') ? INTL_ICU_VERSION : '(ICU is missing)') . '.'
	],
	[
		'name' => 'ICU Data version',
		'mandatory' => false,
		'condition' => defined('INTL_ICU_DATA_VERSION') && version_compare(INTL_ICU_DATA_VERSION, '49.1', '>='),
		'by' => '<a href="http://www.php.net/manual/en/book.intl.php">Internationalization</a> support',
		'memo' => 'ICU Data 49.1 or higher is required when you want to use <code>#</code> placeholder in plural rules
        (for example, plural in
        <a href=\"http://www.yiiframework.com/doc-2.0/yii-i18n-formatter.html#asRelativeTime%28%29-detail\">
        Formatter::asRelativeTime()</a>) in the <code>yii\i18n\Formatter</code> class. Your current ICU Data version is ' .
			(defined('INTL_ICU_DATA_VERSION') ? INTL_ICU_DATA_VERSION : '(ICU Data is missing)') . '.'
	],
	[
		'name' => 'Parse Ini File',
		'mandatory' => false,
		'condition' => function_exists('parse_ini_file'),
		'by' => '<a href="http://neon.newicon.net/">Neon Framework</a>',
		'memo' => 'Required to load environment variables through the env.ini file - if not supported you can create the environment variables manually'
	],
];
