<?php
namespace Oka\WSSEAuthenticationBundle\Security\Nonce\Storage\Handler;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class MemcacheNonceHandler implements NonceHandlerInterface
{
	/**
	 * @var \Memcache Memcache driver
	 */
	private $memcache;
	
	/**
	 * @var int Time to live in seconds
	 */
	private $ttl;
	
	/**
	 * @var string Key prefix for shared environments
	 */
	private $prefix;
	
	public function __construct(\Memcache $memcache, array $options = [])
	{
		if ($diff = array_diff(array_keys($options), ['prefix', 'expiretime'])) {
			throw new \InvalidArgumentException(sprintf(
					'The following options are not supported "%s"', implode(', ', $diff)
			));
		}
		
		$this->memcache = $memcache;
		$this->ttl = isset($options['expiretime']) ? (int) $options['expiretime'] : 300;
		$this->prefix = isset($options['prefix']) ? $options['prefix'] : 'oka';
	}
	
	/**
	 * {@inheritdoc}
	 * @see \Oka\WSSEAuthenticationBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface::open()
	 */
	public function open($savePath)
	{
		return true;
	}
	
	/**
	 * {@inheritdoc}
	 * @see \Oka\WSSEAuthenticationBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface::close()
	 */
	public function close()
	{
		return true;
	}
	
	/**
	 * {@inheritdoc}
	 * @see \Oka\WSSEAuthenticationBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface::read()
	 */
	public function read($nonceId)
	{
		return (int) ($this->memcache->get($this->prefix.$nonceId) ?: 0);
	}
	
	/**
	 * {@inheritdoc}
	 * @see \Oka\WSSEAuthenticationBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface::write()
	 */
	public function write($nonceId, $nonceTime)
	{
		return $this->memcache->set($this->prefix.$nonceId, $nonceTime, 0, time() + $this->ttl);
	}
	
	/**
	 * {@inheritdoc}
	 * @see \Oka\WSSEAuthenticationBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface::destroy()
	 */
	public function destroy($nonceId)
	{
		$this->memcache->delete($this->prefix.$nonceId);
		
		return true;
	}
	
	/**
	 * {@inheritdoc}
	 * @see \Oka\WSSEAuthenticationBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface::gc()
	 */
	public function gc($maxlifetime)
	{
		// not required here because memcache will auto expire the records anyhow.
		return true;
	}
}
