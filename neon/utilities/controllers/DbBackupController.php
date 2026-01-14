<?php


namespace neon\utilities\controllers;


use Carbon\Carbon;
use neon\core\helpers\File;
use neon\core\web\AdminController;
use yii\base\Exception;
use ZipArchive;

class DbBackupController extends AdminController
{
	public $layout = '@neon/utilities/views/layout.php';

	/**
	 * Index db back up page - display list of previous backups
	 *
	 * @return string
	 * @throws Exception
	 */
	public function actionIndex()
	{
		$this->view->title = 'Database Backup';
		return $this->render('index', ['backups' => $this->getBackups()->all()]);
	}

	/**
	 * Download specified db backup
	 *
	 * @param string $file - the name of the file, the base name without the path.
	 * @return \yii\web\Response
	 * @throws Exception
	 */
	public function actionDownload($file)
	{
		// just to be paranoid we won't use the $fileName directly - we will look to see if we can find a match
		// in the set of known backups - then use that trusted path.  This avoids any fileName hacks
		$file = $this->getBackups()->where('file', '=', $file)->first();
		if ($file === null) {
			neon()->session->setFlash('error', 'I was unable to find the backup');
			return $this->redirect('index');
		}
		return neon()->getResponse()->sendFile($this->zip($file['path']), null, [
			'mimeType' => 'application/zip',
		]);
	}

	/**
	 * Performs a new backup and downloads the resulting zip
	 *
	 * @return \yii\web\Response
	 * @throws Exception
	 */
	public function actionBackup()
	{
		$backup = neon()->db->backup();
		// use firefly?
		neon()->session->setFlash('success', 'New backup created ' . $backup);
		$zipPath = $this->zip($backup);
		return neon()->getResponse()->sendFile($zipPath, null, [
			'mimeType' => 'application/zip',
		]);
	}

	/**
	 * Zip up a file for download
	 *
	 * @param string $file - path to file
	 * @throws \Exception - if a new zip archive can not be created
	 * @return string - path to the zip file in runtime/temp folder
	 */
	private function zip($file)
	{
		File::createDirectory(neon()->getAlias('@runtime/temp'));
		$zipPath = neon()->getAlias('@runtime/temp/'.pathinfo($file, PATHINFO_FILENAME) . '.zip');

		if (is_file($zipPath) && !File::unlink($zipPath)) {
			\Neon::warning("Unable to delete the file \"{$zipPath}\": ", __METHOD__);
		}

		$zip = new ZipArchive();
		if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
			throw new \Exception('Cannot create zip at ' . $zipPath);
		}
		$zip->addFile($file, pathinfo($file, PATHINFO_BASENAME));
		$zip->close();
		return $zipPath;
	}

	/**
	 * Returns a collection of backup sql files
	 *
	 * @return \neon\core\helpers\Collection
	 * @throws Exception
	 */
	private function getBackups()
	{
		$backups = File::findFiles(neon()->db->getBackupDirectory(), ['only'=>['*.sql']]);
		return collect($backups)->map(function($file) {
			return [
				'file' => basename($file),
				'size' => filesize($file),
				'path' => $file,
				'modified' => Carbon::createFromTimestamp(filemtime($file))->format('Y-m-d H:i:s'),
			];
		})->sortByDesc('modified');
	}
}
