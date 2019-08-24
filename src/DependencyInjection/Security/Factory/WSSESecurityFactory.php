<?php
namespace Oka\WSSEAuthenticationBundle\DependencyInjection\Security\Factory;

use Oka\WSSEAuthenticationBundle\Security\Nonce\Storage\Handler\FileNonceHandler;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * 
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 * 
 */
class WSSESecurityFactory implements SecurityFactoryInterface
{
	public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
	{
		// Configure Nonce
		if (null === ($nonceHandlerId = $config['nonce']['handler_id'])) {
			$nonceHandlerId = 'oka_wsse_authentication.nonce.file_handler.'.$id;
			$savePath = $config['nonce']['save_path'] ?: $container->getParameter('kernel.cache_dir') . '/oka_wsse/nonces';
			$container->setDefinition($nonceHandlerId, new Definition(FileNonceHandler::class, [$savePath]));
		}
		
		$providerId = 'security.authentication.provider.wsse.'.$id;
		$container->setDefinition($providerId, new ChildDefinition('oka_wsse_authentication.security.authentication.provider'))
				  ->replaceArgument(0, new Reference($userProvider))
				  ->replaceArgument(1, new Reference($nonceHandlerId))
				  ->replaceArgument(2, $config['lifetime']);
		
		$listenerId = 'security.authentication.listener.wsse.'.$id;
		$container->setDefinition($listenerId, new ChildDefinition('oka_wsse_authentication.security.authentication.listener'))
				  ->replaceArgument(5, $config['realm']);
		
		return [$providerId, $listenerId, $defaultEntryPoint];
	}
	
	public function addConfiguration(NodeDefinition $builder)
	{
		$builder
			->children()
				->scalarNode('lifetime')->defaultValue(300)->end()
				
				->scalarNode('realm')
					->defaultValue('Secured Area')
				->end()
				
				->arrayNode('nonce')
					->addDefaultsIfNotSet()
					->children()
						->scalarNode('lifetime')->defaultValue(300)->end()
						->scalarNode('handler_id')->defaultNull()->end()
						->scalarNode('save_path')->defaultNull()->end()
					->end()
				->end()
			->end();
	}
	
	public function getPosition()
	{
		return 'pre_auth';
	}
	
	public function getKey()
	{
		return 'wsse';
	}
}
