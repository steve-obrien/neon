<?php

namespace neon\user\controllers;

use neon\core\web\View;
use neon\user\models\User;
use neon\user\forms\UserCreate;
use neon\user\forms\UserEdit;
use neon\user\forms\UserInvite;
use \neon\core\web\AdminController;
use yii\web\NotFoundHttpException;
use Yii;

/**
 * Manage the CRUD cycle of Users
 * Note this is specifically concerned with their user authenitcation accounts.
 * This should not contain more user level profile info.
 */
class IndexController extends AdminController
{
	/**
	 * List all users
	 * @return String
	 */
	public function actionIndex()
	{
		$userGrid = new \neon\user\grid\UserGrid();

		return $this->render('index', [
			'userGrid' => $userGrid,
		]);
	}

	/**
	 * Add a user
	 * @return View
	 */
	public function actionAdd()
	{
		$form = new UserCreate();

		if (neon()->request->getIsAjax()) {
			return $form->ajaxValidation();
		}
		if ($form->processRequest()) {
			if ($form->signupNewUser()) {
				neon()->session->setFlash('success', 'The user has been successfully added');
				return $this->redirect(['/user/index/index']);
			} else {
				neon()->session->setFlash('error', 'Something went wrong while trying to save the user');
			}
		}

		return $this->render('add', ['form' => $form]);
	}

	/**
	 * Displays a single User model.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionView($id)
	{
		return $this->render('view', [
			'model' => $this->findModel($id),
		]);
	}

	/**
	 * Edits and updates an existing User model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionEdit($id)
	{
		$user = $this->findModel($id);
		$form = new UserEdit();

		if (neon()->request->getIsAjax()) {
			return $form->ajaxValidation();
		}
		if ($form->processRequest()) {
			if ($form->updateExistingUser($user)) {
				neon()->session->setFlash('success', 'The user has been successfully updated');
				return $this->redirect(['/user/index/index']);
			} else {
				neon()->session->setFlash('error', 'Something went wrong while trying to update the user');
			}
		}
		$form->load($user);
		return $this->render('edit', ['form' => $form]);
	}

	/**
	 * Delete a user
	 * @param int $id
	 */
	public function actionDelete($id)
	{
		if ($id) {
			$loggedInUser = neon()->user;
			if ($loggedInUser->isSuper()) {
				if ($loggedInUser->getId() != $id) {
					$user = $this->findModel($id);
					if ($user->delete())
						neon()->session->setFlash('success', 'The user has been successfully deleted');
					else
						neon()->session->setFlash('error', 'Something went wrong while trying to delete the user');
				} else
					neon()->session->setFlash('error', 'Sorry, you cannot delete yourself from the system.');
				return $this->redirect(['/user/index/index']);
			}
			neon()->session->setFlash('error', 'You do not have permissions to delete the user');
		}
	}

	/**
	 * Finds the User model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param integer $id
	 * @return User the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id)
	{
		if (($model = User::findOne($id)) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException('The requested page does not exist.');
		}
	}
}