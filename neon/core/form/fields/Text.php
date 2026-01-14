<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core\form\fields;

use Faker\Guesser\Name;
use neon\core\form\fields\Field;
use neon\core\grid\query\IQuery;
use neon\core\helpers\Html;
use neon\core\web\View;

/**
 * Class Text
 * @package neon\core\form
 */
class Text extends Field
{
	/**
	 * Set some allowable tags for single text fields
	 * @var string
	 */
	protected $allowableTags = "<br><em><i><u><b><s><mark><strong>";

	/**
	 * The DDS data type to store the value of the field
	 * 
	 * @var string
	 */
	public $ddsDataType = 'textshort'; // Textshort limits the field to 150 characters

	/**
	 * @inheritdoc
	 */
	public function processAsFilter(IQuery $query, $searchData=null)
	{
		$searchData = ($searchData === null) ? $this->getValue() : $searchData;
		if (!empty($searchData)) {
			$query->where($this->getDataKey(), 'like', $searchData);
		}
	}

	public function init() 
	{
		// I feel this should be handled by Daedalus directly:
		if ($this->ddsDataType === 'textshort') {
			$this->addValidator([
				'class' => \neon\core\validators\StringValidator::class,
				'max' => 150,
			]);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function fake()
	{
		$guesser = new \Faker\Guesser\Name(faker());
		$guess = $guesser->guessFormat($this->getName(), 150);
		if (is_callable($guess))
			return $guess();
		return faker()->realText(mt_rand(10, 150));
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Single Text Line',
			'icon' => 'fa fa-font',
			'group' => 'Text',
			'order' => 110,
		];
	}
}