<?php

namespace neon\core\web;

use neon\user\services\apiTokenManager\NullAuth;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\ContentNegotiator;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\AccessControl;
use neon\user\services\apiTokenManager\JwtTokenAuth;

/**
 * Useful controller for serving REST style API's
 *
 * The Controller implements the following steps in a RESTful API request handling cycle:
 * The steps are managed by specific behavior objects @see $this->behaviors() which act as middleware
 *
 * 1. Resolving response format (see [[ContentNegotiator]]);
 * 2. Validating request method (see [[verbs()]]).
 * 3. Authenticating user (see [[\yii\filters\auth\AuthInterface]]);
 * 4. Rate limiting (see [[RateLimiter]]);
 * 5. Formatting response data (see [[serializeData()]]).
 *
 * Output using json should loosely be based on
 * http://jsonapi.org/format/1.1/
 */
class ApiController extends Controller
{
	/**
	 * Whether or not the api requires a user to be logged in
	 * This is not used
	 * @deprecated
	 * @var boolean
	 */
	protected $_login_required = false;

	/**
	 * What role(s) must the user have?
	 * This will apply to all controller actions in sub classes
	 * for finer grained control you can override the rules function
	 * @see $this->rules()
	 * @var array
	 */
	protected $_login_roles = ['neon-administrator'];

	/**
	 * @inheritdoc
	 */
	public $enableCsrfValidation = false;

	/**
	 * @var int record the action start time
	 */
	public $actionStartTime = 0;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		// before doing anything we must turn off the session
		// this controller is for stateless non session APIs
		neon()->user->enableSession = false;
		neon()->user->loginUrl = null;
		neon()->getRequest()->enableCsrfValidation = false;
	}

	/**
	 * Set up our default authentication methods for REST style API methods
	 * @return array
	 */
	public function behaviors()
	{
		$behaviours = [
			'contentNegotiator' => [
				'class' => ContentNegotiator::class,
				'formats' => [
					'application/json' => Response::FORMAT_JSON,
					'text/html' => Response::FORMAT_HTML,
					'application/xml' => Response::FORMAT_XML,
					'application/php' => 'php',
					'text/event-stream' => Response::FORMAT_RAW
				],
			],
			'verbFilter' => [
				'class' => VerbFilter::class,
				'actions' => $this->verbs()
			],
			'compositeAuth' => [
				'class' => CompositeAuth::class,
				'authMethods' => [
					['class' => QueryParamAuth::class, 'tokenParam' => 'api_token'],
					// Note for the TokenAuth to authenticate a user the user must have been logged in
					// then TokenAuth::generateJwtToken() must be called on that page.
					// If this has been previously called then the cookie will exist and therefore this method should authenticate.
					['class' => JwtTokenAuth::class],
					// validate a token presented in the http authorization bearer auth header
					['class' => HttpBearerAuth::class],
					// This validates the request against a query token parameter - see UserApiToken
				],
			],
			'access' => [
				'class' => AccessControl::class,
				'rules' => $this->rules()
			]
		];
		return $behaviours;
	}

	/**
	 * @inheritdoc
	 */
	public function beforeAction($action)
	{
		$this->actionStartTime = microtime(true);
		// call the auth methods
		return parent::beforeAction($action);
	}

	/**
	 * @inheritdoc
	 */
	public function afterAction($action, $result)
	{
		$return = parent::afterAction($action, $result);
		if (is_array($return) && isset($return['data'])) {
			$return['meta'] = [
				'time' => [
					'total' => neon()->getExecutionTime(),
					'action' => microtime(true) - $this->actionStartTime
				]
			];
		}
		return $return;
	}

	/**
	 * The allowed http request verbs for an action
	 * For example:
	 *
	 * ```php
	 * [
	 *   'create' => ['get', 'post'],
	 *   'update' => ['get', 'put', 'post'],
	 *   'delete' => ['post', 'delete'],
	 *   '*' => ['get'],
	 * ]
	 * ```
	 */
	public function verbs()
	{
		return [
			'*' => ['post']
		];
	}

	/**
	 * A definition for an array of AccessRule objects:
	 * @see \yii\filters\AccessRule
	 *
	 * For example:
	 *
	 * ```php
	 * [
	 *     [
	 *         'allow' => true,
	 *         'actions' => ['update'], // if not specified will apply to all actions in the controller
	 *         'roles' => ['updatePost'], // the roles a user must have
	 *     ],
	 * ],
	 * ```
	 */
	public function rules()
	{
		return [
			[
				'allow' => true,
				'roles' => $this->_login_roles
			]
		];
	}
}
