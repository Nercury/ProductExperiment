<?php

namespace Evispa\ProductApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Request\ParamFetcher;

use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
//use Evispa\Resource\Component\MultipartResource\Annotations\Resource;

class ProductsController extends Controller
{
    private function getProductStorage() {
        //$this->get('api_resources')->getResourceBackend('product')
    }

    /**
     * Get a product by its identifier.
     *
     * @ApiDoc
     * @View(templateVar="product")
     */
    public function getProductAction($slug)
    {
        $storage = $this->getProductStorage('Evispa\Api\Product\Model\ProductV1');
        $product = $storage->find($slug);

        /*$data = new \Evispa\Api\Product\Model\ProductV1();
        $data->setSlug('pav1');

        $data->code = new \Evispa\Api\Product\Model\Code\ProductCodeV1();
        $data->code->code = "PAV1";
        $data->code->ean = "11111";

        $data->text = new \Evispa\Api\Product\Model\Text\LocalizedTextV1();
        $data->text->items['lt'] = new \Evispa\Api\Product\Model\Text\TextV1();
        $data->text->items['lt']->name = "Pavadinimas 1";
        $data->text->items['lt']->description = "Aprašymas 1";*/

        return \FOS\RestBundle\View\View::create($product);
    }

    /**
     * Get list of all products.
     *
     * @ApiDoc
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page of the product list.")
     * @QueryParam(name="count", requirements="\d+", default="50", strict=true, nullable=true, description="Item count limit")
     * @View(templateVar="products")
     */
    public function getProductsAction(ParamFetcher $paramFetcher) {
        $page = $paramFetcher->get('page');



        $data = new \Evispa\Api\Product\Model\ProductV1();
        $data->setSlug('pav1');

        $data->code = new \Evispa\Api\Product\Model\Code\ProductCodeV1();
        $data->code->code = "PAV1";
        $data->code->ean = "11111";

        $data->text = new \Evispa\Api\Product\Model\Text\LocalizedTextV1();
        $data->text->items['lt'] = new \Evispa\Api\Product\Model\Text\TextV1();
        $data->text->items['lt']->name = "Pavadinimas 1";
        $data->text->items['lt']->description = "Aprašymas 1";

        $data2 = new \Evispa\Api\Product\Model\ProductV1();
        $data2->setSlug('pav2');

        $data2->code = new \Evispa\Api\Product\Model\Code\ProductCodeV1();
        $data2->code->code = "PAV2";
        $data2->code->ean = "11112";

        $data2->text = new \Evispa\Api\Product\Model\Text\LocalizedTextV1();
        $data2->text->items['lt'] = new \Evispa\Api\Product\Model\Text\TextV1();
        $data2->text->items['lt']->name = "Pavadinimas 2";
        $data2->text->items['lt']->description = "Aprašymas 2";

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
     */
    public function postProductsAction() {
        $request = $this->getRequest();



        $fb = $this->createFormBuilder(null, array(
            'csrf_protection' => false
        ));
        $fb->add('slug', 'text');
        $fb->add('name', 'text');

        $form = $fb->getForm();
        $form->setData($data);

        //var_dump($product); die;

        $view = \FOS\RestBundle\View\View::create();

        /*if (false === $request->request->get('form', false)) {
            if ('html' !== $view->getFormat()) {
                $form->addError(new \Symfony\Component\Form\FormError('Submit form data based on specified parameters.'));
            }
        } else {*/
            $form->bind($request);
            $data = $form->getData();
            if ($form->isValid()) {

                return new \Symfony\Component\HttpFoundation\Response($this->get('serializer')->serialize($data, 'json'));
            }
        //}

        $view->setData($form);
        $view->setStatusCode(400);

        return $view;
    }

    /**
     * Update a product.
     *
     * @ApiDoc
     * @@Resource("product", action="update")
     */
    public function putProductAction(\Symfony\Component\HttpFoundation\Request $request, $slug) {
        $data = new \Evispa\ProductApiBundle\Rest\ProductData();
        $data->setSlug('pav1');
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
