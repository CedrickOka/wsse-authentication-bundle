<?php
namespace Oka\WSSEAuthenticationBundle\Security\Authentication\Provider;

use Oka\WSSEAuthenticationBundle\Security\Authentication\Token\WSSEUserToken;
use Oka\WSSEAuthenticationBundle\Security\Helper\CredentialsCheckerTrait;
use Oka\WSSEAuthenticationBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class WSSEAuthenticationProvider implements AuthenticationProviderInterface
{
	use CredentialsCheckerTrait;
	
	/**
	 * @var UserProviderInterface $userProvider
	 */
	private $userProvider;
	
	/**
	 * @param UserProviderInterface $userProvider
	 * @param NonceHandlerInterface $nonceHandler
	 * @param int $lifetime
	 */
	public function __construct(UserProviderInterface $userProvider, NonceHandlerInterface $nonceHandler, $lifetime)
	{
		$this->userProvider = $userProvider;
		$this->nonceHandler = $nonceHandler;
		$this->lifetime = $lifetime;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface::supports()
	 */
	public function supports(TokenInterface $token)
	{
		return $token instanceof WSSEUserToken;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface::authenticate()
	 */
	public function authenticate(TokenInterface $token)
	{
		/** @var \Symfony\Component\Security\Core\User\AdvancedUserInterface $user */
		if ($user = $this->userProvider->loadUserByUsername($token->getUsername())) {
			if (true === $this->validateDigest($token->getAttribute('digest'), $token->getAttribute('nonce'), $token->getAttribute('created'), $user->getPassword())) {
				return new WSSEUserToken($user, $token->getCredentials(), $user->getRoles());
			}
		}
		
		throw new BadCredentialsException('Bad credentials.');
	}
	
	/**
	 * Validate digest password
	 * 
	 * @param string $digest
	 * @param string $nonce
	 * @param string $created
	 * @param string $secret
	 * @throws \Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException
	 * @return boolean
	 */
	protected function validateDigest($digest, $nonce, $created, $secret)
	{
		return $this->check($digest, $nonce, $created, $secret);
	}
}
