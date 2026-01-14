<?php
/**
 * @link http://www.newicon.net/neon
 * @copyright Copyright (c) 2015 Newicon Ltd
 * @license http://www.newicon.net/neon/license
 */

namespace neon\core;

/**
 * The interface for each Neon App.
 */
interface AppInterface
{
	/**
	 * Install function to install the necessary database models
	 */
	public function install();

	/**
	 * Uninstall function to clean up this app when uninstalled
	 */
	public function uninstall();

	/**
	 * This function is useful for adding specific module components and configuration.
	 * The module is first initialized then the configure is called. Not all apps may be initialised so this
	 * function is an inward looking one that is essentially similar to loading a config file for the class.
	 * It is safe to access the parent Neon::$app.
	 * This is often used to remove config setting from the main @neon/core/config/ areas to make configuration
	 * more typical to the App managing it and in particular using Yii's application components (dependency injection)
	 * Example:
	 * ```
	 *	 $this->set('menuManager'), [
	 *		 'class' => '\neon\admin\components\MenuManager'
	 *	 ]);
	 * ```
	 * sets the variable menuManager on the app
	 */
	public function configure();

	/**
	 * The setup function is called after all apps have been initialized
	 * and fully configured therefore it is safe to try to access them.
	 */
	public function setup();

	/**
	 * Gets the alias name used to reference this app
	 * e.g. @neon/user would refer to the user module
	 */
	public function getClassAlias();

	/**
	 * Get a human readable name for this app
	 * @return string
	 */
	public function getName();

	/**
	 * Get the menu and sub menu items (if applicable) for this app
	 * The menu may get overriden aat runtime via setMenu option
	 * @return array|null - null or false if no menu should be created
	 * for e.g.:
	 * ```php
	 * [
	 *     'label' => $this->getName(),
	 *     'order' => 1010,
	 *     'url' => ['/cms/index/index'],
	 *     'visible => neon()->user->is('neon-administrator')
 	 * ];
	 * ```
	 */
	public function getMenu();

	/**
	 * Set the menu options
	 * @param array $menu
	 * @return void
	 */
	public function setMenu($menu);

	/**
	 * Get the menu array configuration
	 * ```php
	 * [
	 *     'label' => $this->getName(),
	 *     'order' => 1010,
	 *     'url' => ['/cms/index/index'],
	 *     'visible => neon()->user->is('neon-administrator')
	 * ];
	 * ```
	 * @return array
	 */
	public function getDefaultMenu();

	/**
	 * Get settings confirguration that this module would like
	 * format should be compatible with form $fields property
	 * @return array
	 * e.g.:
	 * ```php
	 * [
	 *	'fromEmailAddress' => [
	 *		 'dataType' => 'string',
	 *		 'label' => 'From Email Address',
	 *		 'helpBlock' => 'Set the from email address for emails sent from the system'
	 *	 ],
	 * ]
	 * ```
	 */
	public function getSettings();

}