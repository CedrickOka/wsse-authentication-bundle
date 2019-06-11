<?php
namespace Oka\WSSEAuthenticationBundle\Model;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
interface WSSEUserInterface extends AdvancedUserInterface
{
	const ROLE_DEFAULT = 'ROLE_API_USER';
	
	public function getId();
	
	public function setUsername(string $username) :self;
	
	public function setPassword(string $password) :self;
		
	public function hasRole(string $role) :bool;
		
	public function addRole(string $role) :self;
	
	public function setRoles(array $roles) :self;
		
	public function removeRole(string $role) :self;
		
	public function hasAllowedIp(string $ip) :bool;
	
	public function getAllowedIps() :array;
		
	public function addAllowedIp(string $ip) :self;
	
	public function removeAllowedIp(string $ip) :self;
	
	public function setEnabled(bool $enabled) :self;
	
	public function setLocked(bool $locked) :self;
}
