<?php
namespace Oka\WSSEAuthenticationBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Oka\WSSEAuthenticationBundle\Events;
use Oka\WSSEAuthenticationBundle\Event\WSSEUserEvent;
use Oka\WSSEAuthenticationBundle\Model\WSSEUserInterface;
use Oka\WSSEAuthenticationBundle\Model\WSSEUserManipulatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * 
 * Executes some manipulations on the WSSE users.
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class WSSEUserManipulator implements WSSEUserManipulatorInterface
{
	/**
	 * @var \Doctrine\Common\Persistence\ObjectManager $objectManager
	 */
	private $objectManager;
	
	/**
	 * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
	 */
	private $dispatcher;
	
	/** 
	 * @var string $class
	 */
	private $class;
	
	/**
	 * @var \Doctrine\Common\Persistence\ObjectRepository $objectRepository
	 */
	private $objectRepository;

	/**
	 * WSSEUserManipulator constructor.
	 * 
	 * @param ObjectManager				$objectManager
	 * @param EventDispatcherInterface 	$dispatcher
	 * @param string					$class
	 */
	public function __construct(ObjectManager $objectManager, EventDispatcherInterface $dispatcher, $class)
	{
		$metadata = $objectManager->getClassMetadata($class);
		
		$this->objectManager = $objectManager;
		$this->dispatcher = $dispatcher;
		$this->class = $metadata->getName();
		$this->objectRepository = $objectManager->getRepository($class);
	}

	/**
	 * {@inheritDoc}
	 * @see \Oka\WSSEAuthenticationBundle\Model\WSSEUserManipulatorInterface::create()
	 */
	public function create(string $username, string $password, bool $active, array $roles = []) :WSSEUserInterface
	{
		/** @var \Oka\WSSEAuthenticationBundle\Model\UserInterface $user */
		$user = new $this->class();
		$user->setUsername($username);
		$user->setPassword($password);
		$user->setEnabled((boolean) $active);
		$user->setRoles($roles);
		
		$this->saveUser($user);
		
		$this->dispatcher->dispatch(Events::USER_CREATED, new WSSEUserEvent($user));
		
		return $user;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\WSSEAuthenticationBundle\Model\WSSEUserManipulatorInterface::activate()
	 */
	public function activate(string $username)
	{
		$user = $this->findUserByUsernameOrThrowException($username);
		$user->setEnabled(true);
		
		$this->saveUser($user);
		
		$this->dispatcher->dispatch(Events::USER_ACTIVATED, new WSSEUserEvent($user));
	}

	/**
	 * {@inheritDoc}
	 * @see \Oka\WSSEAuthenticationBundle\Model\WSSEUserManipulatorInterface::deactivate()
	 */
	public function deactivate(string $username)
	{
		$user = $this->findUserByUsernameOrThrowException($username);
		$user->setEnabled(false);
		
		$this->saveUser($user);
		
		$this->dispatcher->dispatch(Events::USER_DEACTIVATED, new WSSEUserEvent($user));
	}

	/**
	 * {@inheritDoc}
	 * @see \Oka\WSSEAuthenticationBundle\Model\WSSEUserManipulatorInterface::changePassword()
	 */
	public function changePassword(string $username, string $password)
	{
		$user = $this->findUserByUsernameOrThrowException($username);
		$user->setPassword($password);
		
		$this->saveUser($user);
		
		$this->dispatcher->dispatch(Events::USER_PASSWORD_CHANGED, new WSSEUserEvent($user));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\WSSEAuthenticationBundle\Model\WSSEUserManipulatorInterface::addRole()
	 */
	public function addRole(string $username, string $role) :bool
	{
		$user = $this->findUserByUsernameOrThrowException($username);
		
		if (true === $user->hasRole($role)) {
			return false;
		}
		
		$user->addRole($role);
		$this->saveUser($user);
		
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @see \Oka\WSSEAuthenticationBundle\Model\WSSEUserManipulatorInterface::removeRole()
	 */
	public function removeRole(string $username, string $role) :bool
	{
		$user = $this->findUserByUsernameOrThrowException($username);
		
		if (false === $user->hasRole($role)) {
			return false;
		}
		
		$user->removeRole($role);
		$this->saveUser($user);
		
		return true;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\WSSEAuthenticationBundle\Model\WSSEUserManipulatorInterface::addAllowedIp()
	 */
	public function addAllowedIp(string $username, string $ip) :bool
	{
		$user = $this->findUserByUsernameOrThrowException($username);
		
		if (true === $user->hasAllowedIp($ip)) {
			return false;
		}
		
		$user->addAllowedIp($ip);
		$this->saveUser($user);
		
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @see \Oka\WSSEAuthenticationBundle\Model\WSSEUserManipulatorInterface::removeAllowedIp()
	 */
	public function removeAllowedIp(string $username, string $ip) :bool
	{
		$user = $this->findUserByUsernameOrThrowException($username);
		
		if (false === $user->hasAllowedIp($ip)) {
			return false;
		}
		
		$user->removeAllowedIp($ip);
		$this->saveUser($user);
		
		return true;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\WSSEAuthenticationBundle\Model\WSSEUserManipulatorInterface::delete()
	 */
	public function delete(string $username)
	{
		$user = $this->findUserByUsernameOrThrowException($username);
		
		$this->objectManager->remove($user);
		$this->objectManager->flush($user);
		
		$this->dispatcher->dispatch(Events::USER_DELETED, new WSSEUserEvent($user));
	}

	/**
	 * Finds a user by his username and throws an exception if we can't find it.
	 * 
	 * @param string $username
	 * 
	 * @throws \InvalidArgumentException When user does not exist
	 * 
	 * @return \Oka\WSSEAuthenticationBundle\Model\WSSEUserInterface
	 */
	private function findUserByUsernameOrThrowException(string $username) :?WSSEUserInterface
	{
		if (!$user = $this->objectRepository->findOneBy(['username' => $username])) {
			throw new \InvalidArgumentException(sprintf('User identified by "%s" username does not exist.', $username));
		}
		
		return $user;
	}
	
	/**
	 * Save user in database
	 * 
	 * @param WSSEUserInterface $user
	 * 
	 * @return \Oka\WSSEAuthenticationBundle\Model\WSSEUserInterface
	 */
	private function saveUser(WSSEUserInterface $user)
	{
		if (false === $this->objectManager->contains($user)) {
			$this->objectManager->persist($user);
		}
		
		$this->objectManager->flush($user);
	}
}
