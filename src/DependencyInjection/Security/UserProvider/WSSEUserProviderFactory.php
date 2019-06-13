<?php
namespace Oka\WSSEAuthenticationBundle\DependencyInjection\Security\UserProvider;

use Oka\WSSEAuthenticationBundle\Model\WSSEUserInterface;
use Oka\WSSEAuthenticationBundle\Security\User\WSSEUserProvider;
use Oka\WSSEAuthenticationBundle\Util\WSSEUserManipulator;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\UserProvider\UserProviderFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class WSSEUserProviderFactory implements UserProviderFactoryInterface
{
	public function create(ContainerBuilder $container, $id, $config)
	{
		// Configure user provider
		$userProviderDefinition = new Definition(WSSEUserProvider::class);
		$userProviderDefinition->addArgument(new Reference('oka_wsse_authentication.object_manager'));
		$userProviderDefinition->addArgument($config['user_class']);
		$container->setDefinition('oka_wsse_authentication.wsse_user_provider', $userProviderDefinition);
		
		// Configure user manipulator
		$userManipulatorDefinition = new Definition(WSSEUserManipulator::class);
		$userManipulatorDefinition->addArgument(new Reference('oka_wsse_authentication.object_manager'));
		$userManipulatorDefinition->addArgument(new Reference('event_dispatcher'));
		$userManipulatorDefinition->addArgument($config['user_class']);
		$userManipulatorDefinition->setPublic(true);
		$container->setDefinition('oka_wsse_authentication.util.wsse_user_manipulator', $userManipulatorDefinition);
	}
	
	public function addConfiguration(NodeDefinition $builder)
	{
		$builder
			->children()
				->scalarNode('class')
					->cannotBeEmpty()
					->validate()
						->ifTrue(function($class){
							return !(new \ReflectionClass($class))->implementsInterface(WSSEUserInterface::class);
						})
						->thenInvalid('The %s class must implement '.WSSEUserInterface::class.' for using the "oka_wsse" user provider.')
					->end()
				->end()
			->end();
	}
	
	public function getKey()
	{
		return 'oka_wsse';
	}
}
