<?php

namespace Evispa\ProductAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Evispa\Component\MultipartResource\Annotations\Resource;
use FSC\HateoasBundle\Annotation as Rest;

class ProductsController extends Controller
{
    /**
     * Get a product by its identifier.
     *
     * @Rest\Relation("self", href=@Rest\Route("api_product_get", parameters={"slug"=".slug"}))
     *
     * @Route(name="api_product_get")
     * @ApiDoc
     * @View(templateVar="product")
     */
    public function getProductAction($slug)
    {
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
     * @View(templateVar="products")
     */
    public function getProductsAction() {
        $data = new \Evispa\Component\MultipartResource\Data\CombinedData();
        $data["name"] = "Pavadinimas";
        $data["attributes"] = array(
            new \Evispa\ProductAdminBundle\Attr(),
        );

        $data2 = new \Evispa\Component\MultipartResource\Data\CombinedData();
        $data2["name"] = "Pavadinimas 2";
        $data2["attributes"] = array(
            new \Evispa\ProductAdminBundle\Attr(),
        );

        return \FOS\RestBundle\View\View::create(array(
            $data,
            $data2,
        ));
    }

    /**
     * Create a new product.
     *
     * @ApiDoc
     * @View(templateVar="product")
     * @Resource("product", action="create")
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

        $view = \FOS\RestBundle\View\View::create();

        if (false === $request->request->get('form', false)) {
            if ('html' !== $view->getFormat()) {
                $form->addError(new \Symfony\Component\Form\FormError('Submit form data based on specified parameters.'));
            }
        } else {
            $form->submit($request);
            $data = $form->getData();
            if ($form->isValid()) {
                return new \Symfony\Component\HttpFoundation\Response($this->get('serializer')->serialize($data, 'json'));
            }
        }

        $view->setData($form);
        $view->setStatusCode(400);

        return $view;
    }

    /**
     * Update a product.
     *
     * @ApiDoc
     * @Resource("product", action="update")
     */
    public function putProductAction(\Symfony\Component\HttpFoundation\Request $request, $slug) {
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
