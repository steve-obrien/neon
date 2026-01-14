<?php

namespace neon\core\controllers;

use neon\core\helpers\Hash;
use neon\core\grid\GridBase;
use neon\core\web\Controller;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class FormController extends Controller
{
	/**
	 * Get a data map by its form objects field name
	 * @param string $objectToken - The token used to find the stored serialised object
	 * @param string $query  - the query string typed into a drop down box
	 * @param int $start - map offset
	 * @param int $length - the number of maps to return
	 * @return array
	 * @throws \Exception
	 */
	public function actionGetMapObjects($objectToken, $formField, $query=null, $start=0, $length=100)
	{
		/** @var \neon\core\form\Form $form */
		$form = Hash::getObjectFromToken($objectToken);
		return $form->getFieldByPath($formField)->getMapObjects($query, $start, $length);
	}

	/**
	 * Validate a form via ajax
	 * @param string $objectToken - The token used to find the stored serialised object
	 * @return array - form field errors or empty arrays for fields
	 * @throws BadRequestHttpException
	 * @throws \yii\base\UnknownPropertyException
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function actionValidate($objectToken)
	{
		/** @var \neon\core\form\Form $form */
		$form = Hash::getObjectFromToken($objectToken);
		return $form->ajaxValidation();
	}
}
