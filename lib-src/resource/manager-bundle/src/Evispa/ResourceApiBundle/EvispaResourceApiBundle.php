<?php

namespace Evispa\ResourceApiBundle;

use Evispa\ResourceApiBundle\DependencyInjection\Compiler\ApiConfigLoaderPass;
use Evispa\ResourceApiBundle\DependencyInjection\Compiler\BackendConfigLoaderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EvispaResourceApiBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ApiConfigLoaderPass());
        $container->addCompilerPass(new BackendConfigLoaderPass());
    }
}
