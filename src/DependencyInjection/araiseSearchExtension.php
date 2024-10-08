<?php

declare(strict_types=1);

namespace araise\SearchBundle\DependencyInjection;

use araise\SearchBundle\Manager\IndexManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class araiseSearchExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $indexManager = $container->getDefinition(IndexManager::class);
        $indexManager->addMethodCall('setConfig', [$config]);

        $container->setParameter('araise_search.asterisk_search_enabled', $config['asterisk_search_enabled']);
    }

    public function prepend(ContainerBuilder $container): void
    {
        if (! $container->hasExtension('doctrine_migrations')) {
            return;
        }

        $doctrineConfig = $container->getExtensionConfig('doctrine_migrations');
        $container->prependExtensionConfig('doctrine_migrations', [
            'migrations_paths' => array_merge(array_pop($doctrineConfig)['migrations_paths'] ?? [], [
                'araise\SearchBundle\Migrations' => '@araiseSearchBundle/Migrations',
            ]),
        ]);
    }
}
