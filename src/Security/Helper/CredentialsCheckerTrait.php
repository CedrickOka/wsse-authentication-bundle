<?php
namespace Oka\WSSEAuthenticationBundle\Security\Helper;

use Oka\WSSEAuthenticationBundle\Security\Nonce\Nonce;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
trait CredentialsCheckerTrait
{
	/**
	 * @var \Oka\WSSEAuthenticationBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface $nonceHandler
	 */
	private $nonceHandler;
	
	/**
	 * @var integer $lifetime
	 */
	private $lifetime;
	
	/**
	 * Check credentials
	 * 
	 * @param string $digest
	 * @param string $nonce
	 * @param string $created
	 * @param string $secret
	 * @throws CustomUserMessageAuthenticationException
	 * @return boolean
	 */
	public function check(string $digest, string $nonce, string $created, string $secret) :bool
	{
		$currentTime = time();
		
		// Check that the created has not expired
		if (($currentTime < strtotime($created) - $this->lifetime) || ($currentTime > strtotime($created) + $this->lifetime)) {
			throw new CustomUserMessageAuthenticationException('Created timestamp is not valid.');
		}
		
		$nonce = new Nonce(base64_decode($nonce), $this->nonceHandler);
		
		// Validate that the nonce is *not* used in the last 5 minutes
		// if it has, this could be a replay attack
		if (true === $nonce->isAlreadyUsed($currentTime, $this->lifetime)) {
			throw new CustomUserMessageAuthenticationException('Digest nonce has expired.');
		}
		
		// Save nonce
		$nonce->save($currentTime);
		
		$expected = base64_encode(sha1($nonce->getId().$created.$secret, true));
		
		// Validate the secret
		return hash_equals($expected, $digest);
	}
}
