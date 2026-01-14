<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 29/11/2016 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */
class DynamicDataTypeCest
{
	/**
	 * @var \neon\user\models\User
	 */
	public $user;

	public function _before(AcceptanceTester $I)
	{
		neon()->db->createCommand('TRUNCATE user_session');
		neon_test_server_restart();
		// delete all the users and create a fresh one.
		\neon\user\models\User::deleteAll();
		$user = new \neon\user\models\User();
		$user->username = 'test';
		$user->email = 'test@test.com';
		$user->password = 'testesttest';
		$user->save();
		$user->setRoles(['neon-administrator']);
		$this->user = $user;
	}

	public function _after(AcceptanceTester $I)
	{
		$this->user->delete();
	}

	protected function _login($I)
	{
		$I->seeInCurrentUrl('/login');
		$I->fillField('#login-username', 'test@test.com');
		$I->fillField('#login-password', 'testesttest');
		$I->click('button[type="submit"]');
	}

	public function formsTest(AcceptanceTester $I)
	{
		$I->amOnPage('/daedalus/index/index');
		$this->_login($I);
		$I->wait(1);
		$I->waitForElement('[data-cmd="create_table"]');
		$I->click('[data-cmd="create_table"]');
		$I->waitForText('Untitled Content - Form');

		$formId = 'form';

		$fields = [
			'text' => [
				'name' => 'text',
				'label' => 'Text field',
				'hint' => 'Text field guide text test',
				'placeholder' => 'Text field placeholder text test',
				'required' => true,
				'value' => 'some test text random123',
				'see' => 'some test text random123'
			],
			'textarea' => [
				'name' => 'textarea',
				'label' => 'Textarea field',
				'hint' => 'Textarea field guide text test',
				'placeholder' => 'Textarea field placeholder text test',
				'required' => true,
				'value' => "Some textarea content bla bla bla",
				'see' => 'Some textarea content bla bla bla'
			],
			'image' => [
				'name' => 'image',
				'label' => 'Image field',
				'hint' => 'Upload an image',
			],
			'file' => [
				'name' => 'file',
				'label' => 'File upload',
				'hint' => 'Upload an image',
			],
			'checkbox' => [
				'name' => 'checkbox',
				'label' => 'Checkbox',
				'hint' => 'check me',
				'value' => 1,
				'see' => 'True'
			],
			'select' => [
				'name' => 'select',
				'label' => 'Select',
				'hint' => 'select an option',
				'value' => 'Key 2',
//				'see' => 'Option 2'
			],
			'radio' => [
				'name' => 'radio',
				'label' => 'Radio',
				'hint' => 'select an option',
				'items' => [],
				'value' => 'Key 3',
				'see' => 'Option 3'
			],
			'wysiwyg' => [
				'name' => 'wysiwyg',
				'label' => 'wysiwyg',
			],
		];

		foreach ($fields as $key => $field) {
			// Add test field "text"
			$I->dragAndDrop('[data-component="' . 'neon-core-form-fields-' . $key . '"]', '.formBuilder_fields');
			$I->waitForElement('#data-name');
			//$I->fillField('#data-name', $field['name']);
			if (isset($field['label']))
				$I->fillField('[name="label"] input', $field['label']);
			if (isset($field['hint']))
				$I->fillField('[name="hint"] textarea', $field['hint']);
			if (isset($field['placeholder']))
				$I->fillField('[name="placeholder"] input', $field['placeholder']);
			if (isset($field['required']))
				$I->click('[name="required"] .neonSwitch');
			if (isset($field['items'])) {
				$I->click('[data-test-items-add]');
				$I->click('[data-test-items-add]');
				$I->click('[data-test-items-add]');
			}
		}

		$I->click('Save & Exit');
		$I->waitForJS("return $.active == 0;", 15);

// ------------------------------------------------------------------------------------------------------
// Adding a new row
// ------------------------------------------------------------------------------------------------------
		$I->click('Add New');
		$I->waitForElement('#form-text');

		$testTestField = $fields['text'];
		$I->see($testTestField['label']);
		$I->see($testTestField['hint']);

		foreach ($fields as $key => $field) {
			if (isset($field['value'])) {
				switch ($key) {
					case 'checkbox':
						$I->checkOption("#{$formId}-{$field['name']}");
						break;
						// Todo make select box work
					case 'select':
						break;
//						$I->fillField("#{$formId}-{$field['name']}", $field['see']);
//						$I->pressKey("#{$formId}-{$field['name']}",WebDriverKeys::ENTER);
//						break;
					case 'radio':
						$I->selectOption("[name=\"{$formId}[{$field['name']}]\"]", $field['value']);
						break;
					default:
						$I->fillField("#{$formId}-{$field['name']}", $field['value']);
				}
			}
		}

		$I->click('#form [type=submit]');
// ------------------------------------------------------------------------------------------------------
// Testing the grid display
// ------------------------------------------------------------------------------------------------------
		foreach ($fields as $key => $field) {
			if (isset($field['value'])) {
				if ($key === 'checkbox' || $key === 'radio' || $key === 'select')
					continue;
				$I->waitForText($field['value'], 30);
			}
		}

		foreach ($fields as $key => $field) {
			if (isset($field['value'])) {
				if ($key === 'checkbox' || $key === 'radio' || $key === 'select')
					continue;
				$I->waitForText($field['value'], 30);
			}
		}

		$I->seeInCurrentUrl('daedalus/index/list?type=' . $formId);
		foreach ($fields as $key => $field) {
			if (isset($field['see'])) {
				$I->see($fields[$key]['see']);
			}
		}
	}

}





