<?php
namespace Oka\WSSEAuthenticationBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class AuthenticationFailureEvent extends Event
{
	/**
	 * @var AuthenticationException
	 */
	protected $exception;
	
	/**
	 * @var Response
	 */
	protected $response;
	
	/**
	 * @param AuthenticationException $exception
	 * @param Response                $response
	 */
	public function __construct(AuthenticationException $exception, Response $response)
	{
		$this->exception = $exception;
		$this->response  = $response;
	}
	
	/**
	 * @return AuthenticationException
	 */
	public function getException() :AuthenticationException
	{
		return $this->exception;
	}
	
	/**
	 * @return Response
	 */
	public function getResponse() :Response
	{
		return $this->response;
	}
	
	/**
	 * @param Response $response
	 */
	public function setResponse(Response $response)
	{
		$this->response = $response;
	}
}
