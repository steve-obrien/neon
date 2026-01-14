<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 18/01/2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\tests\unit\core;

use neon\core\form\exceptions\InvalidNameFormat;
use neon\core\form\fields\DateRange;
use neon\core\form\fields\DateTime;
use neon\core\test\Unit;
use neon\user\models\User;
use yii\base\BaseObject;
use yii\db\Expression;
use \yii\helpers\FileHelper;
use neon\core\form\Form;
use neon\core\form\FormRepeater;

/**
 * Class AppLoadingTest
 *
 * Test the Neon applications ability to load apps - and resolve app dependencies
 *
 * @author Steve O'Brien - Newicon Ltd
 * @package neon\tests\unit\core
 */
class FormTest extends Unit
{
	public function setUp(): void
	{
		$webRoot = '@root/var/runtime/FormTest';
		FileHelper::createDirectory(neon()->getAlias("$webRoot/assets"), 0775, true);
		// forms use asset bundles and these break in the context of a console application
		neon()->setAliases(['@web' => '@runtime/web', '@webroot' => $webRoot]);
		neon()->urlManager->baseUrl = 'neontest.dev';
		User::deleteAll();
	}
	public function tearDown(): void
	{
		User::deleteAll();
	}

	/**
	 * Test the valid ways of adding a text field
	 */
	public function test_form_field_array_access()
	{
		$f = new \neon\core\form\Form('my_form');
		$f->addFieldText('name_one')->setLabel('Name one')->setValue('one');
		$this->assertInstanceOf('\neon\core\form\interfaces\IField', $f->getField('name_one')->setLabel('Name One'));
		$this->assertTrue($f->getField('name_one')->getLabel() == 'Name One');
		$this->assertEquals($f['name_one'], $f->getField('name_one'));
	}

	public function test_single_field_instantiation()
	{
		$f = new \neon\core\form\fields\Date(['name'=>'test_date']);
		//$this->markTestIncomplete('This test has not been implemented yet.');
		// I'd like to decouple the need for a parent form when rendering a single form field
		// $f->run();
	}

	/**
	 * Test to check a bug regression - where data containing the form name and the field name e.g. ['person']['person'] = '123'
	 * and a form name of person and a field name of person the form incorrectly passes the entire data to the child field
	 * so the child 'person' field gets an array instead of the value of 123.
	 */
	public function testSameFieldAndFormName()
	{
		$f = new \neon\core\form\Form('person');
		$f->setRequest(new MockRequest_for_sameFieldAndFormNameTest());
		$f->addFieldText('person')->addValidator('string');
		$f->load();
		$this->assertEquals('a person name', $f['person']->getValue());

		$f->load(['person' => 'Frank']);
		$this->assertEquals('Frank', $f['person']->getValue());

		// this would attempt to set the text field to an array
		$f->load(['person' => ['person' => 'a person name']]);
		$this->assertEquals(['person' => 'a person name'], $f['person']->getValue());

		$this->assertEquals(false, $f->getField('person')->validate(), 'The validation should fail as the text box has been set to an array value');
	}

	public function testChoiceField()
	{
		$f = new \neon\core\form\Form('chooser_form');
		$f->addFieldSelect('fav_colour')
			->setItems([
				'b' => 'blue',
				'r' => 'red',
				'g' => 'green',
				'y' => 'yellow'
			])
			->setValue('g');
		$this->assertEquals('g', $f->getField('fav_colour')->getValue());
		$this->assertEquals('green', $f['fav_colour']->getValueDisplay());
		$f->addFieldSelect('fav_colour2')
			->setItems([
				['key' => 'b', 'value' => 'blue'],
				['key'=>'r', 'value' => 'red'],
				['key'=>'g', 'value' =>'green'],
				['key'=>'y', 'value'=> 'yellow']
			])
			->setValueFromDb(['key'=>'g']);
		$this->assertEquals('g', $f['fav_colour2']->getValue());
		$this->assertEquals('green', $f['fav_colour2']->getValueDisplay());
	}

	public function testRequiredFieldValidation()
	{
		$f = new Form('form');
		$f->addFieldText('name')->setRequired();
		$this->assertFalse($f->validate());
	}

	public function testThrowInvalidNameFormatExceptionWhenEmpty()
	{
		$this->expectException(InvalidNameFormat::class);
		$f = new Form('');
	}

	/**
	 * @return array
	 * where 1st item is form name and second is boolean representing whether it is a valid name of not
	 */
	public function nameProvider()
	{
		return [
			// invalid names
			['ewf.', false],
			['one.two', false],
			['$sefw£', false],
			['werfv=w3£', false],
			// valid names
			['one-two', true],
		];
	}

	/**
	 * @dataProvider nameProvider
	 */
	public function testThrowInvalidNameFormatExceptionWhenInvalidCharacters($name, $isValid)
	{
		$exceptionThrown = false;
		try {
			$f = new Form($name);
		} catch(InvalidNameFormat $e) {
			$exceptionThrown = true;
		}
		$this->assertEquals($isValid ? false : true, $exceptionThrown);
	}

	/**
	 * @dataProvider nameProvider
	 */
	public function testFieldThrowInvalidNameFormatExceptionWhenInvalidCharacters($name, $isValid)
	{
		$exceptionThrown = false;
		$f = new Form('form');
		try {
			$f->addFieldText($name);
		} catch(InvalidNameFormat $e) {
			$exceptionThrown = true;
		}
		$this->assertEquals($isValid ? false : true, $exceptionThrown);
	}

	public function testFieldNames()
	{
		neon()->set('request', new MockRequest_generic());
		$f = new Form('form');
		$f->addFieldText('wdsaf-wqef');
		$f->toJson();
	}

	/**
	 * Ensure link fields include selected items
	 */
	public function testLinkFieldsReturnSelectedItems()
	{
		$f = new \neon\core\form\Form('form');
		$f->addFieldLink('link', ['multiple' => true, 'dataMapProvider' => 'user', 'dataMapKey' => 'users']);
		$faker = \Faker\Factory::create();
		for ($i = 0; $i < 10; $i++) {
			$u = new \neon\user\models\User([
				'username' => $faker->userName, 'email' => $faker->email, 'password' => $faker->password
			]);
			$u->save();
		}
		$users = \neon\user\models\User::find()->orderBy(new \yii\db\Expression('rand()'))->limit(5)->all();
		$selected = [];
		foreach($users as $u)
			$selected[] = $u->uuid;
		$f->setValue(['link' => $selected]);

		foreach($users as $u)
			$this->assertArrayHasKey($u->uuid, $f->getField('link')->getItems());
	}

	public function testRepeatersGetFieldPaths()
	{
		$f = new Form('test');
		$repeater = new FormRepeater('people');
		$template = new Form('template');
		$template->addFieldText('name');
		$repeater->setTemplate($template);
		$f->add($repeater);

		$f->setValue([
			'people' => [
				'd97f10435efb10101f61094f8bfcbc939b5f' => [
					'name' => 'steve'
				],
				'uuid64inhereFW4wfWD2Jx' => [
					'name' => 'nicola'
				]
			]
		]);
		$test = $f->getFieldByPath('test.people.d97f10435efb10101f61094f8bfcbc939b5f.name');
		$this->assertEquals('name', $test->getName());

		$test = $f->getFieldByPath('test.people.uuid64inhereFW4wfWD2Jx.name');
		$this->assertEquals('name', $test->getName());

		$test = $f->getFieldByPath('test.people.doesNotYetExist.name');
		$this->assertEquals('name', $test->getName());

		// Lets express the form as a big old array - and double test everything works ok
		// - we will also introduce a field with the same name on the parent form as within the repeater
		// - and test we do not get confused.

		$f = new Form('test', [
			'fields' => [
				'people' => [
					'class' => 'repeater',
					'template' => [
						'fields' => [
							'name' => [
								'class' => 'text',
								'label' => 'The map field'
							]
						]
					]
				],
				'name' => [
					'class' => 'text',
					'label' => 'the test field with the same name'
				]
			]
		]);

		$f->setValue([
			'people' => [
				'd97f10435efb10101f61094f8bfcbc939b5f' => [
					'name' => 'steve'
				],
				'uuid64inhereFW4wfWD2Jx' => [
					'name' => 'nicola'
				]
			],
			'name' => 'Hello!'
		]);

		$test = $f->getFieldByPath('test.people.d97f10435efb10101f61094f8bfcbc939b5f.name');
		$this->assertEquals('name', $test->getName());

		$test = $f->getFieldByPath('test.people.uuid64inhereFW4wfWD2Jx.name');
		$this->assertEquals('name', $test->getName());

		$test = $f->getFieldByPath('test.people.doesNotYetExist.name');
		$this->assertEquals('name', $test->getName());

		// Lets express a complex form with double repeaters

		$f = new Form('test', [
			'fields' => [
				// we can have many people
				// with name and a repeater within each person of email
				'people' => [
					'class' => 'repeater',
					'count' => 0,
					'template' => [
						'fields' => [
							'name' => [
								'class' => 'text',
								'label' => 'The map field'
							],
							'emails' => [
								'class' => 'repeater',
								'count' => 0,
								'template' => [
									'fields' => [
										'forWork' => [
											'class' => 'switch',
										],
										'email' => [
											'class ' => 'email',
										]
									]
								]
							]
						]
					]
				],
			]
		]);

		// lets try with no data
		$test = $f->getFieldByPath('test.people.x.name');
		$this->assertEquals('The map field', $test->getLabel());

		$test = $f->getFieldByPath('test.people.[].emails.[].email'); // it might look better to do this
		$this->assertEquals('email', $test->getName());

		$test = $f->getFieldByPath('test.people.xxxx.emails.xxxx.email'); // it might look better to do this
		$this->assertEquals('email', $test->getName());

		$this->assertEquals(['people' => []], $f->getData());

		$f->setValue([
			'people' => [
				'uuid0' => [
					'name' => 'nicola',
					'emails' => [
						'uuid1' => [
							'forWork' => true,
							'email' => 'ms.important@work.com'
						],
						'uuid2' => [
							'forWork' => false,
							'email' => 'address@home.com'
						]
					]
				]
			]
		]);
		$this->assertEquals([
			'people' => [
				'uuid0' => [
					'name' => 'nicola',
                    'emails' => [
						'uuid1' => [
							'forWork' => 1,
                            'email' => 'ms.important@work.com'
						],
                        'uuid2' => [
							'forWork' => 0,
                            'email' => 'address@home.com'
                        ]
                    ]
                ]
			]
		], $f->getdata());

		$test = $f->getFieldByPath('test.people.uuid0.emails.uuid2.email');
		$this->assertEquals('address@home.com', $test->getValue());
		$test = $f->getFieldByPath('test.people.uuid0.emails.uuid1.email');
		$this->assertEquals('ms.important@work.com', $test->getValue());
		$test = $f->getFieldByPath('test.people.uuid0.emails.[].email');
		$this->assertEquals(null, $test->getValue());
		$this->assertEquals('email', $test->getName());
	}

}

/**
 * Class MockRequest
 * @package neon\tests\unit\core
 */
class MockRequest_generic extends BaseObject
{
	public function getMethod()
	{
		return 'GET';
	}
	public function getCsrfToken()
	{
		return neon()->security->generateRandomString();
	}
	public function getCsrfParam()
	{
		return 'neon_csrf';
	}
}
/**
 * Class MockRequest
 * @package neon\tests\unit\core
 */
class MockRequest_for_sameFieldAndFormNameTest {
	public function getMethod(){
		return 'POST';
	}
	public function getBodyParams() {
		return ['person' => ['person' => 'a person name']];
	}
}