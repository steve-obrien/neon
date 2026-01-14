<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2017 Newicon Ltd
 * @license http://www.newicon.net/neon/license/
 */

namespace neon\daedalus\interfaces;

/**
 * Application database structure needs to be stored in migrations for posting to
 * staging and live sites. Daedalus takes care of this internally through the use
 * of this class.
 *
 * If the migration file hasn't been opened then the migrations won't be saved. This
 * means it is safe to add migration methods to code that may or may not produce
 * a migration depending on context. E.g phoebe will open a migration file to save
 * daedalus structural changes whereas if those are run within a neon migration
 * without opening a file they won't be saved externally and so correctly won't
 * duplicate the migrations.
 *
 * This is currently (Nov 2017) intended for Daedalus use only. It may prove useful
 * for other classes but this is currently untested. Before using it for other
 * areas, ensure that this is updated appropriately.
 */
interface IDdsAppMigrator
{
	/**
	 * Open up a migration file into which migrations can be stored
	 *
	 * @param string $label  a simple label for the migration file. For example
	 *   the name of the class that the migrations are being performed on.
	 * @return string  the path and name of the migration file
	 * @throws \RuntimeException  if there is already a migration file open
	 */
	public function openMigrationFile($label);

	/**
	 * Store an up and down migration. Attempts to store a migration without
	 * having already opened a migration are ignored (this is intentional).
	 * Migrations are not written to file until the migration is closed
	 *
	 * @param string|array $upMigration  an (or array of) up migrations
	 * @param string|array $downMigration  an (or array of) corresponding down migrations
	 * @return string   an identifier for a migration
	 */
	public function storeMigration($upMigration, $downMigration);

	/**
	 * Remove a migration from the stored migrations e.g. if something failed
	 *
	 * @param string $migrationId  the migration id from storeMigration
	 */
	public function removeMigration($migrationId);

	/**
	 * Close the currently open migration file. If no migrations were stored no file is created
	 *
	 * @param boolean $delete  if set to true, then the migration is deleted
	 *   regardless of whether or not any migrations were stored
	 */
	public function closeMigrationFile($delete=false);

}