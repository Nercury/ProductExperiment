<?php

namespace Evispa\ProductAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class ProductsController extends Controller
{
    /**
     * @Route(requirements={"_format"="json|xml"})
     * @ApiDoc
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

    /**
     *
     * @ApiDoc
     */
    public function getProductsAction() {
        $data = new \Evispa\Component\MultipartResource\Data\CombinedData();
        $data["name"] = "Pavadinimas";
        $data["attributes"] = array(
            new \Evispa\ProductAdminBundle\Attr(),
        );

        return \FOS\RestBundle\View\View::create(array(
            $data
        ));
    }

    /**
     *
     * @ApiDoc
     */
    public function postProductsAction() {
        $data = new \Evispa\Component\MultipartResource\Data\CombinedData();
        $data["name"] = "Pavadinimas";
        $data["attributes"] = array(
            new \Evispa\ProductAdminBundle\Attr(),
        );

        $form = $this->createFormBuilder($data);
        $form->add('name', 'text');
        $form->add('attributes', 'collection', array(
            'class' => "\Evispa\ProductAdminBundle\Attr"
        ));

        return \FOS\RestBundle\View\View::create(array(
            $data
        ));
    }
}
