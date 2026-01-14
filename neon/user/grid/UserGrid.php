<?php
/**
 * User: steve
 * Date: 12/12/2015
 * Time: 11:28
 */

namespace neon\user\grid;

use \neon\user\models\User;
use \neon\core\helpers\Url;
use \neon\core\helpers\Html;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class UserGrid extends \neon\core\grid\Grid
{
	private $_roles = null;

	public function init()
	{
		$this->query = (new \yii\db\Query())
			->select("`user_user`.*, (SELECT GROUP_CONCAT(`item_name` ORDER BY `item_name` ASC SEPARATOR '|') FROM `user_auth_assignment` WHERE `user_user`.`id`= `user_auth_assignment`.`user_id`) AS `roles`")
			->from('user_user');

		$this->title = 'Users';

		// columns
		$this->addTextColumn('username')->setWidth('150px');
		$this->addIntColumn('uuid')->setAsIndex()->setVisible(false)->setFilter(false);
		$this->addTextColumn('email');
		$this->addTextColumn('status', ['visible'=>false]);
		$this->addTextColumn('roles');
		$this->addTextColumn('super')->setTitle('Is Superuser');
		$this->addTextColumn('failed_logins');
		$this->addTextColumn('last_login');
		$this->addTextColumn('logins')->setTitle('No. Logins');

		// date
		$this->addTextColumn('created_at');
		$this->addTextColumn('updated_at', ['title'=>'Updated At']);
		// action
		if (neon()->user->isSuper())
			$this->addTextColumn('impersonate')->setFilter(false);
		// scopes
		// $this->addScope('all','All');
		$this->addScope('active','Active');
		$this->addScope('pending','Pending');
		$this->addScope('suspended','Suspended');
		$this->addScope('banned','Banned');
		$this->addScope('super', 'Super');

		$this->setPageSize(100);
	}

	public function setup()
	{
		$neonAdmin = neon()->user->identity->hasRole('neon-administrator');
		$this->getColumn('username')->addRowActionLink('Edit', 'linkEdit');
		// can customize with loaded grid data here:
		if ($this->isScopeActive('all')) {
		 	$this->getColumn('status')->setVisible(true);
			if ($neonAdmin) {
				$this->getColumn('username')->addRowAction('actionActivate', 'Activate', ['id', 'username']);
				$this->getColumn('username')->addRowAction('actionSuspend', 'Suspend', ['class'=>'text-danger']);
			}
		}
		if ($neonAdmin) {
			if ($this->isScopeActive('suspended')) {
				$this->getColumn('username')->addRowAction('actionActivate', 'Activate');
				$this->getColumn('username')->addRowAction('actionBanned', 'Ban', ['class'=>'text-danger']);
			}
			if ($this->isScopeActive('banned')) {
				$this->getColumn('username')->addRowAction('actionActivate', 'Activate');
			}
			if ($this->isScopeActive('active')) {
				$this->getColumn('username')->addRowAction('actionSuspend', 'Suspend', ['class'=>'text-danger']);
				$this->getColumn('username')->addRowAction('actionBanned', 'Ban', ['class'=>'text-danger']);
			}
		}
	}

	/**
	 * Search Roles Column
	 *
	 * @param string $search
	 */
	public function searchRoles($search)
	{
		$roles = $this->filterRoleLabels($search);
		$this->query->andWhere(['IN', '`user_auth_assignment`.`item_name`', $roles]);
	}

	/**
	 * Render the roles with their labels not actual values
	 *
	 * @return string
	 */
	public function renderRoles($model, $key, $index, $column)
	{
		$roles = $this->getRoles();
		$column = $this->getColumn('roles');
		$search = $column->getSearch();
		$userRoles = explode('|', $column->getCellData($model));
		$displayRoles = [];
		foreach ($userRoles as $ur) {
			if (isset($roles[$ur]))
				$displayRoles[] = $column->highlight($roles[$ur]['label'], $search);
		}
		return implode(', ', $displayRoles);
	}

	public function renderUsername($model, $key, $index, $column)
	{
		$column = $this->getColumn('username');
		$username = $column->highlight($column->getCellData($model), $column->getSearch());
		return Html::a('<strong>'.$username.'</strong>', Url::to(['/user/index/view', 'id'=>$model['id']]));
	}

	public function renderSuper($model)
	{
		return $model['super'] ? 'Yes' : 'No';
	}

	public function renderImpersonate($model)
	{
		return '<a class="btn btn-default btn-sm" data-toggle="tooltip" title="Impersonate" href="'.Url::to(['/user/account/impersonate', 'user'=>$model['uuid']]).'" ><i class="fa fa-user-secret" aria-hidden="true"></i></a>';
	}

	public function linkEdit($row)
	{
		return Html::a('Edit', Url::to(['/user/index/edit', 'id'=>$row['id']]));
	}

	/**
	 * Suspend a user given by id
	 * @param int $uuid
	 * @throws HttpException
	 */
	public function actionSuspend($uuid)
	{
		// cannot suspend yourself
		if ($uuid !== $this->loggedInUuid()) {
			$user = $this->_getUser($uuid);
			$user->status = User::STATUS_SUSPENDED;
			$user->save();
		}
	}

	public function actionActivate($uuid)
	{
		$user = $this->_getUser($uuid);
		$user->status = User::STATUS_ACTIVE;
		$user->save();
	}

	public function actionBanned($uuid)
	{
		// cannot ban yourself
		if ($uuid !== $this->loggedInUuid()) {
			$user = $this->_getUser($uuid);
			$user->status = 'BANNED';
			$user->save();
		}
	}

	public function scopeSuper($query)
	{
		$query->where('super', '=', 1);
	}

	public function scopeActive($query)
	{
		$query->where('status', '=', 'ACTIVE');
	}

	public function scopePending($query)
	{
		$query->where('status', '=', 'PENDING');
	}

	public function scopeSuspended($query)
	{
		$query->where('status', '=', 'SUSPENDED');
	}

	public function scopeBanned($query)
	{
		$query->where('status', '=', 'BANNED');
	}

	/**
	 * Get the available user roles
	 *
	 * @staticvar array $roles
	 * @return array
	 */
	private function getRoles()
	{
		static $roles=null;
		if (is_null($roles)) {
			$roles = neon('user')->getRoles(true);
		}
		return $roles;
	}

	/**
	 * Get the available roles by label
	 *
	 * @return array  [label=>role]
	 */
	private function getRoleLabels()
	{
		static $labels=null;
		if (is_null($labels)) {
			$roles = $this->getRoles();
			foreach ($roles as $role => $details)
				$labels[$details['label']] = $role;
		}
		return $labels;
	}

	/**
	 * Find roles that partially match the filter on their labels
	 *
	 * @param string $filter
	 * @return array
	 */
	private function filterRoleLabels($filter)
	{
		$labels = $this->getRoleLabels();
		$roles = [];
		foreach ($labels as $label => $role) {
			if (stripos($label, $filter) !== false)
				$roles[] = $role;
		}
		return $roles;
	}

	/**
	 * Get the id of the logged in user
	 * @return int
	 */
	private function loggedInUuid()
	{
		return neon()->user->getUuid();
	}

	/**
	 * Get user active record by id
	 *
	 * @throws HttpException 500 error if user with $id does not exist
	 * @param string $uuid
	 * @return User
	 */
	private function _getUser($uuid)
	{
		$user = User::find()->where(['uuid' => $uuid])->one();
		if ($user === null) {
			throw new HttpException(500, "No user exists with id '$uuid'");
		}
		return $user;
	}

}
