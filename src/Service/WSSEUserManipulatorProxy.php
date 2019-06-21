<?php
namespace Oka\WSSEAuthenticationBundle\Service;

use Oka\WSSEAuthenticationBundle\Model\WSSEUserInterface;
use Oka\WSSEAuthenticationBundle\Model\WSSEUserManipulatorInterface;

/**
 * 
 * WSSE user manipulatior proxy.
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class WSSEUserManipulatorProxy implements WSSEUserManipulatorInterface
{
	/**
	 * @var WSSEUserManipulatorInterface $userManipulator
	 */
	private $userManipulator;
	
	public function setUserManipulator(WSSEUserManipulatorInterface $userManipulator)
	{		
		return $this->userManipulator = $userManipulator;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\WSSEAuthenticationBundle\Model\WSSEUserManipulatorInterface::create()
	 */
	public function create(string $username, string $password, bool $active, array $roles = []) :WSSEUserInterface
	{
		$this->throwExceptionIfIsEmpty();
		
		return $this->userManipulator->create($username, $password, $active, $roles);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\WSSEAuthenticationBundle\Model\WSSEUserManipulatorInterface::activate()
	 */
	public function activate(string $username)
	{
		$this->throwExceptionIfIsEmpty();
		
		$this->userManipulator->activate($username);
	}

	/**
	 * {@inheritDoc}
	 * @see \Oka\WSSEAuthenticationBundle\Model\WSSEUserManipulatorInterface::deactivate()
	 */
	public function deactivate(string $username)
	{
		$this->throwExceptionIfIsEmpty();
		
		$this->userManipulator->deactivate($username);
	}

	/**
	 * {@inheritDoc}
	 * @see \Oka\WSSEAuthenticationBundle\Model\WSSEUserManipulatorInterface::changePassword()
	 */
	public function changePassword(string $username, string $password)
	{
		$this->throwExceptionIfIsEmpty();
		
		$this->userManipulator->changePassword($username, $password);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\WSSEAuthenticationBundle\Model\WSSEUserManipulatorInterface::addRole()
	 */
	public function addRole(string $username, string $role) :bool
	{
		$this->throwExceptionIfIsEmpty();
		
		return $this->userManipulator->addRole($username, $role);
	}

	/**
	 * {@inheritDoc}
	 * @see \Oka\WSSEAuthenticationBundle\Model\WSSEUserManipulatorInterface::removeRole()
	 */
	public function removeRole(string $username, string $role) :bool
	{
		$this->throwExceptionIfIsEmpty();
		
		return $this->userManipulator->removeRole($username, $role);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\WSSEAuthenticationBundle\Model\WSSEUserManipulatorInterface::addAllowedIp()
	 */
	public function addAllowedIp(string $username, string $ip) :bool
	{
		$this->throwExceptionIfIsEmpty();
		
		return $this->userManipulator->addAllowedIp($username, $ip);
	}

	/**
	 * {@inheritDoc}
	 * @see \Oka\WSSEAuthenticationBundle\Model\WSSEUserManipulatorInterface::removeAllowedIp()
	 */
	public function removeAllowedIp(string $username, string $ip) :bool
	{
		$this->throwExceptionIfIsEmpty();
		
		return $this->userManipulator->removeAllowedIp($username, $ip);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\WSSEAuthenticationBundle\Model\WSSEUserManipulatorInterface::delete()
	 */
	public function delete(string $username)
	{
		$this->throwExceptionIfIsEmpty();
		
		$this->userManipulator->delete($username);
	}
	
	private function throwExceptionIfIsEmpty()
	{
		if (null === $this->userManipulator) {
			throw new \LogicException('Install the bundles "doctrine/doctrine-bundle" or "doctrine/mongodb-odm-bundle" and configure "oka_wsse_authentication.user_class" for to be able to use the service with ID "@oka_wsse_authentication.util.wsse_user_manipulator".');
		}
	}
}
