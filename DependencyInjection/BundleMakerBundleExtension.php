<?php

namespace Prokl\BundleMakerBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class BundleMakerBundleExtension
 * @package Prokl\BundleMakerBundle\DependencyInjection
 */
class BundleMakerBundleExtension extends Extension
{

    /** Setting config to service
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     *
     * @return void
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $configuration = new Configuration();
        $loader->load('services.yaml');

        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('maker_bundle', $config);

        $createBundleCommand = $container->getDefinition('maker_bundle.command.create_bundle_command');
        $createBundleCommand->setArgument(0, $container->getParameter('maker_bundle'));
    }

    /**
     * @return string
     */
    public function getAlias() : string
    {
        return 'maker_bundle';
    }
}
