<?php
namespace Oka\WSSEAuthenticationBundle\Model;

/**
 * 
 * Executes some manipulations on the WSSE users.
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
interface WSSEUserManipulatorInterface
{
	/**
	 * Creates a user and returns it.
	 *
	 * @param string $username
	 * @param string $password
	 * @param bool   $active
	 * @param array  $roles
	 *
	 * @return \Oka\WSSEAuthenticationBundle\Model\WSSEUserInterface
	 */
	public function create(string $username, string $password, bool $active, array $roles = []) :WSSEUserInterface;
	
	/**
	 * Activates the given user.
	 * 
	 * @param string $username
	 */
	public function activate(string $username);

	/**
	 * Deactivates the given user.
	 * 
	 * @param string $username
	 */
	public function deactivate(string $username);

	/**
	 * Changes the password for the given user.
	 * 
	 * @param string $username
	 * @param string $password
	 */
	public function changePassword(string $username, string $password);
	
	/**
	 * Adds role to the given user.
	 * 
	 * @param string $username
	 * @param string $role
	 * 
	 * @return bool true if role was added, false if user already had the role
	 */
	public function addRole(string $username, string $role) :bool;

	/**
	 * Removes role from the given user.
	 * 
	 * @param string $username
	 * @param string $role
	 * 
	 * @return bool true if role was removed, false if user didn't have the role
	 */
	public function removeRole(string $username, string $role) :bool;
	
	/**
	 * Adds $ip to the given user.
	 * 
	 * @param string $username
	 * @param string $ip
	 * 
	 * @return bool true if $ip was added, false if user already had the $ip
	 */
	public function addAllowedIp(string $username, string $ip) :bool;

	/**
	 * Removes $ip from the given user.
	 * 
	 * @param string $username
	 * @param string $ip
	 * 
	 * @return bool true if $ip was removed, false if user didn't have the $ip
	 */
	public function removeAllowedIp(string $username, string $ip) :bool;
	
	/**
	 * Deletes the given user.
	 * 
	 * @param string $username
	 */
	public function delete(string $username);
}
