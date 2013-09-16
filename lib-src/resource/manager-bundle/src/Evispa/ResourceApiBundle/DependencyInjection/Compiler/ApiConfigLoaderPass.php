<?php

namespace Evispa\ResourceApiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Uses services with "resource_api_config" to load blocks into evispa_resource_api.api_config_registry service
 *
 * @author nercury
 */
class ApiConfigLoaderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('evispa_resource_api.api_config_registry')) {
            return;
        }

        $definition = $container->getDefinition('evispa_resource_api.api_config_registry');

        // Extensions must always be registered before everything else.
        // For instance, global variable definitions must be registered
        // afterward. If not, the globals from the extensions will never
        // be registered.
        $calls = $definition->getMethodCalls();
        $definition->setMethodCalls(array());
        foreach ($container->findTaggedServiceIds('resource_api_config') as $id => $attributes) {
            $definition->addMethodCall('registerApiConfig', array(new Reference($id)));
        }
        $definition->setMethodCalls(array_merge($definition->getMethodCalls(), $calls));
    }
}
