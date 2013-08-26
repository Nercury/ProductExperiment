<?php

namespace Evispa\ProductAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter;
use FOS\RestBundle\Controller\Annotations\View;

class ProductsController extends Controller
{
    /**
     * @Route("/products/{slug}.{_format}", defaults={"_format": null})
     * @View
     */
    public function getProductAction($slug)
    {
        $this->getRequest()->setFormat('yaml', 'text/yaml');

        $data = new \Evispa\Component\MultipartResource\Data\CombinedData();
        $data["name"] = "Pavadinimas";
        $data["attributes"] = array(
            new \Evispa\ProductAdminBundle\Attr(),
        );

        return \FOS\RestBundle\View\View::create($data);
    }
}
