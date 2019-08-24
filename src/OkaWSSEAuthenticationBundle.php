<?php
namespace Oka\WSSEAuthenticationBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass;
use Oka\WSSEAuthenticationBundle\DependencyInjection\Security\Factory\WSSESecurityFactory;
use Oka\WSSEAuthenticationBundle\DependencyInjection\Security\UserProvider\WSSEUserProviderFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OkaWSSEAuthenticationBundle extends Bundle
{
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\HttpKernel\Bundle\Bundle::build()
	 */
	public function build(ContainerBuilder $container)
	{
		parent::build($container);
		
		$this->addRegisterMappingsPass($container);
		
		/** @var \Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension $extension */
		$extension = $container->getExtension('security');
		$extension->addSecurityListenerFactory(new WSSESecurityFactory());
		$extension->addUserProviderFactory(new WSSEUserProviderFactory());
	}
	
	/**
	 * @param ContainerBuilder $container
	 */
	private function addRegisterMappingsPass(ContainerBuilder $container)
	{
		$mapping = [realpath(__DIR__.'/Resources/config/doctrine-mapping') => 'Oka\WSSEAuthenticationBundle\Model'];
		
		if (true === class_exists('Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass')) {
			$container->addCompilerPass(DoctrineOrmMappingsPass::createYamlMappingDriver($mapping, array('oka_wsse_authentication.model_manager_name'), 'oka_wsse_authentication.backend_type_orm'));
		}
		
		if (true === class_exists('Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass')) {
			$container->addCompilerPass(DoctrineMongoDBMappingsPass::createYamlMappingDriver($mapping, array('oka_wsse_authentication.model_manager_name'), 'oka_wsse_authentication.backend_type_mongodb'));
		}
	}
}
