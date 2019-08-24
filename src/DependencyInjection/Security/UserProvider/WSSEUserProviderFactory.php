<?php
namespace Oka\WSSEAuthenticationBundle\DependencyInjection\Security\UserProvider;

use Oka\WSSEAuthenticationBundle\Model\WSSEUserInterface;
use Oka\WSSEAuthenticationBundle\Security\User\WSSEUserProvider;
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
		$userProviderDefinition = new Definition(WSSEUserProvider::class);
		$userProviderDefinition->addArgument(new Reference('oka_wsse_authentication.object_manager'));
		$userProviderDefinition->addArgument($config['class']);
		$container->setDefinition($id, $userProviderDefinition);
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
						->thenInvalid('The %s class must implement '.WSSEUserInterface::class.' for using the "wsse" user provider.')
					->end()
				->end()
			->end();
	}
	
	public function getKey()
	{
		return 'wsse';
	}
}
