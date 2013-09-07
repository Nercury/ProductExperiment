<?php

namespace Evispa\MongoProductBackendBundle\Controller;

use Evispa\MongoProductBackendBundle\Document\Product;
use Evispa\ResourceApiBundle\Backend\FindParameters;
use Evispa\ResourceApiBundle\Manager\ResourceManager;
use Pagerfanta\Adapter\DoctrineODMMongoDBAdapter;
use Pagerfanta\Pagerfanta;
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
        /** @var ResourceManager $resourceManager */
        $resourceManager = $this->get('resource_managers')->getResourceManager('product', $options);


        $params = new FindParameters();
        $params->limit = 5;
        $params->offset = 5;

        $resources = $resourceManager->find($params);

        var_dump($resources->getTotalFound());
        var_dump($resources->getParameters());
        var_dump($resources->getResources());

        return array('name' => 'test');
    }
}
