<?php
echo $this->render('_table', [
	'caption' => 'Application Configuration',
	'values' => [
		'OS version' => PHP_OS . ' ' . php_uname('r'),
		'Database' => neon()->db->driverName  . ' ' . neon()->db->getServerVersion(),

		'Neon Version' => neon()->getVersion(),
		'Yii Version' => \yii\BaseYii::getVersion(),
		'PHP version' =>  PHP_VERSION,
		'Environment' => YII_ENV,
		'Debug Mode' => YII_DEBUG ? 'Yes' : 'No',
		'Current Language' => !empty(neon()->language) ? neon()->language: '',
		'Source Language' => !empty(neon()->sourceLanguage) ? neon()->sourceLanguage: '',
		'Charset' => !empty(neon()->charset) ? neon()->charset : '',

		'Database Name' => env('DB_NAME')
	],
]);