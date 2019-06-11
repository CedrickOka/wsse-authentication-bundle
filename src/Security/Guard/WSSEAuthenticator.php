<?php
namespace Oka\WSSEAuthenticationBundle\Security\Guard;

use Oka\WSSEAuthenticationBundle\Events;
use Oka\WSSEAuthenticationBundle\Event\AuthenticationFailureEvent;
use Oka\WSSEAuthenticationBundle\Security\Core\Exception\NonceExpiredException;
use Oka\WSSEAuthenticationBundle\Security\Nonce\Nonce;
use Oka\WSSEAuthenticationBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class WSSEAuthenticator extends AbstractGuardAuthenticator
{
	/**
	 * @var NonceHandlerInterface $nonceHandler
	 */
	private $nonceHandler;
	
	/**
	 * @var EventDispatcherInterface $dispatcher
	 */
	private $dispatcher;
	
	/**
	 * @var integer $lifetime
	 */
	private $lifetime;
	
	/**
	 * @var string $realm
	 */
	private $realm;
	
	public function __construct(NonceHandlerInterface $nonceHandler, EventDispatcherInterface $dispatcher, $lifetime, $realm = 'Secure Area')
	{
		$this->nonceHandler = $nonceHandler;
		$this->dispatcher = $dispatcher;
		$this->lifetime = $lifetime;
		$this->realm = $realm;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Security\Guard\AuthenticatorInterface::supports()
	 */
	public function supports(Request $request)
	{
		return $request->headers->has('Authorization') || $request->headers->has('X-WSSE');
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Security\Guard\AuthenticatorInterface::getCredentials()
	 */
	public function getCredentials(Request $request)
	{
		if (!$token = ($request->headers->get('Authorization') ?? $request->headers->get('X-WSSE'))) {
			throw new TokenNotFoundException('No token could be found.');
		}
		
		$credentials = [];
		
		if (!preg_match('#UsernameToken Username="([^"]+)", PasswordDigest="([^"]+)", Nonce="([^"]+)", Created="([^"]+)"#', $token, $credentials)) {
			throw new TokenNotFoundException('No token could be found.');
		}
		
		return $credentials;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Security\Guard\AuthenticatorInterface::getUser()
	 */
	public function getUser($credentials, UserProviderInterface $userProvider)
	{
		try {
			/** @var \Symfony\Component\Security\Core\User\UserInterface $user */
			return $userProvider->loadUserByUsername($credentials[1]);
		} catch (AuthenticationException $e) {
			throw new BadCredentialsException('Bad credentials.');
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Security\Guard\AuthenticatorInterface::checkCredentials()
	 */
	public function checkCredentials($credentials, UserInterface $user)
	{
		$currentTime = time();
		
		// Check that the created has not expired
		if (($currentTime < strtotime($credentials[4]) - $this->lifetime) || ($currentTime > strtotime($credentials[4]) + $this->lifetime)) {
			throw new AuthenticationException('Created timestamp is not valid.');
		}
		
		$nonce = new Nonce(base64_decode($credentials[3]), $this->nonceHandler);
		
		// Validate that the nonce is *not* used in the last 5 minutes
		// if it has, this could be a replay attack
		if (true === $nonce->isAlreadyUsed($currentTime, $this->lifetime)) {
			throw new NonceExpiredException('Digest nonce has expired.');
		}
		
		$nonce->save($currentTime);
		
		$expected = base64_encode(sha1($nonce->getId().$credentials[4].$user->getPassword(), true));
		
		// Valid the secret
		return hash_equals($expected, $credentials[2]);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Security\Guard\AuthenticatorInterface::onAuthenticationSuccess()
	 */
	public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
	{
		return null;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Security\Guard\AuthenticatorInterface::onAuthenticationFailure()
	 */
	public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
	{
		$response = new JsonResponse(['code' => 401, 'message' => $exception->getMessage()], 401, ['WWW-Authenticate' => sprintf('WSSE realm="%s", profile="UsernameToken"', $this->realm)]);
		$event = new AuthenticationFailureEvent($exception, $response);
		
		$this->dispatcher->dispatch($event, Events::AUTHENTICATION_FAILURE);
		
		return $event->getResponse();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface::start()
	 */
	public function start(Request $request, AuthenticationException $authException = null)
	{
		$exception = new TokenNotFoundException('No token could be found', 0, $authException);
		$response = new JsonResponse(['code' => 401, 'message' => $exception->getMessage()], 401, ['WWW-Authenticate' => sprintf('WSSE realm="%s", profile="UsernameToken"', $this->realm)]);
		$event = new AuthenticationFailureEvent($exception, $response);
		
		$this->dispatcher->dispatch($event, Events::AUTHENTICATION_FAILURE);
		
		return $event->getResponse();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Security\Guard\AuthenticatorInterface::supportsRememberMe()
	 */
	public function supportsRememberMe($param)
	{
		return false;
	}
}
