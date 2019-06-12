<?php
namespace Oka\WSSEAuthenticationBundle\Event;

use Oka\WSSEAuthenticationBundle\Model\WSSEUserInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class WSSEUserEvent extends Event
{
	/**
	 * @var WSSEUserInterface $user
	 */
	protected $user;
	
	/**
	 * @param WSSEUserInterface $user
	 */
	public function __construct(WSSEUserInterface $user)
	{
		$this->user = $user;
	}
	
	/**
	 * @return \Oka\WSSEAuthenticationBundle\Model\WSSEUserInterface
	 */
	public function getUser() :WSSEUserInterface
	{
		return $this->user;
	}
}
