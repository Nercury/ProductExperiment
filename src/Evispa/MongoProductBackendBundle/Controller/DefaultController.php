<?php

namespace Evispa\MongoProductBackendBundle\Controller;

use Evispa\MongoProductBackendBundle\Document\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/test")
     * @Template()
     */
    public function indexAction()
    {
        $options = array('locale' => $this->getRequest()->getLocale());

        $product = $this->get('resource_managers')->getResourceManager('product', $options)->findOne('52284a46ed7d3e6d0c8b4576');

        var_dump($product);

        return array('name' => 'test');
    }
}
