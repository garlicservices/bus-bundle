<?php

namespace Garlic\Bus\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class GarlicBusExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $config = [
            'bus' => [
                'transport' => [
                    'dsn' => 'amqp://'.
                        getenv('RABBIT_USER').':'.
                        getenv('RABBIT_PASSWORD').'@'.
                        getenv('RABBIT_HOST').':'.
                        getenv('RABBIT_PORT').'/'.
                        getenv('RABBIT_DEFAULT_VHOST'),
                ],
                'client' => [
                    'app_name' => getenv('SERVICE_NAME'),
                ],
            ],

        ];

        $container->prependExtensionConfig('enqueue', $config);
    }
}