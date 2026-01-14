<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 17/04/2020
 * @package neon
 */

namespace neon\cms\form\fields;

use neon\cms\models\CmsUrl;
use \neon\core\form\fields\Field;
use neon\core\validators\RegularExpressionValidator;
use neon\core\validators\UniqueValidator;
use neon\daedalus\events\ObjectAddEvent;
use neon\daedalus\events\ObjectEditEvent;
use neon\daedalus\services\ddsManager\DdsObjectManager;
use yii\base\Event;
use yii\db\Exception;

class Slug extends Field
{
	public $pageId = '';
	public $prefix = '';
	public $params = '';

	/**
	 * @inheritdoc
	 */
	public function registerScripts($view)
	{
		$view->registerAssetBundle(\neon\cms\assets\CmsFormAsset::class);
	}

	/**
	 * @inheritdoc
	 */
	public function getFilterField()
	{
		return ['class' => 'text'];
	}

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		return array_merge(['pageId', 'prefix'], parent::getProperties());
	}

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		$this->addValidator([
			'class' => UniqueValidator::class,
			'targetClass'=> CmsUrl::class,
			'targetAttribute'=>'url',
		],[],'unique');

		$this->addValidator([
			'class' => RegularExpressionValidator::class,
			'pattern' => '/^\/[a-z0-9-\/]+$/',
			'message' => 'must start with "/" and contain "a-z", "0-9", "-" and "/" characters only'
		]);

		// assuming operation using DDS where form $name maps to dds classType
		if (!$this->hasForm()) return;
		$classType = $this->getForm()->getName();
		Event::on(DdsObjectManager::class, "dds.afterAdd.$classType", [$this, 'addCmsUrl']);
		Event::on(DdsObjectManager::class, "dds.afterEdit.$classType", [$this, 'editCmsUrl']);
	}

	public function validate()
	{
		$this->setErrors([]);
		if ($this->pageId === '') {
			$this->addError('You must specify a page ID');
		}
		return parent::validate();
	}

	/**
	 * @inheritdoc
	 */
	public function setValueFromDb($value)
	{
		// assume we are in edit mode
		$validators = $this->getValidators();
		if (array_key_exists('unique', $validators)) {
			$validators['unique']->filter = ['!=', 'url', $value];
		}
		return parent::setValueFromDb($value);
	}

	/**
	 * Handle after save edit
	 * @param ObjectEditEvent $event
	 */
	public function editCmsUrl(ObjectEditEvent $event)
	{
		if (!isset($event->object[$this->name])) return;
		$originalSlug = $event->originalValues[$this->name];
		$newSlug = $event->object[$this->name];
		// edit the slug in the cms_url table
		$url = CmsUrl::findOne(['url' => $originalSlug]);
		if (empty($newSlug)) return;
		if ($url === null) $url = new CmsUrl();
		$url->url = $newSlug;
		$url->nice_id = $this->pageId;
		$url->save();
	}

	/**
	 * Handle after save add
	 * @param ObjectAddEvent $event
	 */
	public function addCmsUrl(ObjectAddEvent $event)
	{
		if (!isset($event->object[$this->name])) return;
		$slug = $event->object[$this->name];
		if (empty($slug)) return;
		// add a new slug to cms url table
		$cmsUrl = new CmsUrl();
		$cmsUrl->url = $slug;
		$cmsUrl->nice_id = $this->pageId;
		$cmsUrl->save();
	}

	public function getComponentDetails()
	{
		return [
			'label' => 'Page Slug', 'icon' => 'fa fa-plug', 'group' => 'Text', 'order'=>192
		];
	}

	/**
	 * Get a displayable representation of the fields value to be output to html
	 * @return string
	 */
	public function getValueDisplay($context='')
	{
		$slug = \neon\core\helpers\Html::encode($this->getValue());
		return "<a href=\"$slug\" target=\"$slug\">$slug</a>";
	}

}
