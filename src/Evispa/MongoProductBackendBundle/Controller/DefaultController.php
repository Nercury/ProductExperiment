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
        $product = $this->get('evispa_mongo_product_backend.product_backend_manager')->findOne('52284a46ed7d3e6d0c8b4576', array());

        var_dump($product);

//        $dm = $this->get('doctrine_mongodb')->getManager();
//        $product = new Product();
//        $product->setCode('#'.rand(100000, 9999999));
//        $product->setText('tekstas');
//
//        $dm->persist($product);
//
//        $dm->flush();
//        $product->getSlug();

        return array('name' => 'test');
    }
}
