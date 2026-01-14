<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 21/09/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\core\form\fields;

/**
 * A select chain allows you to create a series of chained
 * selects so the first restricts the second which restricts
 * the third etc. The final item is stored in the database
 * and is used to run back up the chain to populate all of the
 * select boxes accordingly.
 *
 * @todo NJ 20210930 - complete this so it actually works
 *  - needs the configuration to be set and tested.
 */
class SelectChain extends Field
{
	private $_classMemberMap = [];

	public $ddsDataType = 'link_uni_object';
	public $endPoint = '';
	public $chainValues = [];

	public function getReverseMapRequest($uuid) {
		return [
			['nizf5aO4dKK4AILxbyGVPg' => 'UK'],
			[$uuid => $uuid]
		];
	}

	/**
	 * @inheritdoc
	 */
	public function setValueFromDb($value)
	{
		if (empty($value)) {
			parent::setValueFromDb($value);
		} else {
			$this->chainValues = $this->getReverseMapRequest($value);
			$lastKvp = end($this->chainValues);
			$val = empty($lastKvp[$value]) ? null : $lastKvp[$value];
			parent::setValueFromDb($val);
		}
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getFieldHtml()
	{
		return Html::tag('neon-select-chain', '', [
			'v-bind' => $this->toJson(),
			'name' => $this->getInputName()
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function getProperties()
	{
		return array_merge(parent::getProperties(), ['endPoint', 'classMemberMap', 'chainValues']);
	}

	/**
	 * Clear the end point
	 * @return string  the previously set endpoint
	 */
	public function clearEndPoint()
	{
		$endPoint = $this->endPoint;
		$this->endPoint = '';
		return $endPoint;
	}

	/**
	 * Get display value for end point
	 * @return string
	 */
	public function getEndPointDisplay()
	{
		$endPoint = '<span class="neonField_labelText">'.$this->getEndPoint().'</span>';
		return $endPoint;
	}

	/**
	 * @param array $classMemberMap
	 * @return $this
	 */
	public function setClassMemberMap($classMemberMap)
	{
		$this->_classMemberMap = $classMemberMap;
		return $this;
	}

	/**
	 * The array of classes and members to pass to the api
	 * @return array
	 */
	public function getClassMemberMap()
	{
		return $this->_classMemberMap;
	}

	/**
	 * Clear the items
	 * @return array  the previously set items
	 */
	public function clearClassMemberMap()
	{
		$classMemberMap = $this->_classMemberMap;
		$this->_classMemberMap = [];
		return $classMemberMap;
	}

	/**
	 * Get the items suitable for display in standard html elements such as checkbox
	 * lists and radio sets.
	 *
	 * This maps items to key=>value pairs and adds some extra html around the
	 * displayed text to enable enhanced css handling. Use neonField_labelText to
	 * access this in CSS
	 */
	public function getClassMemberMapDisplay()
	{
		$classMemberMap = $this->getClassMemberMap();
		foreach ($classMemberMap as $key => $value) {
			$classMemberMap[$key] = '<span class="neonField_labelText">'.$value['class'].'</span><span class="neonField_labelText">'.$value['member'].'</span>';
		}
		return $classMemberMap;
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label'=>'Select Chain', 'icon' => 'fa fa-ellipsis-h', 'group' => 'Experimental', 'order' => 1210
		];
	}
}