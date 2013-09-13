<?php

namespace Evispa\ProductApiBundle\Controller;

use Evispa\Api\Product\Model\SimpleProductV1;
use Evispa\ResourceApiBundle\Backend\PrimaryBackendObject;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @author nerijus
 */
class ProductEditController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * @return \Evispa\ResourceApiBundle\Manager\ResourceManager
     */
    private function getProductManager()
    {
        return $this->get('resource_api.product');
    }

    /**
     * @Route("/product/create")
     */
    public function createAction()
    {
        $request = $this->getRequest();

        $rm = $this->getProductManager();

        $options = array(
            'locale' => 'en',
        );

        $product = $rm->getNew($options);

        /** @var SimpleProductV1 $simpleProduct */
        $simpleProduct = $product;

        foreach ($rm->migrationInfo->getOutputMigrationActions('Evispa\Api\Product\Model\SimpleProductV1') as $action) {
            $simpleProduct = $action->run($simpleProduct, $options);
        }

        $formBuilder = $this->createFormBuilder($simpleProduct);
        $formBuilder->add('code', 'text');
        $formBuilder->add(
            'name',
            'text',
            array(
                'property_path' => 'text.name',
            )
        );

        $form = $formBuilder->getForm();
        if ($request->isMethod('POST')) {
            $form->submit($request);

            /** @var \Evispa\Api\Product\Model\ProductV1 $product */
            $product = $simpleProduct;


            foreach ($rm->migrationInfo->getInputMigrationActions('Evispa\Api\Product\Model\SimpleProductV1') as $action) {
                $product = $action->run($product, $options);
            }

            $primaryBackendProduct = new PrimaryBackendObject($product->getSlug());
            $primaryBackendProduct->setPart('product.code', $simpleProduct->code);
            $primaryBackendProduct->setPart('product.text', $simpleProduct->text);

            $saved = $this->get('evispa_mongo_product_backend.product_backend')->save(
                $primaryBackendProduct,
                array('product.code', 'product.route', 'product.text')
            );

//            $rm->saveOne($product);

        }

        return $this->render(
            'EvispaProductApiBundle:Edit:create.html.twig',
            array(
                'form' => $form->createView()
            )
        );
    }
}