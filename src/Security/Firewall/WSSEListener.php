<?php
namespace Oka\WSSEAuthenticationBundle\Security\Firewall;

use Oka\WSSEAuthenticationBundle\Events;
use Oka\WSSEAuthenticationBundle\Event\AuthenticationFailureEvent;
use Oka\WSSEAuthenticationBundle\Security\AuthenticatorTrait;
use Oka\WSSEAuthenticationBundle\Security\Authentication\Token\WSSEUserToken;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\LegacyListenerTrait;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class WSSEListener implements ListenerInterface
{
	use LegacyListenerTrait, AuthenticatorTrait;
	
	/**
	 * @var TokenStorageInterface $tokenStorage
	 */
	private $tokenStorage;
	
	/**
	 * @var AuthenticationManagerInterface $authenticationManager
	 */
	private $authenticationManager;
	
	/**
	 * @var EventDispatcherInterface $dispatcher
	 */
	private $dispatcher;
	
	/**
	 * @var LoggerInterface $logger
	 */
	private $logger;
	
	/**
	 * @param TokenStorageInterface $tokenStorage
	 * @param AuthenticationManagerInterface $authenticationManager
	 * @param EventDispatcherInterface $dispatcher
	 * @param TranslatorInterface $translator
	 * @param LoggerInterface $logger
	 * @param string $realm
	 */
	public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager, EventDispatcherInterface $dispatcher, TranslatorInterface $translator, LoggerInterface $logger, $realm)
	{
		$this->tokenStorage = $tokenStorage;
		$this->authenticationManager = $authenticationManager;
		$this->dispatcher = $dispatcher;
		$this->translator = $translator;
		$this->logger = $logger;
		$this->realm = $realm;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Security\Http\Firewall\ListenerInterface::__invoke()
	 */
	public function __invoke(RequestEvent $event)
	{
		try {
			$credentials = $this->getCredentials($event->getRequest());
			
			$preAuthToken = new WSSEUserToken($credentials[1], $credentials, []);
			$preAuthToken->setAttribute('digest', $credentials[2]);
			$preAuthToken->setAttribute('nonce', $credentials[3]);
			$preAuthToken->setAttribute('created', $credentials[4]);
			
			$authToken = $this->authenticationManager->authenticate($preAuthToken);
			$this->tokenStorage->setToken($authToken);
		} catch (AuthenticationException $e) {
			// Log error
			$this->logger->info(sprintf('Login with WS-Security failed, caused by : %s', $e->getMessage()), $credentials ?? []);
			
			$authenticationFailureEvent = new AuthenticationFailureEvent($e, $this->createAuthenticationFailureResponse($e));
			$this->dispatcher->dispatch($authenticationFailureEvent, Events::AUTHENTICATION_FAILURE);
			
// 			// To deny the authentication clear the token. This will redirect to the login page.
// 			// Make sure to only clear your token, not those of other authentication listeners.
// 			$token = $this->tokenStorage->getToken();
			
// 			if ($token instanceof WSSEUserToken && $this->providerKey === $token->getProviderKey()) {
// 				$this->tokenStorage->setToken(null);
// 			}
			
			// Deny authentication with a '401 Unauthorized' HTTP response
			$event->setResponse($authenticationFailureEvent->getResponse());
		}
	}
}
