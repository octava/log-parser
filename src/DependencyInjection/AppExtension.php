<?php

namespace App\DependencyInjection;

use App\Config\BadgesConfig;
use App\Config\ProvidersConfig;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AppExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $badges = $container->getDefinition(BadgesConfig::class);
        $badges->setArguments([$config[BadgesConfig::KEY_BADGES]]);

        $providers = $container->getDefinition(ProvidersConfig::class);
        $providers->setArguments([$config[ProvidersConfig::KEY_PROVIDERS]]);
    }
}
