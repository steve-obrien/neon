<?php

namespace neon\core\db;

class SchemaBuilder
{
	use \yii\db\SchemaBuilderTrait;
	
	public $db;
	
	public function __construct($db)
	{
		$this->db = $db;
	}
	
	protected function getDb()
	{
		return $this->db;
	}
}