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

        $products = $this->get('doctrine_mongodb')->getRepository('EvispaMongoProductBackendBundle:Product')->findAll();

        /** @var Product $product */
        foreach ($products as $product) {
            $resource = $this->get('resource_managers')->getResourceManager('product', $options)->findOne(
                $product->getSlug()
            );

            var_dump($resource);
        }




        return array('name' => 'test');
    }
}
