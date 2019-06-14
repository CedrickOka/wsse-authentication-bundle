<?php
namespace Oka\WSSEAuthenticationBundle\DependencyInjection;

use Oka\WSSEAuthenticationBundle\Security\Authorization\Voter\AllowedIpsVoter;
use Oka\WSSEAuthenticationBundle\Security\Guard\WSSEAuthenticator;
use Oka\WSSEAuthenticationBundle\Security\Nonce\Storage\Handler\FileNonceHandler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Oka\WSSEAuthenticationBundle\Util\WSSEUserManipulator;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class OkaWSSEAuthenticationExtension extends Extension
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
		$container->setParameter('oka_wsse_authentication.backend_type_'.$config['db_driver'], true);
		$container->setParameter('oka_wsse_authentication.model_manager_name', $config['model_manager_name']);
		
		$container->setAlias('oka_wsse_authentication.doctrine_registry', new Alias(self::$doctrineDrivers[$config['db_driver']]['registry'], false));
		$objectManagerDefinition = new Definition(ObjectManager::class);
		$objectManagerDefinition->setFactory([new Reference('oka_wsse_authentication.doctrine_registry'), 'getManager']);
		
		if (true === $container->hasDefinition(self::$doctrineDrivers[$config['db_driver']]['registry']) && null !== $config['user_class']) {
			$userManipulatorDefinition = new Definition(WSSEUserManipulator::class);
			$userManipulatorDefinition->addArgument(new Reference('oka_wsse_authentication.object_manager'));
			$userManipulatorDefinition->addArgument(new Reference('event_dispatcher'));
			$userManipulatorDefinition->addArgument($config['user_class']);
			$userManipulatorDefinition->setPublic(true);
			$container->setDefinition('oka_wsse_authentication.util.wsse_user_manipulator', $userManipulatorDefinition);
		}
		
		// Configure Nonce		
		if (null === ($nonceHandlerId = $config['nonce']['handler_id'])) {
			$nonceHandlerId = 'oka_wsse_authentication.nonce.file_handler';
			$savePath = $config['nonce']['save_path'] ?: $container->getParameter('kernel.cache_dir') . '/oka_wsse/nonces';
			$container->setDefinition($nonceHandlerId, new Definition(FileNonceHandler::class, [$savePath]));
		}
		
		// Configure guard authenticator
		$authenticatorDefinition = new Definition(WSSEAuthenticator::class);
		$authenticatorDefinition->addArgument(new Reference($nonceHandlerId));
		$authenticatorDefinition->addArgument(new Reference('event_dispatcher'));
		$authenticatorDefinition->addArgument($config['nonce']['lifetime']);
		$authenticatorDefinition->addArgument($config['realm']);
		$container->setDefinition('oka_wsse_authentication.wsse_authenticator', $authenticatorDefinition);
		$container->setAlias('oka_wsse_authentication.wsse_token_authenticator', new Alias('oka_wsse_authentication.wsse_authenticator', false));
		
		// Configure authorization allowed IPs voter
		if (true === $config['enabled_allowed_ips_voter']) {
			$allowedIpsVoterDefinition = new Definition(AllowedIpsVoter::class);
			$allowedIpsVoterDefinition->addTag('security.voter');
			$container->setDefinition('oka_wsse_authentication.security.authorization.allowed_ips_voter', $allowedIpsVoterDefinition);
		}
	}
}
