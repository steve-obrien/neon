<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 2019-11-03
 * @package neon
 */

namespace neon\cms\form;


class Component extends \neon\core\form\Form
{
	/**
	 * @inheritDoc
	 */
	public function init()
	{
		parent::init();
		$this->setName('component');

		$this->addFieldText('name', [
			'label'=>'Component name',
			'hint'=>'The name must be a unique string that identifies the component.  Note: A block name can only contain lowercase alphanumeric characters and dashes, and must begin with a letter, this is a html limitation.'
		])->setRequired();

		$this->addFieldText('title', [
			'label'=>'Component title',
			'hint'=>'Give the component a nice title - the component add menu will show this name.'
		])->setRequired();

		$this->addFieldTextArea('description', [
			'label'=>'Component Description',
			'hint'=>'A component description - this will be shown in the component inspector sidebar'
		])->setRequired();

		$this->addFieldText('icon', [
			'label'=>'Icon',
			'hint'=>'An icon property should be specified to make it easier to identify a component.  This can be a string class name or an svg element string'
		])->setRequired();

		$this->addFieldSelect('category', [
			'label'=>'Component Category',
			'hint'=>'Components are grouped into categories to help users browse and discover them.',
			'items' => ['common' => 'common', 'formatting' => 'formatting', 'layout' => 'layout']
		]);

		$this->addFieldText('keywords', [
			'label'=>'Keywords',
			'hint'=>'Enables users to search for components using different names for example text/paragraph.  Keywords are command separated',
		]);

		$this->addFieldText('parent', [
			'label'=>'Parent',
			'hint'=>'Components can be inserted into components that use a ComponentList. The ComponentList component handles a list of components.  Sometimes it is useful to only enable components to be added if their parent is a particular component.  For example only allowing column components to be added to a grid component. Ensures the component can only be added as a child of the parent specified.',
		]);

		$this->add([
			'name' => 'props_form',
			'class' => 'neon\core\form\fields\Builder',
			'label'=>'Props',
			'value' => '{"name": "props", "id": "props", "fields": [{"class": "text", "name":"text", "label":"Text box"}]}',
			'hint'=>'The form that creates the input parameters'
		]);

		$this->addFieldSubmit('Save');
	}
}