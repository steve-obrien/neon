<?php

namespace neon\user\interfaces;

/**
 * Interface iUser
 * Defines the interface for IUser which is available through Neon::$app->user
 * Here the user is the logged in user
 */
interface iUser {

	/**
	 * Get the uuid of the user
	 * @return string
	 */
	public function getUuid();

	/**
	 * Get the name of the user
	 * @return string
	 */
	public function getName();

	/**
	 * Whether this is a super user
	 * super users are granted access by default
	 * @return boolean
	 */
	public function isSuper();

	/**
	 * Get the email address
	 * @return string
	 */
	public function getEmail();

	/**
	 * Get the user username
	 * @return string
	 */
	public function getUsername();

	/**
	 * Get the hash of the
	 * @return mixed
	 */
	public function getPasswordHash();

	/**
	 * Get a url for the user image
	 * @param integer $size - the size of the image
	 * @return string
	 */
	public function getImageUrl($size = 80);

	/**
	 * Get the roles the user has
	 * @return array  an array of role names associated with this user
	 *   For e.g. `['neon-administrator', 'editor']`
	 */
	public function getRoles();

	/**
	 * Check if the user has a particular role
	 * @param string $role  the role you want to test for
	 * @return boolean  whether or not the user has the role
	 */
	public function hasRole($role);

	/**
	 * Check if the user has one of a set of roles
	 * @param array $roles  the set of roles to test for as [n]=>role
	 * @return boolean  whether or not the user has the role
	 */
	public function hasRoles(array $roles);

	/**
	 * Get the user's home url either finding the first one available or
	 * the one for the specified role
	 * @param string $role
	 * @return null | string  the url if found
	 */
	public function getHomeUrl($role=null);
}