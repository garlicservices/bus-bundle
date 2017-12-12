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
class MessageBusExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $config = [
            'transport' => [
                'default' => 'amqp',
                'amqp' => [
                    'driver' => 'ext',
                    'host' => getenv('RABBIT_HOST'),
                    'port' => getenv('RABBIT_PORT'),
                    'user' => getenv('RABBIT_USER'),
                    'pass' => getenv('RABBIT_PASSWORD'),
                    'vhost' => getenv('RABBIT_DEFAULT_VHOST'),
                    'receive_method' => 'basic_consume',
                ]
            ],
            'client' => [
                'app_name' => getenv('SERVICE_NAME')
            ]
        ];

        $container->prependExtensionConfig('enqueue', $config);
    }
}