<?php
namespace Oka\WSSEAuthenticationBundle\Security\Guard;

use Oka\WSSEAuthenticationBundle\Events;
use Oka\WSSEAuthenticationBundle\Event\AuthenticationFailureEvent;
use Oka\WSSEAuthenticationBundle\Security\Helper\AuthenticatorTrait;
use Oka\WSSEAuthenticationBundle\Security\Helper\CredentialsCheckerTrait;
use Oka\WSSEAuthenticationBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Translation\TranslatorInterface;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class WSSEAuthenticator extends AbstractGuardAuthenticator
{
	use AuthenticatorTrait, CredentialsCheckerTrait;
	
	/**
	 * @var EventDispatcherInterface $dispatcher
	 */
	private $dispatcher;
	
	public function __construct(NonceHandlerInterface $nonceHandler, EventDispatcherInterface $dispatcher, TranslatorInterface $translator, $lifetime, $realm)
	{
		$this->nonceHandler = $nonceHandler;
		$this->dispatcher = $dispatcher;
		$this->translator = $translator;
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
		return $this->check($credentials[2], $credentials[3], $credentials[4], $user->getPassword());
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
		$event = new AuthenticationFailureEvent($exception, $this->createAuthenticationFailureResponse($exception));
		$this->dispatcher->dispatch(Events::AUTHENTICATION_FAILURE, $event);
		
		return $event->getResponse();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface::start()
	 */
	public function start(Request $request, AuthenticationException $authException = null)
	{
		$exception = new TokenNotFoundException('No token could be found', 401, $authException);
		$event = new AuthenticationFailureEvent($exception, $this->createAuthenticationFailureResponse($exception));
		$this->dispatcher->dispatch(Events::AUTHENTICATION_FAILURE, $event);
		
		return $event->getResponse();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Security\Guard\AuthenticatorInterface::supportsRememberMe()
	 */
	public function supportsRememberMe()
	{
		return false;
	}
}
