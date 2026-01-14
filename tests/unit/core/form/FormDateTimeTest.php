<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 18/01/2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\tests\unit\core;

use neon\core\form\exceptions\InvalidNameFormat;
use neon\core\form\fields\Date;
use neon\core\form\fields\DateRange;
use neon\core\form\fields\DateTime;
use neon\core\test\Unit;
use yii\base\BaseObject;
use \yii\helpers\FileHelper;
use neon\core\form\Form;

/**
 * Class AppLoadingTest
 *
 * Test the Neon applications ability to load apps - and resolve app dependencies
 *
 * @author Steve O'Brien - Newicon Ltd
 * @package neon\tests\unit\core
 */
class FormDateTimeTest extends Unit
{
	public function setUp(): void
	{
		$webRoot = '@root/var/runtime/FormTest';
		FileHelper::createDirectory(neon()->getAlias("$webRoot/assets"), 0775, true);
		// forms use asset bundles and these break in the context of a console application
		neon()->setAliases(['@web' => '@runtime/web', '@webroot' => $webRoot]);
		neon()->urlManager->baseUrl = 'neontest.dev';
	}

	/**
	 * @dataProvider dateFieldProvider
	 * @param $userInput
	 * @param $getValue
	 * @param $getData
	 * @param $valueDisplay
	 * @param $format
	 * @throws \Exception - if neon request object within the form is configured incorrectly
	 */
	public function testDateField($userInput, $getValue, $getData, $valueDisplay, $format)
	{
		$f = new Form();
		$f->add(new Date('date'));
		$f->load(['date' => $userInput]);

		neon()->formatter->dateFormat = $format;
		neon()->formatter->nullDisplay = '-';

		$this->assertEquals($getValue, $f->getValue()['date'], 'getValue match'); // test form data
		$this->assertEquals($getData, $f->getData()['date'], 'getData match'); // test form data
		$this->assertEquals($valueDisplay, $f['date']->getValueDisplay(), 'getValueDisplay');

	}

	/**
	 * @see testDateField
	 * @return array
	 */
	public function dateFieldProvider()
	{
		return [
			// userInput   ,  getValue   , getData     , getValueDisplay,  Format
			[  '2018-12-25', '2018-12-25', '2018-12-25', '25/12/2018'   ,  'dd/MM/yyyy'  ], #0
			[  '2018-12-25', '2018-12-25', '2018-12-25', '25/12/2018'   ,  'php:d/m/Y'   ], #1
			[  '2018-12-25', '2018-12-25', '2018-12-25', '12/25/2018'   ,  'MM/dd/yyyy'  ], #2
			[  '2018-12-25', '2018-12-25', '2018-12-25', '12/25/2018'   ,  'php:m/d/Y'   ], #3
			[  '2018-12-25', '2018-12-25', '2018-12-25', '25 12 2018'   ,  'dd MM yyyy'  ], #4
			[  ''          , ''          , ''          , '-'            ,  'dd MM yyyy'  ], #5
			[  null        , null        , null        , '-'            ,  'dd MM yyyy'  ], #6
			[  'Eèvav'     , 'Eèvav'     , null        , '-'            ,  'dd MM yyyy'  ], #7
			[  'E<script>v', 'Ev'        , null        , '-'            ,  'dd MM yyyy'  ], #8
			[  '2018-12-35', '2018-12-35', null        , '-'            ,  'dd MM yyyy'  ], #9
		];
	}

	/**
	 * @see testDateField
	 * @return array
	 */
//	public function dateTimeFieldProvider()
//	{
//		return [
//			// userInput   ,  getValue   , getData     , getValueDisplay,  Format
//			[  '2018-12-25', '2018-12-25', '2018-12-25', '25/12/2018'   ,  'dd/MM/yyyy'  ],
//			[  '2018-12-25', '2018-12-25', '2018-12-25', '25/12/2018'   ,  'php:d/m/Y'   ],
//			[  '2018-12-25', '2018-12-25', '2018-12-25', '12/25/2018'   ,  'MM/dd/yyyy'  ],
//			[  '2018-12-25', '2018-12-25', '2018-12-25', '12/25/2018'   ,  'php:m/d/Y'   ],
//			[  '2018-12-25', '2018-12-25', '2018-12-25', '25 12 2018'   ,  'dd MM yyyy'  ],
//			[  ''          , ''          , ''          , '-'            ,  'dd MM yyyy'  ],
//			[  null        , null        , null        , '-'            ,  'dd MM yyyy'  ],
//		];
//	}

	/**
	 * @dataProvider dateRangeProvider
	 *
	 * @param $fromUser - data as expected from request
	 * @param $outputData - data field should generate
	 * @throws \Exception - if neon request object within the form is configured incorrectly
	 */
	public function testDateRangeField($fromUser, $getValueFormat, $getDataFormat)
	{
		$f = new Form();
		$f->add(new DateRange('dr'));
		$f->load(['dr' => $fromUser]);

		$this->assertEquals($getValueFormat, $f->getValue()['dr'], 'getValue match'); // test form data
		$this->assertEquals($getValueFormat, $f->getField('dr')->getValue(), 'getValue match'); // test fields data
		$this->assertEquals($getValueFormat, $f['dr']->getValue(), 'getValue match'); // test array access data

		$this->assertEquals($getDataFormat, $f->getData()['dr'], 'getData match'); // test data format
		$this->assertEquals($getDataFormat, $f->getField('dr')->getData(), 'getData match'); // test fields data
		$this->assertEquals($getDataFormat, $f['dr']->getData(), 'getData match'); // test array access data
	}
	/**
	 * @see testDateRangeField
	 * @return array
	 */
	public function dateRangeProvider()
	{
		return [
			// User input                                      | getValue() format                               | getData() format
			[ ['from' => '1983-12-03', 'to' => '2018-12-03'],  [ 'from' => '1983-12-03', 'to' => '2018-12-03' ],  [ 'from' => '1983-12-03', 'to' => '2018-12-03'] ],
			[ ['from' => '1983-12-03', 'to' => ''],            [ 'from' => '1983-12-03', 'to' => '' ],            [ 'from' => '1983-12-03', 'to' => ''] ],
			[ ['from' => '', 'to' => '1983-12-03'],            [ 'from' => '', 'to' => '1983-12-03' ],            [ 'from' => '', 'to' => '1983-12-03'] ],
			[ ['from' => '', 'to' => ''],                      [ 'from' => '', 'to' => '' ],                      [ 'from' => '', 'to' => ''] ],
			[ [],                                              [],                                                [ 'from' => '', 'to' => '' ] ],
			[ '',                                              '',                                                [ 'from' => '', 'to' => '' ] ],
			[ ['from' => '1983-!1203', 'to' => ''],            [ 'from' => '1983-!1203', 'to' => '' ],            [ 'from' => '', 'to' => ''] ],
			[ ['from' => '1-<script>0', 'to' => '1983-12-03'], [ 'from' => '1-0', 'to' => '1983-12-03' ],         [ 'from' => '', 'to' => '1983-12-03'] ],
			[ ['from' => '2018-12-25', 'to' => '1-<script>0'], [ 'from' => '2018-12-25', 'to' => '1-0' ],         [ 'from' => '2018-12-25', 'to' => ''] ],
			[ ['from' => '2018-12-36', 'to' => ''],            [ 'from' => '2018-12-36', 'to' => '' ],            [ 'from' => '', 'to' => ''] ],
		];
	}

	/**
	 * @dataProvider dateTimeProvider
	 *
	 * @param string $fromUser
	 * @param string $value - the value given by fields getValue function
	 * @param null|string $data - the value given by fields getData function
	 * @param string $display - the value given by fields getValueDisplay function
	 * @param string $datetimeFormat - the current setting for datetime format
	 * @throws \Exception - if neon request object within the form is configured incorrectly
	 */
	public function testDateTimeField($fromUser, $value, $data, $display, $datetimeFormat)
	{
		$f = new Form();
		$f->add(new DateTime('dt'));
		$f->load(['dt' => $fromUser]);

		neon()->formatter->datetimeFormat = $datetimeFormat;
		neon()->formatter->nullDisplay = '-';
		$this->assertEquals($value, $f->getValue()['dt'], 'getValue match'); // test form data
		$this->assertEquals($data, $f->getData()['dt'], 'getData match'); // test form data
		$this->assertEquals($display, $f['dt']->getValueDisplay(), 'getValueDisplay');
	}

	/**
	 * @see testDateTimeField
	 * @return array
	 */
	public function dateTimeProvider()
	{
		return [
			// user input                                                           // db format
			['2018-10-19 10:23:00',   '2018-10-19 10:23:00', '2018-10-19 10:23:00', '2018/10/19 10:23:00', 'yyyy/MM/dd HH:mm:ss'],  #1
			['2018-10-19 10:23:00',   '2018-10-19 10:23:00', '2018-10-19 10:23:00', '2018/10/19 10:23:00', 'php:Y/m/d H:i:s'],  #1
			['2018-10-19 10:23'   ,   '2018-10-19 10:23'   , null                 , '-'                  , 'php:Y/m/d H:i:s'],  #2
		];
	}

	/**
	 * @dataProvider timeProvider
	 *
	 * @param string|array $time - The time format to be passed to the time fields setValue function
	 * @param string $actualTime - The actual time in string format that the field should convert the $time to
	 * @param boolean $pass - Whether the values should pass the default validation rules
	 * @throws \Exception - if neon request object within the form is configured incorrectly
	 */
	public function testTimeField($time, $actualTime, $pass)
	{
		$f = new \neon\core\form\Form('my_form');
		$t = new \neon\core\form\fields\Time('time');
		$t->addValidator('date', [
			'type' => 'time',
			'format' => 'php:H:i:s',
			'timeZone' => neon()->getTimeZone()
		]);
		$f->add($t);
		$t->setValue($time);
		$this->assertEquals($actualTime, $t->getData());
		$this->assertEquals($pass, $t->validate());
	}
	/**
	 * @see testTimeField
	 * @return array
	 */
	public function timeProvider()
	{
		return [
			['00:00:00',                                 '00:00:00',  true],
			['12:00:00',                                 '12:00:00',  true],
			['12:10:15',                                 '12:10:15',  true],
			['12:00',                                    '12:00:00',  true],
			['16:40',                                    '16:40:00',  true],
			[['hh' => '12', 'mm' => 20],                 '12:20:00',  true],
			[['hh' => '12'],                             '12:00:00',  true],
			[['hh' => '18', 'mm' => '30', 'ss' => '30'], '18:30:30',  true],
			[['hh' => '16', 'mm' => 40],                 '16:40:00',  true],
			['00:00:80',                                 '00:00:80',  false],
			['25:00:00',                                 '25:00:00',  false],
			[['hh' => '12', 'mm' => 61],                 '12:61:00',  false],
			[['hh' => '35', 'mm' => '30', 'ss' => '30'], '35:30:30',  false],
			[['hh' => '-1', 'mm' => -1],                 '-1:-1:00',  false],
		];
	}
}