<?php

namespace Evispa\ProductApiBundle\Controller;

use Evispa\ResourceApiBundle\Backend\FindParameters;
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
    /**
     * Get product resource manager.
     *
     * @param array $options
     * @return \Evispa\ResourceApiBundle\Manager\ResourceManager
     */
    private function getProductResourceManager($options) {
        $prm = $this->get('resource_managers')->getResourceManager('product', $options);
        return $prm;
    }

    /**
     * Get version converter for Product resource.
     *
     * @param \Evispa\ResourceApiBundle\Manager\ResourceManager $prm Resource manager.
     *
     * @return \Evispa\ObjectMigration\VersionConverter
     */
    private function getProductVersionConverter($prm) {
        return new \Evispa\ObjectMigration\VersionConverter(
            $prm->getVersionReader(),
            $prm->getClassName(),
            $prm->getConverterOptions()
        );
    }

    /**
     * Get version and format for the request.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Evispa\ResourceApiBundle\Manager\ResourceManager $prm
     *
     * @return \Evispa\ResourceApiBundle\VersionParser\VersionAndFormat
     */
    private function getExpectedVersionAndFormat($request, \Evispa\ResourceApiBundle\Manager\ResourceManager $prm) {
        $versionReader = $prm->getVersionReader();
        $restVersionParser = new \Evispa\ResourceApiBundle\VersionParser\AcceptVersionParser();
        return $restVersionParser
            ->setAllowedVersions($versionReader->getAllowedClassOutputVersions($prm->getClassName()))
            ->setRequestedFormat($request->getRequestFormat())
            ->setDefault('html', $versionReader->getClassVersion('Evispa\Api\Product\Model\ProductV1'))
            ->setDefault('json', $versionReader->getClassVersion('Evispa\Api\Product\Model\SimpleProductV1'))
            ->setDefault('xml', $versionReader->getClassVersion('Evispa\Api\Product\Model\SimpleProductV1'))
            ->parseVersionAndFormat($request->getAcceptableContentTypes());
    }

    /**
     * Get a product by its identifier.
     *
     * @ApiDoc
     * @View(templateVar="product")
     */
    public function getProductAction(ParamFetcher $paramFetcher, $slug)
    {
        $request = $this->getRequest();

        $options = array('locale' => $request->getLocale());

        $prm = $this->getProductResourceManager($options);
        $product = $prm->fetchOne(
            $slug
        );

        if (null === $product) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Product was not found.");
        } else {
            $view = \FOS\RestBundle\View\View::create();

            // Find out what client wants. Impossible, but very important.
            $expectedVersionAndFormat = $this->getExpectedVersionAndFormat($request, $prm);

            if (null === $expectedVersionAndFormat) {
                throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(
                    'Can not return requested resource in '.$request->getRequestFormat().' format.'
                );
            }

            $view->setFormat($expectedVersionAndFormat->getFormat());

            $vc = $this->getProductVersionConverter($prm);
            $product = $vc->migrateToVersion($product, $expectedVersionAndFormat->getVersion());

            $view->setData($product);
        }

        return $view;
    }

    /**
     * Get list of all products.
     *
     * @ApiDoc
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page of the product list.")
     * @QueryParam(name="count", requirements="\d+", default="50", strict=true, nullable=true, description="Item count limit")
     * @View(templateVar="result")
     */
    public function getProductsAction(ParamFetcher $paramFetcher) {
        var_dump($this->get('resource.product'));

        var_dump($this->get('resource.product')->getOutputMigrationActions('Evispa\Api\Product\Model\SimpleProductV1'));

         die;

        $page = $paramFetcher->get('page');

        $request = $this->getRequest();

        $options = array('locale' => $request->getLocale());
        $prm = $this->getProductResourceManager($options);

        $params = new FindParameters();
        $params->limit = 5;
        $params->offset = ($page - 1) * $params->limit;

        $resourcesObject = $prm->fetchAll($params);

        $view = \FOS\RestBundle\View\View::create();

        // Find out what client wants. Impossible, but very important.
        $expectedVersionAndFormat = $this->getExpectedVersionAndFormat($request, $prm);
        if (null === $expectedVersionAndFormat) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(
                'Can not return requested resource in '.$request->getRequestFormat().' format.'
            );
        }

        $view->setFormat($expectedVersionAndFormat->getFormat());
        $vc = $this->getProductVersionConverter($prm);

        // TODO: Change "$results" to proper REST object with links to prev/next.

        $results = array('resources' => array());
        $results['total'] = $resourcesObject->getTotalFound();
        $results['parameters'] = $resourcesObject->getParameters();

        foreach ($resourcesObject->getResources() as $resource) {
            $results['resources'][] = $vc->migrateToVersion($resource, $expectedVersionAndFormat->getVersion());
        }

        $view->setData($results);

        return $view;
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
