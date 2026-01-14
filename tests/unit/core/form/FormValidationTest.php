<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 * @author Steve O'Brien <steve.obrien@newicon.net> 24/02/2018
 * @package neon
 */

namespace neon\tests\unit\core;

use neon\core\test\Unit;
use yii\helpers\FileHelper;
use neon\core\form\Form;
use neon\core\form\fields;

class FormValidationTest extends Unit
{
	public function setUp(): void
	{
		// TODO: make fake webroot
		$webRoot = '@root/var/runtime/FormValidationTest';
		FileHelper::createDirectory(neon()->getAlias("$webRoot/assets"), 0775, true);
		// forms use asset bundles and these break in the context of a console application
		neon()->setAliases(['@web' => '@runtime/web', '@webroot' => $webRoot]);
		neon()->urlManager->baseUrl = 'neontest.dev';

	}

	public function testRequiredFieldValidation()
	{
		$form = new Form('form');
		$field = $form->addFieldText('name')->setRequired();
		$this->assertFalse($form->validate());
		$this->assertEquals(true, $field->hasValidator('required'));
		$this->assertEquals(true, $field->hasValidator(['class'=>'required']));
		$required = current($field->getValidators());
		$this->assertEquals(true, $field->hasValidator($required));
		$validators = $field->getValidators();
		$key = array_keys($validators);
		$val = array_values($validators);
		$this->assertThat($key[0], $this->isType('string'));
		$this->assertThat($val[0], $this->isInstanceOf('\neon\core\validators\RequiredValidator'));
	}

	public function testRequiredGotchas()
	{
		$form = new Form('form');
		$form->setRequest(new MockRequest());

		$field = $form->addFieldText('name');

		// add required
		$field->setRequired(true);
		$this->assertTrue($field->hasValidator('required'));
		$this->assertTrue($field->getRequired());

		// remove required
		$field->setRequired(false);
		$this->assertFalse($field->hasValidator('required'));
		$this->assertFalse($field->getRequired());

		// add required
		$field->addValidator('required');
		$this->assertTrue($field->isRequired());
		$this->assertTrue($field->hasValidator('required'));

		// remove required
		$field->removeValidator('required');
		$this->assertFalse($field->hasValidator('required'));
		$this->assertFalse($field->getRequired());
	}

	public function testSettingValidatorsByDifferentFormats()
	{
		$form = new Form('form');
		$form->setRequest(new MockRequest());
		$field = $form->addFieldText('name')->setRequired();
		$field->addValidator('required');
		$field->addValidator('required');
		// there should only be one required validator
		$this->assertCount(1, $field->getValidators());
		$field->removeValidator('required');
		$this->assertCount(0, $field->getValidators());
		$field->setValidators([
			'string' => ['min' => 10, 'tooShort'=> 'Too short'],
			'required',
			['string', ['max' => 50, 'tooLong' => 'Too big']],
			// this is the only format we really want to support long term
			['class' => \neon\core\validators\DateValidator::class, 'timeZone' => 'UTC', 'message'=>'Not valid date and time'],
		]);
		$this->assertCount(4, $field->getValidators(), 'Four validators have been added');
		$this->assertTrue($field->hasValidator(\neon\core\validators\DateValidator::class));
		$this->assertTrue($field->hasValidator(\neon\core\validators\StringValidator::class));
		$stringValidators = array_values($field->getValidator(\neon\core\validators\StringValidator::class));
		$this->assertCount(2, $stringValidators);
		$this->assertSame(10, $stringValidators[0]->min);
		$this->assertSame(50, $stringValidators[1]->max);

		$field->setValue('123');
		$field->validate();
		$this->assertEquals(['Too short', 'Not valid date and time'], $field->getErrors());
		$field->setValue('12345678910 11 bigger ');
		$field->setErrors([]);
		$field->validate();
		$this->assertEquals(['Not valid date and time'], $field->getErrors());
		$field->setValue('More than 50 012345678901234567890123456789012345678901234567890123456789');
		$field->setErrors([]);
		$field->validate();
		$this->assertEquals(['Too big', 'Not valid date and time'], $field->getErrors());

		$required = $field->createValidator('required', ['message' => 'REQUIRED']);
		$string = $field->createValidator('string', ['max' => 10, 'tooLong' => 'Too long']);

		$field->setValidators([
			$required, $string
		]);
		$this->assertCount(2, $field->getValidators());
		$this->assertTrue($field->hasValidator('required'));
		$this->assertTrue($field->hasValidator('string'));
		$field->setErrors([]);
		$field->validate();
		$this->assertEquals(['Too long'], $field->getErrors());
		$field->setValue('');
		$field->setErrors([]);
		$field->validate();
		$this->assertEquals(['REQUIRED'], $field->getErrors());

		// empty the validators
		$field->setValidators([]);
		$this->assertCount(0, $field->getValidators());
		$field->setValidators([
			'a8fbffbd40ca4286de910e27f171da54' => $required,
			'd8b6a1a2b9a9280cd9da74b53c926e0b' => $string
		]);
		$this->assertCount(2, $field->getValidators());
		$this->assertTrue($field->hasValidator('required'));
		$this->assertTrue($field->hasValidator('string'));
		$field->setValidators([]);
		$this->assertCount(0, $field->getValidators());
		$field->setValidators([
			'f0f792b6cbb67fd8506cc93ce2e56339' => ['class' => 'string', 'max' => 10],
			'9e2286864114962d217c5230dd0474ca' => ['class' => 'required'],
		]);
		$this->assertCount(2, $field->getValidators());
		$this->assertTrue($field->hasValidator('required'));
		$this->assertTrue($field->hasValidator('string'));
	}
}

/**
 * Class MockRequest
 * @package neon\tests\unit\core
 */
class MockRequest {
	public function getBodyParams() {
		return [];
	}
	public $csrfParam = 'neon_csrf';
	public $csrfToken = 'welkjfhwergherwhgergerWERGUWERGHWERgwergwerg23452345';
}