<?php

namespace neon\tests\unit\core\helpers;

use \neon\core\helpers\Arr;

class ArrTest extends \Codeception\Test\Unit
{
	/**
	 * Pluck an array of values from an array.
	 * Inspired by Laravels Pluck method
	 *
	 * example useage:
	 *
	 * ```php
	 * $array = [
	 *     ['user' => ['id' => 1, 'name' => 'Steve']],
	 *     ['user' => ['id' => 2, 'name' => 'Bob']]
	 * ]
	 *
	 * Arr::pluck($array, 'user.name')
	 *
	 * // Returns: ['Steve', 'Bob'];
	 * ```
	 *
	 * You may also specify how you wish the resulting list to be keyed:
	 *
	 * ```php
	 * $array = array_pluck($array, 'user.name', 'user.id');
	 *
	 * // Returns: [1 => 'Steve', 2 => 'Bob'];
	 * ```
	 */
	public function testPluck()
	{
		$array = [
			['user' => ['id' => 5, 'name' => 'Steve']],
			['user' => ['id' => 10, 'name' => 'Bob']]
		];
		$output = Arr::pluck($array, 'user.name');
		$this->assertTrue($output[0] == 'Steve');
		$this->assertTrue($output[1] == 'Bob');

		$output = Arr::pluck($array, 'user.name', 'user.id');
		$this->assertTrue($output[5] == 'Steve');
		$this->assertTrue($output[10] == 'Bob');
	}

	public function testDot()
	{
		$array = [
			'name' => 'steve',
			'sex' => 'oh yes please',
			'parents' => ['mother' => ['name' => 'Lyn'], 'father' => ['name' => 'colin']]
		];
		$result = Arr::dot($array);
		$shouldBe =  [
			"name" => "steve",
		    "sex" => "oh yes please",
		    "parents.mother.name" => "Lyn",
		    "parents.father.name" => "colin"
		];
		$this->assertEquals($shouldBe, $result);
		$result = Arr::dot($array, '--');
		$shouldBe =  [
			"--name" => "steve",
			"--sex" => "oh yes please",
			"--parents.mother.name" => "Lyn",
			"--parents.father.name" => "colin"
		];
		$this->assertEquals($shouldBe, $result);
	}

	public function testExcept()
	{
		$array = [
			'name' => 'steve',
			'sex' => 'oh yes please',
			'parents' => ['mother' => ['name' => 'Lyn'], 'father' => ['name' => 'colin']]
		];
		$result = Arr::except($array, ['sex', 'parents']);
		$shouldBe = ['name' => 'steve'];
		$this->assertEquals($shouldBe, $result);

		$result = Arr::except($array, 'parents.father');
		$shouldBe = ['name' => 'steve', 'sex' => 'oh yes please', 'parents' => ['mother' => ['name' => 'Lyn']]];
		$this->assertEquals($shouldBe, $result);
	}

	public function testFirst()
	{
		$array = [50, 100, 200, 300];
		$result = Arr::first($array, function($value, $key) {
			return $value > 150;
		});
		$this->assertEquals(200, $result);

		// should return default value (result of callback) if empty array and no search callback specified
		$result = Arr::first([], null, function() { return 'OI DEFAULT!'; });
		$this->assertEquals('OI DEFAULT!', $result);

		// should return first item in an array if no search callback specified
		$result = Arr::first(['thing', 'different thing'], null);
		$this->assertEquals('thing', $result);

		$array = [50, 100, 200, 300];
		$result = Arr::first($array, function($value, $key) {
			return $value > 400;
		}, 'not found');
		$this->assertEquals('not found', $result);
	}

	public function testWhere()
	{
		$array = [100, '200', 300, '400', 500];
		$result = Arr::where($array, function ($value, $key) {
			return is_string($value);
		});
		$shouldBe = [1 => 200, 3 => 400];
		$this->assertEquals($shouldBe, $result);
	}

	public function testFlatten()
	{
		$array = [
			'name' => 'steve',
			'sex' => 'oh yes please',
			'parents' => ['mother' => ['name' => 'Lyn'], 'father' => ['name' => 'colin']]
		];
		$shouldBe = ["steve", "oh yes please", "Lyn", "colin"];
		$result = Arr::flatten($array);
		$this->assertEquals($shouldBe, $result);
	}

	public function testPull()
	{
		$array = ['name' => 'steve', 'sex' => 'oh yes please', 'parents' => ['mother' => ['name' => 'Lyn'], 'father' => ['name' => 'colin']]];
		$result = Arr::pull($array, 'parents.father.name');
		$this->assertEquals('colin', $result);
		$this->assertEquals(['name' => 'steve', 'sex' => 'oh yes please', 'parents' => ['mother' => ['name' => 'Lyn'], 'father' => []]], $array);
		$result = Arr::pull($array, 'parents');
		$this->assertEquals(['mother' => ['name' => 'Lyn'], 'father' => []], $result);
		$this->assertEquals(['name' => 'steve', 'sex' => 'oh yes please'], $array);
	}

	public function testPrepend()
	{
		$array = ['two', 'three', 'four'];
		$array = Arr::prepend($array, 'one');
		$this->assertEquals(['one', 'two', 'three', 'four'], $array);
	}

	public function testLast()
	{
		$array = [50, 100, 200, 300];
		$result = Arr::last($array, function($value, $key) {
			return $value > 150;
		});
		$this->assertEquals(300, $result);
	}

	public function testSet()
	{
		$array = ['name' => 'steve', 'sex' => 'oh yes please', 'parents' => ['mother' => ['name' => 'Lyn'], 'father' => ['name' => 'colin']]];
		Arr::set($array, 'sex', 'not today');
		Arr::set($array, 'siblings.brother.youngest', 'Rich');
		$expected = ["name" => "steve", "sex" => "not today", "parents" => ["mother" => ["name" => "Lyn"], "father" => ["name" => "colin"]], "siblings" => ["brother" => ["youngest" => "Rich"]]];
		$this->assertEquals($expected, $array);
	}

	public function testCollapse()
	{
		$array = [[1, 2, 3], [4, 5, 6], [7, 8, 9]];
		$result = Arr::collapse($array);
		$this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8, 9], $result);
	}
}