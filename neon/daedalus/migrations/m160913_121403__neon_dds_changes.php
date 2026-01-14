<?php

use yii\db\Migration;

class m160913_121403__neon_dds_changes extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
		// add a series of default types to the datatypes
		$sql =<<<EOQ
INSERT INTO `dds_data_type` (`data_type_ref`, `label`, `description`, `definition`, `storage_ref`, `deleted`)
VALUES
('binary','Normal Binary','For normal binaries such as images up to 16MB in size','','binary',0),
('binarylong','Very Large Binary','For storing binary data such as documents up to 4GB in size','','binarylong',0),
('binaryshort','Small Binary','For small binaries up to 16KB in size (e.g thumbnails)','','binaryshort',0),
('boolean','Boolean','For true | false value','{"size":1}','integertiny',0),
('date','Date','For storing a date (no time associated) from the years 1000 to 9999','','date',0),
('datetime','Date and Time','For storing date and time from the years 1000 to 9999','','datetime',0),
('document','Document','For storing documents up to 4GB in size','','binarylong',0),
('float','Float','For floating numbers (decimal with limited but large precision) up to about 1 x 10^±38','','float',0),
('floatdouble','Double Float','A double precision float up to 1 x 10^±308','','floatdouble',0),
('html','HTML','For HTML up to about ten thousand characters','{"size":10000}','text',0),
('image','Image File','For storing images up to 16MB in size','','binary',0),
('integer','Integer','For integers up to ±2,147,483,648','','integer',0),
('integerlong','Large Integer','For integers up to 9 quintillion (9 with 18 zeros after it).','','integerlong',0),
('integershort','Short Integer','For integers up to ±8192','{"max":8192}','integershort',0),
('integertiny','Tiny Integer','For numbers up to ±128','{"max":128}','integertiny',0),
('json','JSON','For JSON up to about ten thousand characters','{"size":10000}','text',0),
('mime','MIME Type','For storing mime types','{"size":256}','text',0),
('text','Text','For text up to about twenty thousand characters (about two to four thousand average English words)','{"size":20000}','text',0),
('textlong','Long Text','For text up to about five million characters (about half to one million average English words).','{"size":5000000}','textlong',0),
('textshort','Short Text','For text up to 150 characters (about 15 to 30 words)','{"size":150}','textshort',0),
('url','URL','URL up to 150 characters','{"size":150}','textshort',0),
('uuid','UUID','For Universally Unique Identifiers','{"size":36}','char',0),
('uuid64','UUID64','For Universally Unique Identifiers in base 64','{"size":22}','char',0);

EOQ;
		$connection = \Yii::$app->getDb();
		$command = $connection->createCommand($sql);
		$command->execute();

    }

    public function safeDown()
    {
		// delete the additional rows in the datatypes
		$sql =<<<EOQ
DELETE FROM `dds_data_type` WHERE `data_type_ref` IN
('binary', 'binarylong', 'binaryshort', 'date', 'datetime', 'float', 'floatdouble',
'integer', 'integerlong', 'integershort', 'integertiny', 'text', 'textlong', 'textshort',
'boolean', 'url', 'html', 'json', 'uuid', 'mime', 'document', 'image');
EOQ;
		$connection = \Yii::$app->getDb();
		$command = $connection->createCommand($sql);
		$command->execute();

    }
}
