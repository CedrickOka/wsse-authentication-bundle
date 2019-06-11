<?php
namespace Oka\WSSEAuthenticationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder('oka_wsse_authentication');
		$rootNode = $treeBuilder->getRootNode();
		
		$rootNode
				->addDefaultsIfNotSet()
				->children()
					
					->enumNode('db_driver')
						->cannotBeEmpty()
						->values(['mongodb', 'orm'])
						->defaultValue('orm')
					->end()
					
					->scalarNode('model_manager_name')->defaultNull()->end()
					
					->scalarNode('user_class')
						->cannotBeEmpty()
						->defaultNull()
					->end()
					
					->scalarNode('realm')->defaultValue('Secure Area')->end()
					
					->arrayNode('nonce')
						->addDefaultsIfNotSet()
						->children()
							->scalarNode('lifetime')->defaultValue(300)->end()
							->scalarNode('handler_id')->defaultNull()->end()
							->scalarNode('save_path')->defaultNull()->end()
						->end()
					->end()
					
					->booleanNode('enabled_allowed_ips_voter')
						->defaultTrue()
						->info('Allows request authorization voter with IPs addresses given.')
					->end()					
				->end();
		
		return $treeBuilder;
	}
}
