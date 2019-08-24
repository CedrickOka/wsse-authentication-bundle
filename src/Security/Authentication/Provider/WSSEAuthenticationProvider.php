<?php
namespace Oka\WSSEAuthenticationBundle\Security\Authentication\Provider;

use Oka\WSSEAuthenticationBundle\Security\Authentication\Token\WSSEUserToken;
use Oka\WSSEAuthenticationBundle\Security\Core\Exception\NonceExpiredException;
use Oka\WSSEAuthenticationBundle\Security\Nonce\Nonce;
use Oka\WSSEAuthenticationBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class WSSEAuthenticationProvider implements AuthenticationProviderInterface
{
	/**
	 * @var UserProviderInterface $userProvider
	 */
	private $userProvider;
	
	/**
	 * @var NonceHandlerInterface $nonceHandler
	 */
	private $nonceHandler;
	
	/**
	 * @var integer $lifetime
	 */
	private $lifetime;
	
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
	 * @throws AuthenticationException
	 * @throws NonceExpiredException
	 * @return boolean
	 */
	protected function validateDigest($digest, $nonce, $created, $secret)
	{
		$currentTime = time();
		
		// Check that the created has not expired
		if (($currentTime < strtotime($created) - $this->lifetime) || ($currentTime > strtotime($created) + $this->lifetime)) {
			throw new AuthenticationException('Created timestamp is not valid.');
		}
		
		$nonce = new Nonce(base64_decode($nonce), $this->nonceHandler);
		
		// Validate that the nonce is *not* used in the last 5 minutes
		// if it has, this could be a replay attack
		if (true === $nonce->isAlreadyUsed($currentTime, $this->lifetime)) {
			throw new NonceExpiredException('Previously used nonce detected.');
		}
		
		// Save nonce
		$nonce->save($currentTime);
		
		$expected = base64_encode(sha1($nonce->getId().$created.$secret, true));
		
		// Validate the secret
		return hash_equals($expected, $digest);
	}
}
