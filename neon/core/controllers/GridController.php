<?php

namespace neon\core\controllers;

use neon\core\helpers\Hash;
use neon\core\grid\GridBase;
use neon\core\web\Controller;
use yii\web\BadRequestHttpException;
use \neon\core\ApplicationWeb;

class GridController extends Controller
{
	/**
	 * @param $token
	 * @throws BadRequestHttpException
	 * @throws \yii\web\NotFoundHttpException
	 * @throws \yii\web\RangeNotSatisfiableHttpException
	 */
	public function actionExportCsv($token)
	{
		$grid = Hash::getObjectFromToken($token, GridBase::class);
		$this->exportCsvFile($grid);
	}

	public function exportCsvFile($grid)
	{
		$page = 0;
		$csvFile = $this->createCSVFile();
		
		// add BOM to prevent Microsoft wetting the bed
		// https://support.microsoft.com/en-us/office/opening-csv-utf-8-files-correctly-in-excel-8a935af5-3416-4edd-ba7e-3dfd2bc4a032
		file_put_contents($csvFile, "\xEF\xBB\xBF");

		do {
			set_time_limit(30);
			$rows = $grid->getCsvData($page++);
			$hasRows = !empty($rows);
			file_put_contents($csvFile, $rows."\n", FILE_APPEND);
			unset($rows);
		} while ($hasRows === true);
		$handle = fopen($csvFile, 'r');
		
		// register an event that runs after sending the request - before PHP request closes.
		// To delete the temporary file after sending.
		neon()->on(ApplicationWeb::EVENT_APP_END, function() use ($csvFile) {
			@unlink($csvFile);
		});

		// send the file to the browser as a stream.
		return neon()->response->sendStreamAsFile($handle, $grid->title.'.csv');
	}

	private function createCSVFile()
	{
		// create a temp directory
		$tmpDir = \Yii::$app->runtimePath . '/csvtmp';
		if (!is_dir($tmpDir) && (!@mkdir($tmpDir) && !is_dir($tmpDir))) {
			throw new \yii\web\NotFoundHttpException('temp directory does not exist');
		}
		return "$tmpDir/".Hash::uuid64().".csv";
	}

	/**
	 * Process grid filter actions
	 * @param string $token
	 * @return mixed
	 * @throws BadRequestHttpException
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionFilter($token)
	{
		$grid = Hash::getObjectFromToken($token, GridBase::class);
		return $grid->run();
	}
}
