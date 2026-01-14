<?php

namespace neon\core\controllers;

use neon\core\web\Controller;

class ErrorController extends Controller
{
	/**
	 * The default error handling controller
	 * Tries to find a specific view e.g. `error_404` if not uses the generic `error`
	 * Also assumes you would like a json representation of the error
	 * if the current request is ajax or the HTTP `Accept` header is application/json
	 * This is ugly being in the controller action.
	 * @return array|string
	 */
	public function actionError()
	{
		$handler = neon()->errorHandler;
		$exception = $handler->exception;
		$this->layout = '/error';
		$data = [
			'status' => (isset($exception->statusCode) && $exception->statusCode !== null)
				? $exception->statusCode
				: 500,
			'code' =>  $exception->getCode(),
		];

		if (neon()->debug) {
			$data['message'] = $exception->getMessage();
		} else {
			$data['message'] = '';
		}

		// If we have requested json we should return the error as json
		$acceptsJson = array_key_exists('application/json', neon()->request->getAcceptableContentTypes());
		if (neon()->request->getIsAjax() || $acceptsJson ) {
			return $data;
		}

		$exceptionHtml = $this->render('@yii/views/errorHandler/exception.php', [
			'handler' => $handler,
			'exception'=> $exception
		]);
		$data['handler'] = $handler;
		$data['exception'] = $exception;
		$data['exceptionHtml'] = $exceptionHtml;

		// Look up cms theme specific view file
		$errorPage = $this->getCustomErrorPage($data['status']);
		if ($errorPage !== false) {
			return $this->render($errorPage, $data);
		}
		// Look up specific framework view file e.g `core/view/error_404.php`
		$viewForError = 'error_' . $data['status'];
		if ($this->findView($viewForError)) {
			return $this->render($viewForError, $data);
		}
		// no specific view found so use the default generic error e.g. `core/views/site/error.php`
		return $this->render('error', $data);
	}

	/**
	 * Returns the custom view file or false if one does not exist
	 * @param int $errorCode e.g. 404
	 * @return bool|string
	 */
	public function getCustomErrorPage($errorCode)
	{
		$themeFileOld = neon('cms')->getThemeAlias() . "/error_$errorCode.tpl";
		$themeFile = neon('cms')->getThemeAlias() . "/pages/errors/$errorCode.tpl";
		if ($this->findView($themeFile)) {
			return $themeFile;
		}
		if ($this->findView($themeFileOld)) {
			return $themeFile;
		}
		return false;
	}
}
