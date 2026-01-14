<?php

namespace neon\user\controllers;

use neon\core\web\View;
use neon\user\services\UserInvite as UserInviteService;
use neon\user\forms\UserInvite as UserInviteForm;
use neon\user\grid\UserInviteGrid;
use \neon\core\web\AdminController;
use yii\web\NotFoundHttpException;
use Yii;

/**
 * Manage the CRUD cycle of User Invites
 */
class InviteController extends AdminController
{
	/**
	 * Add a user and invite them to register their details
	 * @return view
	 */
	public function actionIndex()
	{
		$form = new UserInviteForm();
		if (neon()->request->getIsAjax()) {
			return $form->ajaxValidation();
		}
		if ($form->processRequest()) {
			if ($form->inviteUser()) {
				neon()->session->setFlash('success', 'The user has been successfully invited');
				return $this->redirect(['/user/index/index']);
			} else {
				neon()->session->setFlash('error', 'Something went wrong while trying to invite the user');
			}
		}

		return $this->render('index', ['form' => $form]);
	}

	/**
	 * Display a grid of pending invites
	 * @return view
	 */
	public function actionPending()
	{
		$inviteGrid = new UserInviteGrid();
		$inviteGrid->load($_REQUEST);

		return $this->render('pending', [
			'inviteGrid' => $inviteGrid,
		]);
	}

	/**
	 * Resend an invite email to the invited user
	 * @param  string  $token
	 * @return view
	 */
	public function actionResend($token)
	{
		if (UserInviteService::resend($token)) {
			neon()->session->setFlash('success', 'The invite has been successfully resent');
		} else {
			neon()->session->setFlash('error', 'Something went wrong while trying to resend the invite');
		}
		return $this->redirect(['/user/invite/pending']);
	}

	/**
	 * Hard delete an invitation
	 * @param  string  $token
	 * @return view
	 */ 
	public function actionRevoke($token)
	{
		if (UserInviteService::revoke($token)) {
			neon()->session->setFlash('success', 'The invite was revoked successfully');
		} else {
			neon()->session->setFlash('error', 'Something went wrong while trying to revoke the invite');
		}
		return $this->redirect(['/user/invite/pending']);
	}

	public function rules()
	{
		return [
			// allow authenticated users if invites are allowed
			[
				'allow' => setting('user', 'allowUserInvitations', false),
				'roles' => [ 'neon-administrator' ]
			],
			[
				'allow' => setting('user', 'allowUserInvitations', false),
				'matchCallback' => function ($rule, $action) {
					return neon()->user->isSuper();
				}
			]
			// everything else is denied
		];
	}
}