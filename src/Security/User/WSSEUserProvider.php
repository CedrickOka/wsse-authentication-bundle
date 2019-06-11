<?php
namespace Oka\WSSEAuthenticationBundle\Security\User;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class WSSEUserProvider implements UserProviderInterface
{
	/**
	 * @var \Doctrine\Common\Persistence\ObjectManager $om
	 */
	protected $om;
	
	/**
	 * @var \Doctrine\Common\Persistence\ObjectRepository $or
	 */
	protected $or;
	
	/**
	 * @var string $class
	 */
	protected $class;
	
	/**
	 * Constructor for WSSE authetication User provider 
	 * 
	 * @param ObjectManager $om
	 * @param string $class
	 */
	public function __construct(ObjectManager $om, $class)
	{
		$metadata = $om->getClassMetadata($class);
		
		$this->om = $om;
		$this->class = $metadata->getName();
		$this->or = $om->getRepository($class);
	}
	
	/**
	 * Loads the user for the given username.
	 * 
	 * This method must throw UsernameNotFoundException if the user is not
	 * found.
	 * 
	 * @param string $username The username
	 * 
	 * @return UserInterface
	 * 
	 * @see UsernameNotFoundException
	 * 
	 * @throws UsernameNotFoundException if the user is not found
	 */
	public function loadUserByUsername($username)
	{
		if (!$user = $this->or->findOneBy(['username' => $username])) {
			throw new UsernameNotFoundException(sprintf('User username "%s" does not exist.', $username));
		}
		
		return $user;
	}
	
	/**
	 * Refreshes the user for the account interface.
	 *
	 * It is up to the implementation to decide if the user data should be
	 * totally reloaded (e.g. from the database), or if the UserInterface
	 * object can just be merged into some internal array of users / identity
	 * map.
	 * @param UserInterface $user
	 *
	 * @return UserInterface
	 *
	 * @throws UnsupportedUserException if the account is not supported
	 */
	public function refreshUser(UserInterface $user)
	{
		$class = get_class($user);
		
		if (!$this->supportsClass($class)) {
			throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $class));
		}
		
		return $this->or->find($user->getId());
	}
	
	/**
	 * Whether this provider supports the given user class
	 * 
	 * @param string $class
	 * 
	 * @return Boolean
	 */
	public function supportsClass($class)
	{
		return is_subclass_of($class, \Oka\WSSEAuthenticationBundle\Model\WSSEUserInterface::class);
	}
}
