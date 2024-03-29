<?php
namespace Oka\WSSEAuthenticationBundle\Security\Nonce;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
interface NonceInterface
{
	/**
	 * @return string The nonce ID
	 */
	public function getId() :string;
	
	/**
	 * @return int The timestamp at which the nonce was created
	 */
	public function getIssuedAt() :int;
	
	/**
	 * Indicates whether the nonce is already registered in the nonce storage
	 * and validate that the nonce is *not* used in the last minutes equals at the lifetime
	 * 
	 * @param int $time The current timestamp
	 * @param int $lifetime The life time
	 * @return bool
	 */
	public function isAlreadyUsed(int $time, int $lifetime) :bool;
	
	/**
	 * Save the nonce in the storage
	 * 
	 * @param int $timestamp
	 */
	public function save(int $timestamp = null);
}
