<?php
namespace Oka\WSSEAuthenticationBundle\DependencyInjection;

use Doctrine\Common\Persistence\ObjectManager;
use Oka\WSSEAuthenticationBundle\Security\Authorization\Voter\AllowedIpsVoter;
use Oka\WSSEAuthenticationBundle\Security\Nonce\Storage\Handler\FileNonceHandler;
use Oka\WSSEAuthenticationBundle\Service\WSSEUserManipulator;
use Oka\WSSEAuthenticationBundle\Service\WSSEUserManipulatorProxy;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class OkaWSSEAuthenticationExtension extends Extension implements CompilerPassInterface
{
	/**
	 * @var array $doctrineDrivers
	 */
	public static $doctrineDrivers = [
			'orm' => [
					'registry' => 'doctrine',
					'tag' => 'doctrine.event_subscriber',
			],
			'mongodb' => [
					'registry' => 'doctrine_mongodb',
					'tag' => 'doctrine_mongodb.odm.event_subscriber',
			]
	];
	
	/**
	 * {@inheritdoc}
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		$configuration = new Configuration();
		$config = $this->processConfiguration($configuration, $configs);
		
		$loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
		$loader->load('services.yml');
				
		// Doctrine configuration
		$container->setParameter('oka_wsse_authentication.db_driver', $config['db_driver']);
		$container->setParameter('oka_wsse_authentication.backend_type_'.$config['db_driver'], true);
		$container->setParameter('oka_wsse_authentication.model_manager_name', $config['model_manager_name']);
		
		if (null !== $config['user_class']) {
			$container->setParameter('oka_wsse_authentication.user_class', $config['user_class']);
		}
		
		// Configure Nonce
		if (null === ($nonceHandlerId = $config['nonce']['handler_id'])) {
			$nonceHandlerId = 'oka_wsse_authentication.nonce.file_handler';
			$savePath = $config['nonce']['save_path'] ?: $container->getParameter('kernel.cache_dir') . '/oka_wsse/nonces';
			$container->setDefinition($nonceHandlerId, new Definition(FileNonceHandler::class, [$savePath]));
		}
		
		// Configure guard authenticator
		$authenticatorDefinition = $container->getDefinition('oka_wsse_authentication.wsse_authenticator');
		$authenticatorDefinition->replaceArgument(0, new Reference($nonceHandlerId));
		$authenticatorDefinition->replaceArgument(3, $config['nonce']['lifetime']);
		$authenticatorDefinition->replaceArgument(4, $config['realm']);
		
		// Configure authorization allowed IPs voter
		if (true === $config['enabled_allowed_ips_voter']) {
			$allowedIpsVoterDefinition = new Definition(AllowedIpsVoter::class);
			$allowedIpsVoterDefinition->addTag('security.voter');
			$container->setDefinition('oka_wsse_authentication.security.authorization.allowed_ips_voter', $allowedIpsVoterDefinition);
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface::process()
	 */
	public function process(ContainerBuilder $container)
	{
		$doctrineRegistry = self::$doctrineDrivers[$container->getParameter('oka_wsse_authentication.db_driver')]['registry'];
		
		if (false === $container->hasDefinition($doctrineRegistry)) {
			return;
		}
		
		$container->setAlias('oka_wsse_authentication.doctrine_registry', new Alias($doctrineRegistry, false));
		$objectManager = $container->setDefinition('oka_wsse_authentication.object_manager', new Definition(ObjectManager::class));
		$objectManager->setFactory([new Reference('oka_wsse_authentication.doctrine_registry'), 'getManager']);
		
		if (false === $container->hasParameter('oka_wsse_authentication.user_class')) {
			return;
		}
		
		$userManipulator = $container->setDefinition(WSSEUserManipulator::class, new Definition(WSSEUserManipulator::class));
		$userManipulator->addArgument(new Reference('oka_wsse_authentication.object_manager'));
		$userManipulator->addArgument(new Reference('event_dispatcher'));
		$userManipulator->addArgument($container->getParameter('oka_wsse_authentication.user_class'));
		
		$proxy = $container->getDefinition(WSSEUserManipulatorProxy::class);
		$proxy->addMethodCall('setUserManipulator', [new Reference(WSSEUserManipulator::class)]);
		$proxy->setPublic(true);
	}
}
