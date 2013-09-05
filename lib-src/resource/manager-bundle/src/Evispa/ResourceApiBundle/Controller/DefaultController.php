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

        $pm = $managers->getResourceManager('product');

        $p = $pm->findOne(15);

        var_dump($p); die;

        return array('name' => $name);
    }
}
