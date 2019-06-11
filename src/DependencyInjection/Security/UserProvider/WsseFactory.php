<?php
namespace Oka\WSSEAuthenticationBundle\DependencyInjection\Security\UserProvider;

use Oka\WSSEAuthenticationBundle\Model\WSSEUserInterface;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\UserProvider\UserProviderFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class WsseFactory implements UserProviderFactoryInterface
{
	public function create(ContainerBuilder $container, $id, $config)
	{
		$definition = $container->setDefinition($id, new ChildDefinition('oka_wsse_authentication.wsse_user_provider'));
		$definition->replaceArgument(1, $config['class']);
		
		$definition = $container->findDefinition('oka_wsse_authentication.util.wsse_user_manipulator');
		$definition->replaceArgument(2, $config['class']);
	}
	
	public function addConfiguration(NodeDefinition $builder)
	{
		$builder
			->children()
				->scalarNode('class')
					->cannotBeEmpty()
					->validate()
						->ifTrue(function ($class) {
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
