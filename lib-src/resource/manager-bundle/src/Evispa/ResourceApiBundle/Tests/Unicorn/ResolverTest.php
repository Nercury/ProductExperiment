<?php

namespace Evispa\ResourceApiBundle\Tests\Unicorn;

use Evispa\ResourceApiBundle\Config\ResourceApiConfig;
use Evispa\ResourceApiBundle\Config\ResourceBackendConfig;
use Evispa\ResourceApiBundle\Registry\ResourceBackendConfigRegistry;
use Evispa\ResourceApiBundle\Tests\Mock\MockPrimaryProductBackend;
use Evispa\ResourceApiBundle\Unicorn\ApiUnicornResolver;

/**
 * @author nerijus
 */
class ResolverTest extends \PHPUnit_Framework_TestCase
{

    public function testAutoconfiguration() {

        $apiConfig = new ResourceApiConfig('product', 'Evispa\ResourceApiBundle\Tests\Mock\MockProduct', array(
            'product.text2' => 'text',
            'product.price' => 'price',
        ));

//        $backendConfigs = new ResourceBackendConfigRegistry();
//        $backendConfigs->registerBackendConfig(
//            ResourceBackendConfig::create(
//                'mock_backend',
//                'product',
//                array('product.text' => 'Evispa\ResourceApiBundle\Tests\Mock\MockProductText'),
//                new MockPrimaryProductBackend()
//            )
//        );
//
//        $appApiBackendMap = array(
//
//        );
//
//        $resolver = new ApiUnicornResolver();
//        $unicorn = $resolver->getUnicornConfigurationInfo($apiConfig, $backendConfigs, $appApiBackendMap);
//
//        var_dump($unicorn);

    }
}