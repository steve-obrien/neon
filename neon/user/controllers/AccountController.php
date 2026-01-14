<?php

namespace neon\user\controllers;

use neon\core\form\Form;
use neon\core\web\AdminController;
use neon\core\web\Response;
use yii\base\Model;
use yii\web\BadRequestHttpException;
use \neon\user\forms\PasswordReset;
use neon\user\models\User;
use neon\user\models\UserInvite;

/**
 * The account controller contains actions to do with authentication.
 * Log in, logging out, password reset
 */
class AccountController extends AdminController
{

	public $layout = '//login';

	public function rules()
	{
		return [
			[
				'allow' => true,
				'actions' => ['login', 'logout', 'request-password-reset', 'reset-password', 'impersonate-restore'],
				'roles' => [ '?', '@' ] // any guest user or logged in user
			],
			[
				'allow' => setting('user', 'allowUserInvitations', false), // Allow registration if allowed in settings
				'actions' => ['register'],
				'roles' => [ '?', '@' ]
			],
			// allow users with neon-administrator access to all functions defined
			[
				'allow' => true,
				'actions' => ['index', 'impersonate'],
				'roles' => [ 'neon-administrator' ]
			],
			// everything else is denied
		];
	}

	/**
	 * User login page
	 *
	 * @return string|Response if successful login
	 */
	public function actionLogin()
	{
		$model = new \neon\user\forms\Login();
		if ($model->load(neon()->request->post()) && $model->login()) {
			return $this->redirect(neon()->user->getReturnUrl());
		} else {
			return $this->render('login', [
				'model' => $model,
			]);
		}
	}

	/**
	 * Log out a user
	 *
	 * @return Response
	 */
	public function actionLogout()
	{
		neon()->user->logout();
		return $this->goHome();
	}

	/**
	 * Requests password reset.
	 * @throws \Exception if no admin email address is set
	 * @return mixed
	 */
	public function actionRequestPasswordReset()
	{
		$model = new \neon\user\forms\PasswordResetRequest();
		if ($model->load(neon()->request->post()) && $model->validate()) {
			if ($model->sendEmail()) {
				neon()->session->setFlash('success', 'Check your email for further instructions.');
			} else {
				neon()->session->setFlash('error', 'Sorry, we are unable to reset the password for the email provided.');
			}
		}

		return $this->render('request-password-reset', [
			'model' => $model,
		]);
	}

	/**
	 * Resets password.
	 *
	 * @param string $token
	 * @return mixed
	 * @throws BadRequestHttpException
	 */
	public function actionResetPassword($token)
	{
		try {
			$model = new PasswordReset($token);
		} catch (\Exception $e) {
			throw new BadRequestHttpException($e->getMessage());
		}
		if ($model->load(neon()->request->post()) && $model->validate() && $model->resetPassword()) {
			neon()->session->setFlash('success', PasswordReset::$successResetMessage);
			return $this->redirect('login');
		}
		return $this->render('reset-password', [
			'model' => $model,
		]);
	}

	/**
	 * Registers an invited user
	 * @param  string  $token  invitation token
	 * @return  bool|view
	 */
	public function actionRegister($token)
	{
		$form = new \neon\user\forms\Register();

		if (neon()->request->isAjax) {
			return $form->ajaxValidation();
		}
		
		if (neon()->user->isLoggedIn()) {
			return $this->redirect(neon()->user->getReturnUrl());
		}

		if (!UserInvite::isInviteTokenValid($token)) {
			neon()->session->setFlash('error', UserInvite::getInvalidInviteMessage($token));
			return $this->redirect('login');
		}
		if ($form->processRequest()) {
			$user = $form->register($token);
			if ($user) {
				neon()->user->login($user);
				neon()->session->setFlash('success', UserInvite::$successfulRegistrationMessage);
				return $this->redirect(neon()->user->getReturnUrl());
			}
		} else {
			return $this->render('register', [
				'form' => $form,
			]);
		}
	}

	/**
	 * Impersonate a user
	 * @return \yii\base\Response
	 */
	public function actionImpersonate($user)
	{
		if (neon()->user->isSuper()) {
			neon()->user->impersonate($user);
		}
		return $this->redirect(neon()->user->getReturnUrl());
	}

	/**
	 * restore the session of an impersonated user to the original user
	 * i.e. reverse the effects of impersonate
	 * @return \yii\base\Response
	 */
	public function actionImpersonateRestore()
	{
		neon()->user->impersonateRestore();
		return $this->redirect(neon()->request->referrer);
	}

}