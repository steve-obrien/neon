<?php

use neon\core\db\Migration;

class m20240506_140000_firefly_fulltext extends Migration
{
	public function safeUp()
	{
		// Add full-text indexes one by one
		$this->execute("ALTER TABLE firefly_file_manager ADD FULLTEXT idx_ft_name(name);");
		$this->execute("ALTER TABLE firefly_file_manager ADD FULLTEXT idx_ft_path(path);");
		$this->execute("ALTER TABLE firefly_file_manager ADD FULLTEXT idx_ft_mime_type(mime_type);");
		$this->execute("ALTER TABLE firefly_file_manager ADD FULLTEXT idx_ft_alt(alt);");
		$this->execute("ALTER TABLE firefly_file_manager ADD FULLTEXT idx_ft_caption(caption);");

		// Add combined full-text index
		$this->execute("ALTER TABLE firefly_file_manager ADD FULLTEXT idx_ft_combined(name, path, mime_type, alt, caption);");
	}

	public function safeDown()
	{
		// Remove full-text indexes one by one
		$this->execute("ALTER TABLE firefly_file_manager DROP INDEX idx_ft_name;");
		$this->execute("ALTER TABLE firefly_file_manager DROP INDEX idx_ft_path;");
		$this->execute("ALTER TABLE firefly_file_manager DROP INDEX idx_ft_mime_type;");
		$this->execute("ALTER TABLE firefly_file_manager DROP INDEX idx_ft_alt;");
		$this->execute("ALTER TABLE firefly_file_manager DROP INDEX idx_ft_caption;");

		// Remove combined full-text index
		$this->execute("ALTER TABLE firefly_file_manager DROP INDEX idx_ft_combined;");
	}
}
