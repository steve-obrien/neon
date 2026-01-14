<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 2020-01-23
 * @package neon
 */

namespace neon\core\form\fields;


use neon\core\helpers\Arr;

class LocationMap extends Field
{
	public $ddsDataType = 'json';

	/**
	 * @var string - settings app responsible for defining the setting key
	 */
	public $settingsApp = '';

	/**
	 * @var string - setting key responsible for defining the setting key
	 */
	public $settingsKey = '';


	/**
	 * Get the Google maps API key,
	 * if this is not set the component should display but with options on how to set it.
	 * @return string
	 */
	public function getApiKey()
	{
		return setting($this->settingsApp, $this->settingsKey);
	}

	/**
	 * @return array
	 */
	public function getProperties()
	{
		$props = array_merge(parent::getProperties(), ['settingsApp', 'settingsKey']);
		return $props;
	}

	/**
	 * @inheritdoc
	 */
	public function getFilterField()
	{
		return ['class' => 'neon\\core\\form\\fields\\Text'];
	}

	/**
	 * @inheritdoc
	 */
	public function registerScripts($view)
	{
		$view->registerJsFile('https://maps.googleapis.com/maps/api/js?key='.$this->getApiKey());
	}

	/**
	 * This function represents the formatted data of the fields value
	 * the getValue represents the field value in the context of the input control and may have different formatting to
	 * the data wished to be returned by the form.
	 * Override this function in children Field type classes to change the output
	 * This function is called when the parent forms getData function is called
	 *
	 * @return mixed
	 */
	public function getData()
	{
		return $this->getValue();
	}

	/**
	 * @inheritdoc
	 */
	public function getValue()
	{
		if (is_string($this->_value)) {
			return json_decode($this->_value);
		}
		if (is_array($this->_value)) {
			return $this->_value;
		}
		return ['lat' => 0, 'lng' => 0, 'address' => ''];
	}

	/**
	 * This could be a string
	 */
	public function setValue($value)
	{
		if (is_string($value)) {
			// is this in the json format {"lat": "", "lng": "", "address": ""}
			$this->_value = json_decode($value, true);
		}
		$this->_value = $value;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function setValueFromDb($value)
	{
		$this->setValue($value);
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getValueDisplay($context='')
	{
		$value = $this->value;
		
		$lat = Arr::get($value, 'lat', 0); //51.45001175896699);
		$lng = Arr::get($value, 'lng', 0); //-2.592547239785281);
		
		if ($lat == 0 && $lng == 0) {
			return '';
		}

		if ($context === 'csv') {
			return "$lat, $lng";
		}

		/* height: 100px; */
		$query = Arr::query([
			'center' => "$lat,$lng",
			'zoom' => 7,
			'key' => $this->getApiKey(),
			'maptype' => 'roadmap',
			'markers' => "size:tiny|$lat,$lng",
			'scale' => 2,
			'format' => 'png'
		]);
		$src = "https://maps.googleapis.com/maps/api/staticmap?$query";
		$styles = 'width:150px; height:50px; min-height: 50px; max-width:none; max-height:none;';
		return "<img title=\"$lat, $lng\" style=\"$styles\" src=\"$src&size=150x50\"/>";
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentDetails()
	{
		return [
			'label' => 'Map Location', 'icon' => 'fa fa-map-marker', 'group' => 'Data', 'order' => 820
		];
	}
}