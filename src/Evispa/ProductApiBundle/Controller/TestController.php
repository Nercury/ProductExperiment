<?php

namespace Evispa\ProductApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class TestController
{
    /**
    * @Route("/test")
    */
    public function testAction() {
        return new \Symfony\Component\HttpFoundation\Response('Hello');
    }
}