<?php
namespace Oka\WSSEAuthenticationBundle\Tests\Util;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class WSSEUserManipulatorTest extends KernelTestCase
{
	/**
	 * @var \Oka\WSSEAuthenticationBundle\Security\User\WSSEUserProvider $userProvider
	 */
	protected $userProvider;
	
	/**
	 * @var \Oka\WSSEAuthenticationBundle\Util\WSSEUserManipulator $userManipulator
	 */
	protected $userManipulator;
	
	public function setUp()
	{
		self::bootKernel();
		
		$this->userProvider = static::$kernel->getContainer()->get('oka_wsse_authentication.wsse_user_provider');
		$this->userManipulator = static::$kernel->getContainer()->get('oka_wsse_authentication.util.wsse_user_manipulator');
	}
	
	/**
	 * @covers WSSEUserManipulator::create
	 */
	public function testCreate()
	{
		$username = 'client_test';
		$this->userManipulator->create($username, $username, true);
		$user = $this->userProvider->loadUserByUsername($username);
		
		$this->assertEquals('client_test', $user->getUsername());
		$this->assertEquals('client_test', $user->getPassword());
		$this->assertEquals(true, $user->isEnabled());
		
		return $username;
	}
	
	/**
	 * @covers WSSEUserManipulator::activate
	 * @depends testCreate
	 */
	public function testActivate($username)
	{
		$this->userManipulator->activate($username);
		$user = $this->userProvider->loadUserByUsername($username);
				
		$this->assertEquals(true, $user->isEnabled());
		
		return $username;
	}
	
	/**
	 * @covers WSSEUserManipulator::deactivate
	 * @depends testActivate
	 */
	public function testDeactivate($username)
	{
		$this->userManipulator->deactivate($username);
		$user = $this->userProvider->loadUserByUsername($username);
				
		$this->assertEquals(false, $user->isEnabled());
		
		return $username;
	}
	
	/**
	 * @covers WSSEUserManipulator::changePassword
	 * @depends testDeactivate
	 */
	public function testChangePassword($username)
	{
		$this->userManipulator->changePassword($username, 'new_password');
		$user = $this->userProvider->loadUserByUsername($username);
				
		$this->assertEquals('new_password', $user->getPassword());
		
		return $username;
	}
	
	/**
	 * @covers WSSEUserManipulator::addRole
	 * @depends testChangePassword
	 */
	public function testAddRole($username)
	{
		$this->userManipulator->addRole($username, 'ROLE_TEST');
		$user = $this->userProvider->loadUserByUsername($username);
				
		$this->assertEquals(true, $user->hasRole('ROLE_TEST'));
		
		return $username;
	}
	
	/**
	 * @covers WSSEUserManipulator::removeRole
	 * @depends testAddRole
	 */
	public function testRemoveRole($username)
	{
		$this->userManipulator->removeRole($username, 'ROLE_TEST');
		$user = $this->userProvider->loadUserByUsername($username);
		
		$this->assertEquals(false, $user->hasRole('ROLE_TEST'));
		
		return $username;
	}
	
	/**
	 * @covers WSSEUserManipulator::addAllowedIp
	 * @depends testRemoveRole
	 */
	public function testAddAllowedIp($username)
	{
		$this->userManipulator->addAllowedIp($username, '127.0.0.1');
		$user = $this->userProvider->loadUserByUsername($username);
		
		$this->assertEquals(true, $user->hasAllowedIp('127.0.0.1'));
		
		return $username;
	}
	
	/**
	 * @covers WSSEUserManipulator::removeAllowedIp
	 * @depends testAddAllowedIp
	 */
	public function testRemoveAllowedIp($username)
	{
		$this->userManipulator->removeAllowedIp($username, '127.0.0.1');
		$user = $this->userProvider->loadUserByUsername($username);
		
		$this->assertEquals(false, $user->hasAllowedIp('127.0.0.1'));
		
		return $username;
	}
	
	/**
	 * @covers WSSEUserManipulator::delete
	 * @depends testRemoveAllowedIp
	 * @expectedException \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
	 */
	public function testDelete($username)
	{
		$this->userManipulator->delete($username);
		$this->userProvider->loadUserByUsername($username);
	}
}
