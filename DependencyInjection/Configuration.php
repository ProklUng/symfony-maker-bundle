<?php

namespace Prokl\BundleMakerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Prokl\BundleMakerBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder() : TreeBuilder
    {
        $treeBuilder = new TreeBuilder('maker_bundle');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->scalarNode('template_dir')
                ->info('path to templates (default to directory `installation/templates` in bundle)')
                ->defaultValue('default')
            ->end()
                ->scalarNode('bundle_dir')
                    ->info('path to generate bundles (default to /local/classes/Bundles/)')
                    ->defaultValue('/local/classes/Bundles/')
                ->end()
                ->scalarNode('config_file_dir')
                    ->info('path to config dir. Default: /local/configs/')
                    ->defaultValue('/local/configs/')
                ->end()
                ->scalarNode('config_file')
                    ->info('Name of bundles config file. Default: standalone_bundles.php')
                    ->defaultValue('standalone_bundles.php')
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
