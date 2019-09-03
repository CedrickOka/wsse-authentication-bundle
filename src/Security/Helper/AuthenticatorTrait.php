<?php
namespace Oka\WSSEAuthenticationBundle\Security\Helper;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
trait AuthenticatorTrait
{
	/**
	 * @var \Symfony\Component\Translation\TranslatorInterface $translator
	 */
	protected $translator;
	
	/**
	 * @var string $realm
	 */
	protected $realm;
	
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
	
	protected function createAuthenticationFailureResponse(AuthenticationException $exception, $statusCode = 401, array $headers = []) :JsonResponse
	{
		$message = $this->translator->trans($exception->getMessageKey(), $exception->getMessageData(), 'security');
		$headers['WWW-Authenticate'] = sprintf('WSSE realm="%s", profile="UsernameToken"', $this->realm);
		
		return new JsonResponse(['code' => $statusCode, 'message' => $message], $statusCode, $headers);
	}
}
