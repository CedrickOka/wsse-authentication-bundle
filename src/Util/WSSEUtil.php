<?php
namespace Oka\WSSEAuthenticationBundle\Util;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
final class WSSEUtil
{
	/**
	 * Digests password
	 * 
	 * @param string $password
	 * @param string $salt
	 * @return string
	 */
	public static function digestPassword(string $password, string $salt = '') :string
	{
		return base64_encode(sha1($salt.$password, true));
	}
	
	/**
	 * Generate Nonce
	 * 
	 * @return string
	 */
	public static function generateNonce() :string
	{
		return base64_encode(substr(md5(uniqid()), 0, 16));
	}
	
	/**
	 * Generate Token
	 * 
	 * @param string $username
	 * @param string $password
	 * @param string $nonce
	 * @param \DateTime $created
	 * @return string
	 */
	public static function generateToken(string $username, string $password, string $nonce = null, \DateTime $created = null) :string
	{
		if (null === $nonce) {
			$nonce = self::generateNonce();
		}
		
		$created = $created ? $created->format('c') : date('c');
		
		return sprintf(
				'UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"', 
				$username, 
				self::digestPassword($password, base64_decode($nonce).$created), 
				$nonce, 
				$created
		);
	}
}
