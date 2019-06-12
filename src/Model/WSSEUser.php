<?php
namespace Oka\WSSEAuthenticationBundle\Model;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
abstract class WSSEUser implements WSSEUserInterface
{
	/**
	 * @var mixed $id
	 */
	protected $id;
	
	/**
	 * @var string $username
	 */
	protected $username;
	
	/**
	 * @var string $password
	 */
	protected $password;
	
	/**
	 * @var array $roles
	 */
	protected $roles;
	
	/**
	 * @var boolean $enabled
	 */
	protected $enabled;
	
	/**
	 * @var boolean $locked
	 */
	protected $locked;
	
	/**
	 * @var array allowedIps
	 */
	protected $allowedIps;
	
	public function __construct()
	{
		$this->enabled = true;
		$this->locked = false;
		$this->roles = [];
		$this->allowedIps = [];
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function getUsername()
	{
		return $this->username;
	}
	
	public function setUsername(string $username) :WSSEUserInterface
	{
		$this->username = $username;
		return $this;
	}
	
	public function getPassword()
	{
		return $this->password;
	}
	
	public function setPassword(string $password) :WSSEUserInterface
	{
		$this->password = $password;
		return $this;
	}
	
	public function getSalt()
	{
		return null;
	}
	
	public function hasRole(string $role) :bool
	{
		return in_array(strtoupper($role), $this->roles, true);
	}
	
	public function getRoles() :array
	{
		$roles = $this->roles;
		
		// we need to make sure to have at least one role
		$roles[] = static::ROLE_DEFAULT;
		
		return array_unique($roles);
	}
	
	public function addRole(string $role) :WSSEUserInterface
	{
		$role = strtoupper($role);
		
		if ($role !== static::ROLE_DEFAULT && false === in_array($role, $this->roles, true)) {
			$this->roles[] = $role;
		}
		
		return $this;
	}
	
	public function setRoles(array $roles) :WSSEUserInterface
	{
		$this->roles = [];
		
		foreach ($roles as $role) {
			$this->addRole($role);
		}
		
		return $this;
	}
	
	public function removeRole(string $role) :WSSEUserInterface
	{
		$role = strtoupper($role);
		
		if (false !== ($key = array_search($role, $this->roles, true))) {
			unset($this->roles[$key]);
			$this->roles = array_values($this->roles);
		}
		
		return $this;
	}
	
	public function isEnabled()
	{
		return $this->enabled;
	}
	
	public function setEnabled(bool $enabled) :WSSEUserInterface
	{
		$this->enabled = $enabled;
		return $this;
	}
	
	public function setLocked(bool $locked) :WSSEUserInterface
	{
		$this->locked = $locked;
		return $this;
	}
	
	public function eraseCredentials() {}
	
	public function isAccountNonExpired()
	{
		return true;
	}
	
	public function isAccountNonLocked()
	{
		return !$this->locked;
	}
	
	public function isCredentialsNonExpired()
	{
		return true;
	}
	
	public function hasAllowedIp(string $ip) :bool
	{
		return in_array($ip, $this->allowedIps, true);
	}
	
	public function getAllowedIps() :array
	{
		return $this->allowedIps;
	}
	
	public function addAllowedIp(string $ip) :WSSEUserInterface
	{
		if (false === in_array($ip, $this->allowedIps, true)) {
			$this->allowedIps[] = $ip;
		}
		return $this;
	}
	
	public function setAllowedIps(array $allowedIps) :WSSEUserInterface
	{
		$this->allowedIps = [];
		foreach ($allowedIps as $ip) {
			$this->addAllowedIp($ip);
		}
		return $this;
	}
	
	public function removeAllowedIp(string $ip) :WSSEUserInterface
	{
		if (false !== ($key = array_search($ip, $this->allowedIps, true))) {
			unset($this->allowedIps[$key]);
			$this->allowedIps = array_values($this->allowedIps);
		}
		return $this;
	}
}
