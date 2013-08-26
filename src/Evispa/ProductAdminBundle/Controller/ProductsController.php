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
     * Get a product by its identifier.
     *
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
     * Get list of all products.
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
     * Create a new product.
     *
     * @ApiDoc
     */
    public function postProductsAction(\Symfony\Component\HttpFoundation\Request $request) {
        $data = new \Evispa\Component\MultipartResource\Data\CombinedData();
        $data["name"] = "Pavadinimas";

        $fb = $this->createFormBuilder(null, array(
            'csrf_protection' => false
        ));
        $fb->add('name', 'text', array(
            'property_path' => '[name]'
        ));

        $form = $fb->getForm();
        $form->setData($data);

        if (false !== $request->request->get('form', false)) {
            $form->submit($request);
            $data = $form->getData();
            if ($form->isValid()) {
                return new \Symfony\Component\HttpFoundation\Response($this->get('serializer')->serialize($data, 'json'));
            }
        }

        return \FOS\RestBundle\View\View::create($form, 400);
    }
}
