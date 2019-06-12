<?php
namespace Oka\WSSEAuthenticationBundle\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class WSSERequestMatcher implements RequestMatcherInterface
{
	/**
	 * {@inheritdoc}
	 * @see \Symfony\Component\HttpFoundation\RequestMatcherInterface::matches()
	 */
	public function matches(Request $request)
	{
		if (true === $request->headers->has('X-WSSE')) {
			return true;
		}
		
		if (true === $request->headers->has('Authorization')) {
			return (bool) preg_match('#^UsernameToken (.+)$#i', $request->headers->get('Authorization'));
		}
		
		return false;
	}
}
