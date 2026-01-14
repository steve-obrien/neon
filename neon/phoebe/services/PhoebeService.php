<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2018- Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\phoebe\services;

use Neon;
use neon\phoebe\interfaces\IPhoebeService;
use yii\base\Component;


class PhoebeService extends Component implements IPhoebeService
{
	/**
	 * @var string  the generic adapter path. KEY is replaced by the type key
	 */
	public $genericTypePath = 'neon\phoebe\services\adapters\ADAPTER\PhoebeType';

	/** ----------------------------- **/
	/** ---------- IPhoebe ---------- **/

	/**
	 * @inheritdoc
	 */
	public function listPhoebeTypes()
	{
		return [
			'basic' => [
				'key' => 'basic',
				'adapter' => 'basic',
				'label' => 'Basic phoebe type',
				'description' => 'For just saving classes directly in phoebe. This does no additional processing nor source control',
				'status' => 'AVAILABLE'
			],
			'daedalus' => [
				'key'=>'daedalus',
				'adapter' => 'daedalus',
				'label' => 'Database Tables',
				'description' => 'For the management of Daedalus database tables',
				'status' => 'AVAILABLE'
			],
			'applicationForm' => [
				'key' => 'applicationForm',
				'adapter' => 'appforms',
				'label' => 'Application Defined Forms',
				'description' => 'For the management of application defined forms that use daedalus database tables for storage and are under source control',
				'status' => 'AVAILABLE',
			],
			'clientForm' => [
				'key' => 'clientForm',
				'adapter' => 'clientforms',
				'label' => 'Client Defined Forms',
				'description' => 'For the management of client defined forms that are stored generically and are not under source control',
				'status' => 'WISHLIST',
			],
			'applicationPageTemplate' => [
				'key' => 'applicationPageTemplate',
				'adapter' => 'apppages',
				'label' => 'Application Defined Page Templates',
				'description' => 'For the management of application defined page templates that are stored and kept under source control',
				'status' => 'WISHLIST',
			],
			'clientPageTemplate' => [
				'key' => 'clientPageTemplate',
				'adapter' => 'clientpages',
				'label' => 'Client Defined Page Templates',
				'description' => 'For the management of client defined page templates that are stored generically and are not under source control',
				'status' => 'WISHLIST',
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function getPhoebeType($phoebeKey)
	{
		$type = null;
		$types = $this->listPhoebeTypes();
		$requested = isset($types[$phoebeKey]) ? $types[$phoebeKey] : null;
		// check that we can create the requested type
		if (!($requested && in_array($requested['status'], ['AVAILABLE', 'IN_DEVELOPMENT'])))
			return null;
		$adapter = $requested['adapter'];
		if (strpos($adapter, '\\') !== false)
			$type = $adapter;
		else
			$type = str_replace('ADAPTER', $requested['adapter'], $this->genericTypePath);
		return $type ? new $type : null;
	}

}