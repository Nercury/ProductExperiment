<?php

namespace Evispa\ResourceApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @return \Evispa\ResourceApiBundle\Registry\ManagerRegistry
     */
    private function getResourceManagers() {
        return $this->get('resource_managers');
    }

    /**
     * @Route("/api/test")
     * @Template()
     */
    public function testAction()
    {
        $managers = $this->getResourceManagers();

        var_dump($managers->getResourceManager('product')); die;

        return array('name' => $name);
    }
}
