<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\DependencyInjection\ContainerBuilder;

return static function (ContainerConfigurator $container, ContainerBuilder $containerBuilder): void {
    $services = $container->services();
    $services->defaults()->private()->autowire()->autoconfigure();

    $services->load('Supseven\\CanonicalFiles\\Backend\\', __DIR__ . '/../Classes/Backend/*')->public();
    $services->load('Supseven\\CanonicalFiles\\Middleware\\', __DIR__ . '/../Classes/Middleware/*')->public();
    $services->load('Supseven\\CanonicalFiles\\Service\\', __DIR__ . '/../Classes/Service/*');
};
