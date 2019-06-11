<?php
namespace Oka\WSSEAuthenticationBundle\Security\Nonce;

use Oka\WSSEAuthenticationBundle\Security\Nonce\Storage\Handler\NonceHandlerInterface;
use Oka\WSSEAuthenticationBundle\Security\Nonce\Storage\Proxy\NonceHandlerProxy;

/**
 *
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 *
 */
class Nonce implements NonceInterface
{
	/**
	 * @var string $id
	 */
	private $id;
	
	/**
	 * @var int $issuedAt
	 */
	private $issuedAt;
	
	/**
	 * @var NonceHandlerProxy $savedHandler
	 */
	private $savedHandler;
	
	/**
	 * @var string $savePath
	 */
	private $savePath;
	
	/**
	 * @var bool $started
	 */
	private $started = false;
	
	/**
	 * @var bool $started
	 */
	private $closed = true;
	
	/**
	 * @param string $id
	 * @param mixed $handler
	 * @param string $savePath
	 */
	public function __construct($id, $handler, $savePath = '')
	{
		$this->id = $id;
		$this->savePath = $savePath;
		$this->setSavedHandler($handler);
		$this->start();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\WSSEAuthenticationBundle\Security\Nonce\NonceInterface::getId()
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\WSSEAuthenticationBundle\Security\Nonce\NonceInterface::getIssuedAt()
	 */
	public function getIssuedAt()
	{
		if ($this->savedHandler->isActive() && !$this->started) {
			$this->start();
		} elseif (!$this->started) {
			throw new \RuntimeException('Failed to get the nonce timestamp because the nonce storage has not been started.');
		}
		
		if (!$this->issuedAt) {
			$this->issuedAt = $this->savedHandler->read($this->id);
		}
		
		return $this->issuedAt;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\WSSEAuthenticationBundle\Security\Nonce\NonceInterface::isAlreadyUsed()
	 */
	public function isAlreadyUsed($time, $lifetime)
	{
		if ($issuedAt = $this->getIssuedAt()) {
			return ($issuedAt + $lifetime) > $time;
		}
		
		return false;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Oka\WSSEAuthenticationBundle\Security\Nonce\NonceInterface::save()
	 */
	public function save($timestamp = null)
	{
		if (!$this->started) {
			throw new \RuntimeException('Failed to save the nonce because the nonce storage has not been started.');
		}
		
		if ($this->closed) {
			throw new \RuntimeException('Failed to save the nonce because the nonce storage has been closed.');
		}
		
		$this->savedHandler->write($this->id, $timestamp ?: time());
		$this->close();
	}
	
	/**
	 * Gets the save handler instance.
	 * 
	 * @return NonceHandlerProxy
	 */
	public function getSavedHandler()
	{
		return $this->savedHandler;
	}
	
	/**
	 * Registers nonce save handler.
	 * 
	 * @param NonceHandlerProxy|NonceHandlerInterface $savedHandler
	 * 
	 * @throws \InvalidArgumentException
	 */
	public function setSavedHandler($savedHandler)
	{
		if (!$savedHandler instanceof NonceHandlerProxy &&
			!$savedHandler instanceof NonceHandlerInterface) {
			throw new \InvalidArgumentException('Must be instance of NonceHandlerProxy; or implement NonceHandlerInterface.');
		}
		
		// Wrap $savedHandler in proxy and prevent double wrapping of proxy
		if (!$savedHandler instanceof NonceHandlerProxy && $savedHandler instanceof NonceHandlerInterface) {
			$savedHandler = new NonceHandlerProxy($savedHandler);
		}
		
		$this->savedHandler = $savedHandler;
	}
	
	protected function start()
	{
		if ($this->started) {
			return true;
		}
		
		if (!$this->savedHandler->open($this->savePath)) {
			throw new \RuntimeException('Failed to start the nonce storage.');
		}
		
		$this->started = true;
		$this->closed = false;
		
		return true;
	}
	
	protected function close()
	{
		if ($this->closed) {
			return true;
		}
		
		if (!$this->savedHandler->close()) {
			throw new \RuntimeException('Failed to close the nonce storage.');
		}
		
		$this->started = false;
		$this->closed = true;
		
		return true;
	}
}
