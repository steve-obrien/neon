<?php

/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 25/05/2017
 * @package neon
 */

namespace neon\firefly\controllers\api;

use neon\core\web\ApiController;
use neon\firefly\services\fileManager\actions\DownloadAction;
use neon\firefly\services\fileManager\actions\UploadAction;
use neon\firefly\services\fileManager\actions\MetaAction;

class FileController extends ApiController
{
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		$rules = neon()->firefly->fileAccessRules;
		return empty($rules) ? parent::rules() : $rules;
	}

	public function verbs()
	{
		return [
			'meta' => ['GET', 'POST'],
			'upload' => ['POST'],
			'download' => ['GET', 'POST'],
			'update' => ['POST']
		];
	}

	/**
	 * @param $id
	 * @return array
	 * @throws \yii\base\InvalidConfigException
	 */
	public function actionUpdate($id)
	{
		$updates = neon()->request->getBodyParams();
		return neon()->firefly->fileManager->update($id, $updates);
	}

	/**
	 * Replaces a file with another but maintains the originUuid so all references remain.
	 * We keep the original by swapping the uuid to the 
	 * 
	 * @param mixed $originalUuid
	 * @param mixed $replaceUuid
	 * @return void
	 */
	public function actionReplace($originalUuid, $replaceUuid)
	{
		// find the original
		// find the replacement
		// (save the original file path as a version?)
		// set the original file path to the replacement file path
		// clear the firefly cache for this file
	}

	/**
	 * Gets chatgpt to write alt text for you
	 * @param mixed $uuid
	 * @return void
	 */
	public function actionGenerateAlt($uuid)
	{
		$ai = new \neon\core\Ai();

		// The binary string of your image
		$binary_image = neon()->firefly->imageManager->process($uuid, [
			'f'=>'webp', 'w'=>250, 'h'=>250
		]);
		$base64_image = base64_encode($binary_image);
		$data_url = 'data:image/webp;base64,' . $base64_image; // Adjust 'image/jpeg' to the correct MIME type of your image
		
		$ai->addMessage('system', "You generate alt image descriptions from given image input. Only respond with text appropriate for the img html element alt tag content ");
		$ai->addMessage('user', [ [ "type" => "image_url", "image_url" => [ "url" => $data_url ] ] ]);
		
		$ai->eventStream();
		exit;
	}

	public function actions()
	{
		return [
			'upload' => UploadAction::class,
			'meta' => MetaAction::class,
			'download' => DownloadAction::class,
		];
	}
}
